<?php

namespace APD\Core;

use APD\Core\Admin\EditorPanels;
use APD\Core\Admin\Metabox;
use APD\Core\Admin\Settings;
use APD\Core\Admin\SetupWizard;
use APD\Core\Content\AppPostType;
use APD\Core\Content\Capabilities;
use APD\Core\Content\Meta;
use APD\Core\Content\Taxonomies;
use APD\Core\Downloads\Controller as DownloadController;
use APD\Core\Migration\AppynMigrator;
use APD\Core\Reports\Controller as ReportController;
use APD\Core\Reviews\Controller as ReviewController;
use APD\Core\Schema\SoftwareApplication;
use APD\Core\Search\Controller as SearchController;
use APD\Core\Versions\Admin as VersionAdmin;
use APD\Core\Versions\Repository;

final class Plugin {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot(): void {
		register_activation_hook( ADP_CORE_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( ADP_CORE_FILE, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function activate(): void {
		Capabilities::register();
		Capabilities::add_caps_to_roles();
		Repository::create_table();
		flush_rewrite_rules();
		set_transient( 'adp_activation_redirect', true, 30 );
	}

	public function deactivate(): void {
		flush_rewrite_rules();
	}

	public function init(): void {
		load_plugin_textdomain( 'apk-directory-pro', false, dirname( plugin_basename( ADP_CORE_FILE ) ) . '/languages' );

		Capabilities::register();
		Capabilities::add_caps_to_roles();

		( new AppPostType() )->register();
		( new Taxonomies() )->register();
		( new Meta() )->register();

		Repository::maybe_upgrade();

		( new VersionAdmin() )->register();
		( new EditorPanels() )->register();
		( new Metabox() )->register();
		( new Settings() )->register();
		( new SetupWizard() )->register();
		( new DownloadController() )->register();
		( new SearchController() )->register();
		( new ReviewController() )->register();
		( new ReportController() )->register();
		( new SoftwareApplication() )->register();
		( new AppynMigrator() )->register();
		( new Privacy() )->register();

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'adp', Cli\Commands::class );
		}

		add_action( 'pre_get_posts', array( $this, 'extend_search' ) );
	}

	public function extend_search( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}
		$query->set( 'post_type', array( 'post', AppPostType::POST_TYPE ) );
	}
}
