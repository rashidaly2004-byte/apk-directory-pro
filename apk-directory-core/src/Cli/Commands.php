<?php

namespace APD\Core\Cli;

use APD\Core\Versions\Repository;

class Commands {

	/**
	 * Flush ADP rewrite rules.
	 */
	public function flush_rewrites(): void {
		flush_rewrite_rules();
		\WP_CLI::success( 'Rewrite rules flushed.' );
	}

	/**
	 * Run Appyn migration dry-run.
	 */
	public function migration_dry_run(): void {
		$migrator = new \APD\Core\Migration\AppynMigrator();
		$count    = $migrator->dry_run();
		\WP_CLI::success( sprintf( '%d posts would be migrated.', $count ) );
	}

	/**
	 * Show version table status.
	 */
	public function db_status(): void {
		$version = get_option( 'adp_db_version', 'none' );
		\WP_CLI::log( 'DB version: ' . $version );
		\WP_CLI::log( 'Table: ' . Repository::table_name() );
	}
}
