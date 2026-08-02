<?php
/**
 * Plugin activation handler.
 *
 * @package Adp\Core
 */

namespace Adp\Core;

use Adp\Core\Content\Capabilities;
use Adp\Core\Versions\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation.
 */
class Activator {

	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate(): void {
		Schema::create_table();
		Capabilities::register();
		Capabilities::add_caps_to_roles();

		$settings = get_option( 'adp_core_settings', array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		if ( ! isset( $settings['local_uploads_enabled'] ) ) {
			$settings['local_uploads_enabled']  = true;
			$settings['default_download_type']  = 'media';
			$settings['download_token_ttl']     = 3600;
			$settings['external_url_allowlist'] = array();
			$settings['download_countdown']     = false;
			$settings['reviews_enabled']        = true;
			$settings['view_counter_enabled']   = false;
			update_option( 'adp_core_settings', $settings, false );
		}

		update_option( 'adp_core_version', ADP_CORE_VERSION, false );
		update_option( 'adp_core_db_version', Schema::VERSION, false );

		if ( ! get_option( 'adp_core_rewrites_flushed' ) ) {
			flush_rewrite_rules();
			update_option( 'adp_core_rewrites_flushed', true, false );
		}
	}
}
