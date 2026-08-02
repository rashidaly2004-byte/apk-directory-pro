<?php
/**
 * Privacy policy helper text.
 *
 * @package Adp\Core
 */

namespace Adp\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Provides privacy-related content and hooks.
 */
class Privacy {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( self::class, 'register_policy_content' ) );
	}

	/**
	 * Register suggested privacy policy text.
	 *
	 * @return void
	 */
	public static function register_policy_content(): void {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content = self::get_policy_text();
		wp_add_privacy_policy_content(
			__( 'APK Directory Core', 'apk-directory-core' ),
			wp_kses_post( $content )
		);
	}

	/**
	 * Get privacy policy suggested text.
	 *
	 * @return string
	 */
	public static function get_policy_text(): string {
		return implode(
			"\n\n",
			array(
				__( 'When you download an app from this site, we use a privacy-preserving cookie to count downloads once per browser without storing personal information.', 'apk-directory-core' ),
				__( 'If you submit a review, we collect your name, email address, and rating. Reviews are moderated before publication.', 'apk-directory-core' ),
				__( 'If you submit a report about an app, we store the reason and details you provide. Your email is optional and used only for follow-up if provided.', 'apk-directory-core' ),
				__( 'Search queries may be temporarily cached to improve performance. We rate-limit search requests to prevent abuse.', 'apk-directory-core' ),
			)
		);
	}
}
