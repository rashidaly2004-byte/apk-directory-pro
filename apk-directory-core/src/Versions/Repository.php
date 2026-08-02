<?php

namespace APD\Core\Versions;

final class Repository {

	private const DB_VERSION = '1.0.0';

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'adp_versions';
	}

	public static function create_table(): void {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			app_id bigint(20) unsigned NOT NULL,
			version_name varchar(100) NOT NULL DEFAULT '',
			version_code bigint(20) DEFAULT NULL,
			release_date datetime DEFAULT NULL,
			changelog longtext,
			min_android varchar(50) DEFAULT NULL,
			file_size_bytes bigint(20) unsigned NOT NULL DEFAULT 0,
			file_type varchar(10) NOT NULL DEFAULT 'apk',
			architectures_json longtext,
			dpi_json longtext,
			download_type varchar(20) NOT NULL DEFAULT 'media',
			attachment_id bigint(20) unsigned DEFAULT NULL,
			external_url text,
			sha256 char(64) DEFAULT NULL,
			signature_sha256 char(64) DEFAULT NULL,
			virus_scan_status varchar(20) NOT NULL DEFAULT 'unknown',
			virus_scan_date datetime DEFAULT NULL,
			is_current tinyint(1) NOT NULL DEFAULT 0,
			download_count bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY app_id (app_id),
			KEY is_current (is_current)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'adp_db_version', self::DB_VERSION );
	}

	public static function maybe_upgrade(): void {
		if ( get_option( 'adp_db_version' ) !== self::DB_VERSION ) {
			self::create_table();
		}
	}

	public function find( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', self::table_name(), $id ),
			ARRAY_A
		);
		return $row ?: null;
	}

	public function find_by_app( int $app_id, int $limit = 20, int $offset = 0 ): array {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE app_id = %d ORDER BY is_current DESC, release_date DESC, id DESC LIMIT %d OFFSET %d',
				self::table_name(),
				$app_id,
				$limit,
				$offset
			),
			ARRAY_A
		) ?: array();
	}

	public function count_by_app( int $app_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE app_id = %d', self::table_name(), $app_id )
		);
	}

	public function get_current( int $app_id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE app_id = %d AND is_current = 1 LIMIT 1',
				self::table_name(),
				$app_id
			),
			ARRAY_A
		);
		return $row ?: null;
	}

	public function insert( array $data ): int {
		global $wpdb;
		$wpdb->insert( self::table_name(), $this->prepare_row( $data ) );
		return (int) $wpdb->insert_id;
	}

	public function update( int $id, array $data ): bool {
		global $wpdb;
		return (bool) $wpdb->update( self::table_name(), $this->prepare_row( $data ), array( 'id' => $id ) );
	}

	public function delete( int $id ): bool {
		global $wpdb;
		return (bool) $wpdb->delete( self::table_name(), array( 'id' => $id ) );
	}

	public function set_current( int $app_id, int $version_id ): void {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare( 'UPDATE %i SET is_current = 0 WHERE app_id = %d', self::table_name(), $app_id )
		);
		$wpdb->update(
			self::table_name(),
			array( 'is_current' => 1 ),
			array(
				'id'     => $version_id,
				'app_id' => $app_id,
			)
		);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	private function prepare_row( array $data ): array {
		$allowed = array(
			'app_id',
			'version_name',
			'version_code',
			'release_date',
			'changelog',
			'min_android',
			'file_size_bytes',
			'file_type',
			'architectures_json',
			'dpi_json',
			'download_type',
			'attachment_id',
			'external_url',
			'sha256',
			'signature_sha256',
			'virus_scan_status',
			'virus_scan_date',
			'is_current',
			'download_count',
		);
		return array_intersect_key( $data, array_flip( $allowed ) );
	}
}
