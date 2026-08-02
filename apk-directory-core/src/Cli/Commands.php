<?php
/**
 * WP-CLI commands.
 *
 * @package Adp\Core\Cli
 */

namespace Adp\Core\Cli;

use Adp\Core\Activator;
use Adp\Core\Migration\AppynMigrator;
use Adp\Core\Versions\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * WP-CLI command registration.
 */
class Commands {

	/**
	 * Register CLI commands.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		\WP_CLI::add_command( 'adp', self::class );
	}

	/**
	 * Show plugin status.
	 *
	 * ## EXAMPLES
	 *
	 *     wp adp status
	 *
	 * @param array<int, string>    $args       Positional args.
	 * @param array<string, mixed>  $assoc_args Associative args.
	 * @return void
	 */
	public function status( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );
		global $wpdb;
		$table  = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$apps   = wp_count_posts( 'adp_app' );

		\WP_CLI::log( 'APK Directory Core v' . ADP_CORE_VERSION );
		\WP_CLI::log( 'DB version: ' . get_option( 'adp_core_db_version', 'unknown' ) );
		\WP_CLI::log( 'Versions in table: ' . $count );
		\WP_CLI::log( 'Published apps: ' . (int) ( $apps->publish ?? 0 ) );
	}

	/**
	 * Run migration dry-run.
	 *
	 * ## EXAMPLES
	 *
	 *     wp adp migrate --dry-run
	 *
	 * @param array<int, string>    $args       Positional args.
	 * @param array<string, mixed>  $assoc_args Associative args.
	 * @return void
	 */
	public function migrate( array $args, array $assoc_args ): void {
		unset( $args );
		if ( ! empty( $assoc_args['dry-run'] ) ) {
			$result = AppynMigrator::dry_run();
			foreach ( $result['log'] as $line ) {
				\WP_CLI::log( $line );
			}
			\WP_CLI::success( 'Dry run complete.' );
			return;
		}

		$result = AppynMigrator::start_import();
		\WP_CLI::success( 'Import started. ' . count( $result['post_ids'] ?? array() ) . ' posts queued.' );
	}

	/**
	 * Flush rewrite rules.
	 *
	 * ## EXAMPLES
	 *
	 *     wp adp flush-rewrites
	 *
	 * @param array<int, string>    $args       Positional args.
	 * @param array<string, mixed>  $assoc_args Associative args.
	 * @return void
	 */
	public function flush_rewrites( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );
		flush_rewrite_rules();
		\WP_CLI::success( 'Rewrite rules flushed.' );
	}

	/**
	 * Re-run activation tasks.
	 *
	 * ## EXAMPLES
	 *
	 *     wp adp activate
	 *
	 * @param array<int, string>    $args       Positional args.
	 * @param array<string, mixed>  $assoc_args Associative args.
	 * @return void
	 */
	public function activate( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );
		Activator::activate();
		\WP_CLI::success( 'Activation tasks completed.' );
	}
}
