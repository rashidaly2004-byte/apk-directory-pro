<?php
/**
 * Run Theme Check against apk-directory-pro from WP-CLI context.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

$check_file = WP_PLUGIN_DIR . '/theme-check/checkbase.php';
if ( ! file_exists( $check_file ) ) {
	WP_CLI::error( 'Theme Check plugin not installed.' );
}

require_once $check_file;

$theme = wp_get_theme( 'apk-directory-pro' );
if ( ! $theme->exists() ) {
	WP_CLI::error( 'Theme apk-directory-pro not found.' );
}

WP_CLI::success( 'Theme Check loaded for: ' . $theme->get( 'Name' ) );
WP_CLI::log( 'Run manual review in Appearance → Theme Check for full results.' );
