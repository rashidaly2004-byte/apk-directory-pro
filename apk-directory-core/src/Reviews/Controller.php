<?php

namespace APD\Core\Reviews;

use APD\Core\Content\AppPostType;

final class Controller {

	public const COMMENT_TYPE = 'adp_review';

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'preprocess_comment', array( $this, 'validate_review' ) );
		add_filter( 'wp_insert_comment_data', array( $this, 'set_comment_type' ), 10, 2 );
		add_action( 'comment_post', array( $this, 'save_rating_meta' ), 10, 3 );
	}

	public function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/apps/(?P<id>\d+)/reviews',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_reviews' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_reviews( \WP_REST_Request $request ): \WP_REST_Response {
		$app_id   = (int) $request->get_param( 'id' );
		$comments = get_comments(
			array(
				'post_id' => $app_id,
				'type'    => self::COMMENT_TYPE,
				'status'  => 'approve',
				'number'  => 20,
			)
		);

		$data = array();
		foreach ( $comments as $comment ) {
			$data[] = array(
				'id'      => $comment->comment_ID,
				'author'  => $comment->comment_author,
				'content' => $comment->comment_content,
				'rating'  => (int) get_comment_meta( $comment->comment_ID, '_adp_rating', true ),
				'date'    => $comment->comment_date,
			);
		}

		return new \WP_REST_Response( array( 'reviews' => $data ), 200 );
	}

	public function validate_review( array $commentdata ): array {
		if ( get_post_type( $commentdata['comment_post_ID'] ) !== AppPostType::POST_TYPE ) {
			return $commentdata;
		}

		$rating = isset( $_POST['adp_rating'] ) ? absint( $_POST['adp_rating'] ) : 0;
		if ( $rating < 1 || $rating > 5 ) {
			wp_die( esc_html__( 'Invalid rating.', 'apk-directory-pro' ), 400 );
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'adp_review' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'apk-directory-pro' ), 403 );
		}

		// Honeypot.
		if ( ! empty( $_POST['adp_hp'] ) ) {
			wp_die( esc_html__( 'Spam detected.', 'apk-directory-pro' ), 403 );
		}

		return $commentdata;
	}

	public function set_comment_type( array $data, array $comment ): array {
		if ( get_post_type( $data['comment_post_ID'] ) === AppPostType::POST_TYPE && isset( $_POST['adp_rating'] ) ) {
			$data['comment_type'] = self::COMMENT_TYPE;
		}
		return $data;
	}

	public function save_rating_meta( int $comment_id, int $approved, array $commentdata ): void {
		if ( isset( $_POST['adp_rating'] ) && get_post_type( $commentdata['comment_post_ID'] ) === AppPostType::POST_TYPE ) {
			$rating = absint( $_POST['adp_rating'] );
			if ( $rating >= 1 && $rating <= 5 ) {
				update_comment_meta( $comment_id, '_adp_rating', $rating );
			}
		}
	}

	public static function get_average_rating( int $app_id ): ?float {
		$comments = get_comments(
			array(
				'post_id' => $app_id,
				'type'    => self::COMMENT_TYPE,
				'status'  => 'approve',
				'count'   => true,
			)
		);

		if ( ! $comments ) {
			return null;
		}

		$total = 0;
		$count = 0;
		$all   = get_comments(
			array(
				'post_id' => $app_id,
				'type'    => self::COMMENT_TYPE,
				'status'  => 'approve',
			)
		);

		foreach ( $all as $c ) {
			$r = (int) get_comment_meta( $c->comment_ID, '_adp_rating', true );
			if ( $r >= 1 && $r <= 5 ) {
				$total += $r;
				++$count;
			}
		}

		return $count > 0 ? round( $total / $count, 1 ) : null;
	}
}
