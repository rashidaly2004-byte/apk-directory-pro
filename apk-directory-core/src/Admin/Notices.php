<?php
/**
 * Admin notices.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Displays admin notices for permissions and setup.
 */
class Notices {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_notices', array( self::class, 'upload_capability_notice' ) );
	}

	/**
	 * Notice for users without APK upload capability.
	 *
	 * @return void
	 */
	public static function upload_capability_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'adp_app' !== $screen->post_type ) {
			return;
		}
		if ( \Adp\Core\Content\Capabilities::can_upload_apk() ) {
			return;
		}
		if ( ! current_user_can( 'edit_adp_apps' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Your role does not have permission to upload APK files. Contact an administrator to grant the upload_apk_files capability.', 'apk-directory-core' )
		);
	}
}
