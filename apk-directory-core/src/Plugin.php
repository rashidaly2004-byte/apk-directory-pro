<?php
/**
 * Main plugin bootstrap.
 *
 * @package Adp\Core
 */

namespace Adp\Core;

use Adp\Core\Admin\DemoImporter;
use Adp\Core\Admin\EditorPanels;
use Adp\Core\Admin\Metaboxes;
use Adp\Core\Admin\Notices;
use Adp\Core\Admin\Settings;
use Adp\Core\Admin\SetupWizard;
use Adp\Core\Cache\Invalidator;
use Adp\Core\Cli\Commands;
use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Capabilities;
use Adp\Core\Content\Meta;
use Adp\Core\Content\Taxonomies;
use Adp\Core\Content\TermMeta;
use Adp\Core\Downloads\Controller as DownloadController;
use Adp\Core\Downloads\HashService;
use Adp\Core\Migration\AppynMigrator;
use Adp\Core\Reports\Controller as ReportController;
use Adp\Core\Reports\ReportPostType;
use Adp\Core\Rest\AppController;
use Adp\Core\Rest\VersionsController;
use Adp\Core\Reviews\Controller as ReviewController;
use Adp\Core\Reviews\Schema as ReviewSchema;
use Adp\Core\Schema\BlogPosting;
use Adp\Core\Schema\SoftwareApplication;
use Adp\Core\Search\Controller as SearchController;
use Adp\Core\Seo\Sitemaps;
use Adp\Core\Versions\Admin as VersionAdmin;
use Adp\Core\Versions\Frontend as VersionsFrontend;
use Adp\Core\Versions\Schema as VersionSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin singleton bootstrap.
 */
final class Plugin {

	private static ?self $instance = null;

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
	}

	/**
	 * Constructor — register all components.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'boot' ), 0 );
		add_action( 'init', array( $this, 'register_blocks' ) );
		Commands::register();
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'apk-directory-core',
			false,
			dirname( ADP_CORE_BASENAME ) . '/languages'
		);
	}

	/**
	 * Boot all plugin components.
	 *
	 * @return void
	 */
	public function boot(): void {
		// Ensure DB table exists on upgrades.
		if ( get_option( 'adp_core_db_version' ) !== VersionSchema::VERSION ) {
			VersionSchema::create_table();
			update_option( 'adp_core_db_version', VersionSchema::VERSION, false );
		}

		// Content layer.
		AppPostType::init();
		Taxonomies::init();
		TermMeta::init();
		Meta::init();
		Capabilities::init();

		// Versions.
		VersionAdmin::init();
		VersionsFrontend::init();

		// Downloads.
		DownloadController::init();
		HashService::init();

		// Reviews & Reports.
		ReviewSchema::init();
		ReviewController::init();
		ReportPostType::init();
		ReportController::init();

		// Search & REST.
		SearchController::init();
		AppController::init();
		VersionsController::init();

		// Schema & SEO.
		SoftwareApplication::init();
		BlogPosting::init();
		Sitemaps::init();

		// Admin.
		Settings::init();
		SetupWizard::init();
		EditorPanels::init();
		Metaboxes::init();
		Notices::init();

		// Migration.
		AppynMigrator::init();

		// Cache & Privacy.
		Invalidator::init();
		Privacy::init();
	}

	/**
	 * Register server-rendered blocks.
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$blocks = array(
			'app-grid',
			'featured-categories',
			'trending-apps',
			'app-info-table',
			'download-button',
			'version-history',
			'related-apps',
			'safety-notice',
			'faq',
			'ad-slot',
		);

		foreach ( $blocks as $block ) {
			$block_dir = ADP_CORE_PATH . 'blocks/' . $block;
			if ( file_exists( $block_dir . '/block.json' ) ) {
				register_block_type( $block_dir );
			}
		}
	}
}
