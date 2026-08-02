<?php
/**
 * Review submission controller.
 *
 * @package Adp\Core\Reviews
 */

namespace Adp\Core\Reviews;

use Adp\Core\Content\AppPostType;
use Adp\Core\Support\RateLimiter;
use Adp\Core\Support\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Handles review REST and form submissions.
 */
class Controller {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/apps/(?P<id>\d+)/reviews',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'submit_review' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'id'      => array(
							'required'          => true,
							'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
						),
						'rating'  => array(
							'required'          => true,
							'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v >= 1 && (int) $v <= 5,
						),
						'content' => array(
							'required' => true,
							'type'     => 'string',
						),
						'author'  => array(
							'type'     => 'string',
							'required' => true,
						),
						'email'   => array(
							'type'     => 'string',
							'required' => true,
						),
						'nonce'   => array(
							'type'     => 'string',
							'required' => true,
						),
						'website' => array(
							'type' => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * Submit a review via REST.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function submit_review( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$settings = get_option( 'adp_core_settings', array() );
		if ( empty( $settings['reviews_enabled'] ) ) {
			return new \WP_Error( 'adp_reviews_disabled', __( 'Reviews are disabled.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		$post_id = (int) $request->get_param( 'id' );
		if ( AppPostType::POST_TYPE !== get_post_type( $post_id ) ) {
			return new \WP_Error( 'adp_invalid_app', __( 'Invalid app.', 'apk-directory-core' ), array( 'status' => 404 ) );
		}

		$nonce = sanitize_text_field( (string) $request->get_param( 'nonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'adp_submit_review_' . $post_id ) ) {
			return new \WP_Error( 'adp_invalid_nonce', __( 'Security check failed.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		// Honeypot.
		$honeypot = sanitize_text_field( (string) $request->get_param( 'website' ) );
		if ( '' !== $honeypot ) {
			return new \WP_REST_Response( array( 'success' => true ), 201 );
		}

		$rate_key = RateLimiter::key( 'review', (string) $post_id );
		if ( ! RateLimiter::attempt( $rate_key, 3, HOUR_IN_SECONDS ) ) {
			return new \WP_Error( 'adp_rate_limited', __( 'Too many reviews. Please try again later.', 'apk-directory-core' ), array( 'status' => 429 ) );
		}

		if ( self::has_recent_review( $post_id ) ) {
			return new \WP_Error( 'adp_duplicate_review', __( 'You have already submitted a review recently.', 'apk-directory-core' ), array( 'status' => 409 ) );
		}

		/**
		 * Filter review before submission.
		 *
		 * @param bool $allowed Whether submission is allowed.
		 * @param int  $post_id App post ID.
		 */
		$captcha_ok = apply_filters( 'adp_review_captcha_verify', true, $post_id );
		if ( ! $captcha_ok ) {
			return new \WP_Error( 'adp_captcha_failed', __( 'CAPTCHA verification failed.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		$rating  = Sanitizer::rating( $request->get_param( 'rating' ) );
		$content = sanitize_textarea_field( (string) $request->get_param( 'content' ) );
		$author  = sanitize_text_field( (string) $request->get_param( 'author' ) );
		$email   = sanitize_email( (string) $request->get_param( 'email' ) );

		if ( '' === $content || '' === $author || ! is_email( $email ) ) {
			return new \WP_Error( 'adp_invalid_input', __( 'Please fill in all required fields.', 'apk-directory-core' ), array( 'status' => 400 ) );
		}

		$comment_id = wp_insert_comment(
			array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => $author,
				'comment_author_email' => $email,
				'comment_content'      => $content,
				'comment_type'         => Schema::COMMENT_TYPE,
				'comment_approved'     => 0,
				'user_id'              => get_current_user_id(),
			)
		);

		if ( ! $comment_id ) {
			return new \WP_Error( 'adp_review_failed', __( 'Failed to submit review.', 'apk-directory-core' ), array( 'status' => 500 ) );
		}

		update_comment_meta( (int) $comment_id, Schema::RATING_META, $rating );

		return new \WP_REST_Response(
			array(
				'success'    => true,
				'comment_id' => $comment_id,
				'message'    => __( 'Thank you! Your review is awaiting moderation.', 'apk-directory-core' ),
			),
			201
		);
	}

	/**
	 * Check if visitor has a recent review for this app.
	 *
	 * @param int $post_id App post ID.
	 * @return bool
	 */
	private static function has_recent_review( int $post_id ): bool {
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			$existing = get_comments(
				array(
					'post_id' => $post_id,
					'type'    => Schema::COMMENT_TYPE,
					'user_id' => $user_id,
					'count'   => true,
				)
			);
			return $existing > 0;
		}

		$key = 'adp_review_' . md5( RateLimiter::client_id() . ':' . $post_id );
		return (bool) get_transient( $key );
	}
}
