<?php
/**
 * App report submission controller.
 *
 * @package Adp\Core\Reports
 */

namespace Adp\Core\Reports;

use Adp\Core\Content\AppPostType;
use Adp\Core\Support\RateLimiter;

defined( 'ABSPATH' ) || exit;

/**
 * Handles report form submissions.
 */
class Controller {

	public const REASONS = array(
		'broken_link',
		'outdated_version',
		'malware_concern',
		'copyright',
		'incorrect_information',
		'other',
	);

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
			'/apps/(?P<id>\d+)/report',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'submit_report' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'id'       => array(
							'required'          => true,
							'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
						),
						'reason'   => array(
							'required' => true,
							'type'     => 'string',
							'enum'     => self::REASONS,
						),
						'details'  => array(
							'type' => 'string',
						),
						'email'    => array(
							'type' => 'string',
						),
						'consent'  => array(
							'required' => true,
							'type'     => 'boolean',
						),
						'nonce'    => array(
							'required' => true,
							'type'     => 'string',
						),
						'company'  => array(
							'type' => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * Submit a report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function submit_report( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		if ( AppPostType::POST_TYPE !== get_post_type( $post_id ) ) {
			return new \WP_Error( 'adp_invalid_app', __( 'Invalid app.', 'apk-directory-core' ), array( 'status' => 404 ) );
		}

		$nonce = sanitize_text_field( (string) $request->get_param( 'nonce' ) );
		if ( ! wp_verify_nonce( $nonce, 'adp_submit_report_' . $post_id ) ) {
			return new \WP_Error( 'adp_invalid_nonce', __( 'Security check failed.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		// Honeypot.
		if ( '' !== sanitize_text_field( (string) $request->get_param( 'company' ) ) ) {
			return new \WP_REST_Response( array( 'success' => true ), 201 );
		}

		if ( ! $request->get_param( 'consent' ) ) {
			return new \WP_Error( 'adp_consent_required', __( 'Consent is required.', 'apk-directory-core' ), array( 'status' => 400 ) );
		}

		$rate_key = RateLimiter::key( 'report', (string) $post_id );
		if ( ! RateLimiter::attempt( $rate_key, 2, HOUR_IN_SECONDS ) ) {
			return new \WP_Error( 'adp_rate_limited', __( 'Too many reports. Please try again later.', 'apk-directory-core' ), array( 'status' => 429 ) );
		}

		/**
		 * Filter report before submission.
		 *
		 * @param bool $allowed Whether submission is allowed.
		 * @param int  $post_id App post ID.
		 */
		$captcha_ok = apply_filters( 'adp_report_captcha_verify', true, $post_id );
		if ( ! $captcha_ok ) {
			return new \WP_Error( 'adp_captcha_failed', __( 'CAPTCHA verification failed.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		$reason  = sanitize_key( (string) $request->get_param( 'reason' ) );
		$details = sanitize_textarea_field( (string) $request->get_param( 'details' ) );
		$email   = sanitize_email( (string) $request->get_param( 'email' ) );

		$app_title = get_the_title( $post_id );
		$title     = sprintf(
			/* translators: 1: app title, 2: reason */
			__( 'Report: %1$s — %2$s', 'apk-directory-core' ),
			$app_title,
			$reason
		);

		$content = sprintf(
			"App ID: %d\nApp: %s\nReason: %s\nDetails: %s\nReporter email: %s\nIP hash: %s",
			$post_id,
			$app_title,
			$reason,
			$details,
			$email ? '[provided]' : '[not provided]',
			md5( RateLimiter::client_id() )
		);

		$report_id = wp_insert_post(
			array(
				'post_type'    => ReportPostType::POST_TYPE,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'private',
				'meta_input'   => array(
					'_adp_report_app_id'  => $post_id,
					'_adp_report_reason'  => $reason,
					'_adp_report_details' => $details,
					'_adp_report_email'   => $email,
				),
			),
			true
		);

		if ( is_wp_error( $report_id ) ) {
			return $report_id;
		}

		self::notify_admin( $report_id, $post_id, $reason );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Thank you. Your report has been submitted.', 'apk-directory-core' ),
			),
			201
		);
	}

	/**
	 * Notify admin of new report.
	 *
	 * @param int    $report_id Report post ID.
	 * @param int    $app_id    App post ID.
	 * @param string $reason    Report reason.
	 * @return void
	 */
	private static function notify_admin( int $report_id, int $app_id, string $reason ): void {
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf(
			/* translators: %s: site name */
			__( '[%s] New App Report', 'apk-directory-core' ),
			get_bloginfo( 'name' )
		);
		$message = sprintf(
			"A new app report was submitted.\n\nApp: %s (ID: %d)\nReason: %s\n\nView: %s",
			get_the_title( $app_id ),
			$app_id,
			$reason,
			admin_url( 'post.php?post=' . $report_id . '&action=edit' )
		);
		wp_mail( $admin_email, $subject, $message );
	}
}
