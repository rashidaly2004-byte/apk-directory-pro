<?php

namespace APD\Core\Content;

final class Capabilities {

	public const UPLOAD_APK = 'upload_apk_files';
	public const MANAGE_APPS = 'manage_adp_apps';

	public static function register(): void {
		// Capabilities are added on activation via add_caps_to_roles.
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
