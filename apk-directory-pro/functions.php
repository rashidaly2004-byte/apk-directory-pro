<?php
/**
 * APK Directory Pro theme bootstrap.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADP_THEME_VERSION', '1.0.0' );
define( 'ADP_THEME_DIR', get_template_directory() );
define( 'ADP_THEME_URI', get_template_directory_uri() );

$adp_autoload = ADP_THEME_DIR . '/vendor/autoload.php';
if ( file_exists( $adp_autoload ) ) {
	require_once $adp_autoload;
} else {
	require_once ADP_THEME_DIR . '/inc/helpers.php';
	require_once ADP_THEME_DIR . '/inc/Setup.php';
	require_once ADP_THEME_DIR . '/inc/Assets.php';
	require_once ADP_THEME_DIR . '/inc/Customizer.php';
	require_once ADP_THEME_DIR . '/inc/TemplateHooks.php';
	require_once ADP_THEME_DIR . '/inc/SeoCompatibility.php';
	require_once ADP_THEME_DIR . '/inc/Accessibility.php';
}

/**
 * Initialize theme modules.
 */
function adp_theme_init(): void {
	Adp\Theme\Setup::init();
	Adp\Theme\Assets::init();
	Adp\Theme\Customizer::init();
	Adp\Theme\TemplateHooks::init();
	Adp\Theme\SeoCompatibility::init();
	Adp\Theme\Accessibility::init();
}
add_action( 'after_setup_theme', 'adp_theme_init' );
