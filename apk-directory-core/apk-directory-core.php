<?php
/**
 * Plugin Name:       APK Directory Core
 * Plugin URI:        https://github.com/adp/apk-directory-core
 * Description:       Content types, metadata, versions, downloads, ratings, and business logic for APK Directory Pro.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            APK Directory
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       apk-directory-core
 * Domain Path:       /languages
 *
 * @package Adp\Core
 */

defined( 'ABSPATH' ) || exit;

define( 'ADP_CORE_VERSION', '1.0.0' );
define( 'ADP_CORE_FILE', __FILE__ );
define( 'ADP_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADP_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'ADP_CORE_BASENAME', plugin_basename( __FILE__ ) );

$adp_core_autoload = ADP_CORE_PATH . 'vendor/autoload.php';
if ( file_exists( $adp_core_autoload ) ) {
	require_once $adp_core_autoload;
}

require_once ADP_CORE_PATH . 'src/Plugin.php';

register_activation_hook( __FILE__, array( 'Adp\\Core\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Adp\\Core\\Deactivator', 'deactivate' ) );

\Adp\Core\Plugin::init();
