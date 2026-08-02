<?php
/**
 * Versions database schema.
 *
 * @package Adp\Core\Versions
 */

namespace Adp\Core\Versions;

defined( 'ABSPATH' ) || exit;

/**
 * Creates and manages the adp_versions table.
 */
class Schema {

	public const VERSION = '1.0.0';
	public const TABLE   = 'adp_versions';

	/**
	 * Get full table name with prefix.
	 *
	 * @return string
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Create or upgrade the versions table.
	 *
	 * @return void
	 */
	public static function create_table(): void {
		global $wpdb;

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			app_id bigint(20) unsigned NOT NULL,
			version_name varchar(64) NOT NULL DEFAULT '',
			version_code bigint(20) DEFAULT NULL,
			release_date datetime DEFAULT NULL,
			changelog longtext,
			min_android varchar(32) DEFAULT NULL,
			file_size_bytes bigint(20) unsigned NOT NULL DEFAULT 0,
			file_type varchar(16) NOT NULL DEFAULT 'apk',
			architectures_json longtext,
			dpi_json longtext,
			download_type varchar(16) NOT NULL DEFAULT 'media',
			attachment_id bigint(20) unsigned DEFAULT NULL,
			external_url text DEFAULT NULL,
			sha256 char(64) DEFAULT NULL,
			signature_sha256 char(64) DEFAULT NULL,
			virus_scan_status varchar(16) NOT NULL DEFAULT 'unknown',
			virus_scan_date datetime DEFAULT NULL,
			is_current tinyint(1) NOT NULL DEFAULT 0,
			download_count bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY app_id (app_id),
			KEY is_current (app_id, is_current),
			KEY version_code (version_code)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
