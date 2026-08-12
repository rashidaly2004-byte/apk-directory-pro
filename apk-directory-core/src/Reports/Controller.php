<?php

namespace APD\Core\Reports;

use APD\Core\Support\RateLimiter;

final class Controller {

	public const POST_TYPE = 'adp_report';

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => 'edit.php?post_type=adp_app',
				'labels'             => array(
					'name'          => __( 'App Reports', 'apk-directory-pro' ),
					'singular_name' => __( 'App Report', 'apk-directory-pro' ),
				),
				'supports'           => array( 'title', 'editor' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
			)
		);
	}

	public function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/apps/(?P<id>\d+)/report',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_report' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function submit_report( \WP_REST_Request $request ): \WP_REST_Response {
		$app_id  = (int) $request->get_param( 'id' );
		$reason  = sanitize_text_field( $request->get_param( 'reason' ) ?? '' );
		$details = sanitize_textarea_field( $request->get_param( 'details' ) ?? '' );
		$email   = sanitize_email( $request->get_param( 'email' ) ?? '' );
		$consent = (bool) $request->get_param( 'consent' );

		$allowed_reasons = array( 'broken_link', 'outdated', 'malware', 'copyright', 'incorrect', 'other' );
		if ( ! in_array( $reason, $allowed_reasons, true ) ) {
			return new \WP_REST_Response( array( 'error' => 'invalid_reason' ), 400 );
		}

		if ( ! $consent ) {
			return new \WP_REST_Response( array( 'error' => 'consent_required' ), 400 );
		}

		// Honeypot via header or param.
		if ( $request->get_param( 'hp' ) ) {
			return new \WP_REST_Response( array( 'success' => true ), 200 );
		}

		/**
		 * Filters the reports allowed per window. Set to 0 to disable the limit.
		 */
		$limit = (int) apply_filters( 'adp_report_rate_limit', 5 );

		/** Filters the report rate limit window, in seconds. */
		$window = (int) apply_filters( 'adp_report_rate_window', 3600 );

		if ( ! RateLimiter::hit( 'report', $limit, $window ) ) {
			return new \WP_REST_Response( array( 'error' => 'rate_limited' ), 429 );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => sprintf( 'Report: App %d — %s', $app_id, $reason ),
				'post_content' => $details,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'error' => 'save_failed' ), 500 );
		}

		update_post_meta( $post_id, '_adp_report_app_id', $app_id );
		update_post_meta( $post_id, '_adp_report_reason', $reason );
		if ( $email ) {
			update_post_meta( $post_id, '_adp_report_email', $email );
		}

		do_action( 'adp_report_submitted', $post_id, $app_id, $reason );

		return new \WP_REST_Response( array( 'success' => true ), 201 );
	}
}
