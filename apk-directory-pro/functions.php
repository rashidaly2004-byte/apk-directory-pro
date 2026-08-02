<?php
/**
 * APK Directory Pro bootstrap.
 */

defined( 'ABSPATH' ) || exit;

define( 'ADP_THEME_VERSION', '1.0.0' );
define( 'ADP_THEME_PATH', get_template_directory() );
define( 'ADP_THEME_URI', get_template_directory_uri() );

$adp_autoload = ADP_THEME_PATH . '/vendor/autoload.php';
if ( file_exists( $adp_autoload ) ) {
	require_once $adp_autoload;
}

require_once ADP_THEME_PATH . '/inc/helpers.php';

APD\Theme\Setup::init();
APD\Theme\Assets::init();
APD\Theme\Customizer::init();
APD\Theme\TemplateHooks::init();
APD\Theme\SeoCompatibility::init();
APD\Theme\Accessibility::init();
