<?php
/**
 * Plugin deactivation handler.
 *
 * @package Adp\Core
 */

namespace Adp\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation.
 */
class Deactivator {

	/**
	 * Run deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
		wp_clear_scheduled_hook( 'adp_core_hash_pending_files' );
		wp_clear_scheduled_hook( 'adp_core_migration_batch' );
	}
}
