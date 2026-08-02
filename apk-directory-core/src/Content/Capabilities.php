<?php

namespace APD\Core\Content;

final class Capabilities {

	public const UPLOAD_APK  = 'upload_apk_files';
	public const MANAGE_APPS = 'manage_adp_apps';

	public static function register(): void {
		add_filter( 'upload_mimes', array( self::class, 'allow_apk_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( self::class, 'check_apk_filetype' ), 10, 5 );
	}

	/**
	 * @param array<string, string> $mimes
	 * @return array<string, string>
	 */
	public static function allow_apk_mimes( array $mimes ): array {
		if ( ! current_user_can( 'upload_files' ) && ! current_user_can( self::UPLOAD_APK ) ) {
			return $mimes;
		}

		$mimes['apk']  = 'application/vnd.android.package-archive';
		$mimes['xapk'] = 'application/octet-stream';
		$mimes['apks'] = 'application/octet-stream';
		$mimes['obb']  = 'application/octet-stream';

		return $mimes;
	}

	/**
	 * Allow APK-family uploads when WordPress cannot detect the extension.
	 *
	 * @param array<string, string|false> $data     File type data.
	 * @param string                      $file     Full path to the file.
	 * @param string                      $filename File name.
	 * @param array|null                  $mimes    Mime types.
	 * @param bool                        $real_mime Whether real MIME was checked.
	 * @return array<string, string|false>
	 */
	public static function check_apk_filetype( array $data, string $file, string $filename, ?array $mimes, bool $real_mime ): array {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		$map = array(
			'apk'  => 'application/vnd.android.package-archive',
			'xapk' => 'application/octet-stream',
			'apks' => 'application/octet-stream',
			'obb'  => 'application/octet-stream',
		);

		if ( isset( $map[ $ext ] ) && ( current_user_can( 'upload_files' ) || current_user_can( self::UPLOAD_APK ) ) ) {
			$data['ext']  = $ext;
			$data['type'] = $map[ $ext ];
		}

		return $data;
	}

	public static function add_caps_to_roles(): void {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			$admin->add_cap( self::UPLOAD_APK );
			$admin->add_cap( self::MANAGE_APPS );
		}

		$editor = get_role( 'editor' );
		if ( $editor ) {
			$editor->add_cap( self::MANAGE_APPS );
		}
	}
}
