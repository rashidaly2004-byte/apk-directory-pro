<?php
/**
 * Custom capabilities and APK Manager role.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Capability management.
 */
class Capabilities {

	public const UPLOAD_APK = 'upload_apk_files';
	public const ROLE       = 'apk_manager';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 5 );
		add_filter( 'upload_mimes', array( self::class, 'allow_apk_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( self::class, 'check_apk_filetype' ), 10, 4 );
	}

	/**
	 * Register APK Manager role.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( null === get_role( self::ROLE ) ) {
			add_role(
				self::ROLE,
				__( 'APK Manager', 'apk-directory-core' ),
				array(
					'read'                   => true,
					'upload_files'           => true,
					self::UPLOAD_APK         => true,
					'edit_adp_apps'          => true,
					'edit_published_adp_apps' => true,
					'publish_adp_apps'       => true,
					'delete_adp_apps'        => true,
					'delete_published_adp_apps' => true,
					'edit_adp_app'           => true,
					'read_adp_app'           => true,
					'delete_adp_app'         => true,
				)
			);
		}
	}

	/**
	 * Add capabilities to administrator and editor roles.
	 *
	 * @return void
	 */
	public static function add_caps_to_roles(): void {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( self::UPLOAD_APK );
			foreach ( self::get_app_caps() as $cap ) {
				$admin->add_cap( $cap );
			}
		}

		$editor = get_role( 'editor' );
		if ( $editor ) {
			foreach ( self::get_app_caps() as $cap ) {
				$editor->add_cap( $cap );
			}
		}
	}

	/**
	 * App-related capabilities.
	 *
	 * @return array<int, string>
	 */
	public static function get_app_caps(): array {
		return array(
			'edit_adp_app',
			'read_adp_app',
			'delete_adp_app',
			'edit_adp_apps',
			'edit_others_adp_apps',
			'publish_adp_apps',
			'read_private_adp_apps',
			'delete_adp_apps',
			'delete_private_adp_apps',
			'delete_published_adp_apps',
			'delete_others_adp_apps',
			'edit_private_adp_apps',
			'edit_published_adp_apps',
			'create_adp_apps',
		);
	}

	/**
	 * Check if current user can upload APK files.
	 *
	 * @return bool
	 */
	public static function can_upload_apk(): bool {
		return current_user_can( self::UPLOAD_APK );
	}

	/**
	 * Allow APK MIME types for authorized users.
	 *
	 * @param array<string, string> $mimes MIME types.
	 * @return array<string, string>
	 */
	public static function allow_apk_mimes( array $mimes ): array {
		if ( ! self::can_upload_apk() ) {
			return $mimes;
		}
		$mimes['apk']  = 'application/vnd.android.package-archive';
		$mimes['xapk'] = 'application/octet-stream';
		$mimes['apks'] = 'application/octet-stream';
		$mimes['obb']  = 'application/octet-stream';
		return $mimes;
	}

	/**
	 * Ensure APK file extensions are recognized.
	 *
	 * @param array<string, mixed> $data     File data.
	 * @param string               $file     File path.
	 * @param string               $filename Filename.
	 * @param array<string, mixed> $mimes    MIME types.
	 * @return array<string, mixed>
	 */
	public static function check_apk_filetype( array $data, string $file, string $filename, array $mimes ): array {
		unset( $file, $mimes );
		if ( ! self::can_upload_apk() ) {
			return $data;
		}
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		$map = array(
			'apk'  => array( 'type' => 'application/vnd.android.package-archive', 'ext' => 'apk' ),
			'xapk' => array( 'type' => 'application/octet-stream', 'ext' => 'xapk' ),
			'apks' => array( 'type' => 'application/octet-stream', 'ext' => 'apks' ),
			'obb'  => array( 'type' => 'application/octet-stream', 'ext' => 'obb' ),
		);
		if ( isset( $map[ $ext ] ) && ( empty( $data['ext'] ) || empty( $data['type'] ) ) ) {
			$data['ext']  = $map[ $ext ]['ext'];
			$data['type'] = $map[ $ext ]['type'];
		}
		return $data;
	}
}
