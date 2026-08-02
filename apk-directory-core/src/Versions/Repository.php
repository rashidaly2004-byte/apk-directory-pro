<?php
/**
 * Versions repository — prepared SQL CRUD.
 *
 * @package Adp\Core\Versions
 */

namespace Adp\Core\Versions;

defined( 'ABSPATH' ) || exit;

/**
 * Data access layer for adp_versions table.
 */
class Repository {

	/**
	 * Find version by ID.
	 *
	 * @param int $id Version ID.
	 * @return array<string, mixed>|null
	 */
	public function find( int $id ): ?array {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
		return is_array( $row ) ? $this->hydrate( $row ) : null;
	}

	/**
	 * Find current version for an app.
	 *
	 * @param int $app_id App post ID.
	 * @return array<string, mixed>|null
	 */
	public function find_current( int $app_id ): ?array {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE app_id = %d AND is_current = 1 LIMIT 1",
				$app_id
			),
			ARRAY_A
		);
		return is_array( $row ) ? $this->hydrate( $row ) : null;
	}

	/**
	 * List versions for an app.
	 *
	 * @param int $app_id  App post ID.
	 * @param int $limit   Max rows.
	 * @param int $offset  Offset.
	 * @return array<int, array<string, mixed>>
	 */
	public function list_by_app( int $app_id, int $limit = 50, int $offset = 0 ): array {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE app_id = %d ORDER BY is_current DESC, version_code DESC, id DESC LIMIT %d OFFSET %d",
				$app_id,
				$limit,
				$offset
			),
			ARRAY_A
		);
		if ( ! is_array( $rows ) ) {
			return array();
		}
		return array_map( array( $this, 'hydrate' ), $rows );
	}

	/**
	 * Count versions for an app.
	 *
	 * @param int $app_id App post ID.
	 * @return int
	 */
	public function count_by_app( int $app_id ): int {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE app_id = %d", $app_id )
		);
	}

	/**
	 * Insert a version row.
	 *
	 * @param array<string, mixed> $data Version data.
	 * @return int Inserted ID or 0 on failure.
	 */
	public function insert( array $data ): int {
		global $wpdb;
		$table  = Schema::table_name();
		$record = $this->prepare_for_db( $data );
		$result = $wpdb->insert( $table, $record['data'], $record['format'] );
		return false === $result ? 0 : (int) $wpdb->insert_id;
	}

	/**
	 * Update a version row.
	 *
	 * @param int                  $id   Version ID.
	 * @param array<string, mixed> $data Version data.
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		global $wpdb;
		$table  = Schema::table_name();
		$record = $this->prepare_for_db( $data, false );
		$result = $wpdb->update(
			$table,
			$record['data'],
			array( 'id' => $id ),
			$record['format'],
			array( '%d' )
		);
		return false !== $result;
	}

	/**
	 * Delete a version row.
	 *
	 * @param int $id Version ID.
	 * @return bool
	 */
	public function delete( int $id ): bool {
		global $wpdb;
		$table  = Schema::table_name();
		$result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
		return false !== $result && $result > 0;
	}

	/**
	 * Clear current flag for all versions of an app.
	 *
	 * @param int $app_id App post ID.
	 * @return void
	 */
	public function clear_current( int $app_id ): void {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET is_current = 0 WHERE app_id = %d",
				$app_id
			)
		);
	}

	/**
	 * Set a version as current.
	 *
	 * @param int $id     Version ID.
	 * @param int $app_id App post ID.
	 * @return void
	 */
	public function set_current( int $id, int $app_id ): void {
		global $wpdb;
		$table = Schema::table_name();
		$this->clear_current( $app_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET is_current = 1 WHERE id = %d AND app_id = %d",
				$id,
				$app_id
			)
		);
	}

	/**
	 * Increment download count.
	 *
	 * @param int $id Version ID.
	 * @return void
	 */
	public function increment_download_count( int $id ): void {
		global $wpdb;
		$table = Schema::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET download_count = download_count + 1 WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Hydrate row with decoded JSON fields.
	 *
	 * @param array<string, mixed> $row Database row.
	 * @return array<string, mixed>
	 */
	private function hydrate( array $row ): array {
		$row['id']              = (int) $row['id'];
		$row['app_id']          = (int) $row['app_id'];
		$row['version_code']    = null !== $row['version_code'] ? (int) $row['version_code'] : null;
		$row['file_size_bytes'] = (int) $row['file_size_bytes'];
		$row['attachment_id']   = null !== $row['attachment_id'] ? (int) $row['attachment_id'] : null;
		$row['is_current']      = (bool) (int) $row['is_current'];
		$row['download_count']  = (int) $row['download_count'];
		$row['architectures']   = $this->decode_json( (string) ( $row['architectures_json'] ?? '' ) );
		$row['dpi']             = $this->decode_json( (string) ( $row['dpi_json'] ?? '' ) );
		unset( $row['architectures_json'], $row['dpi_json'] );
		return $row;
	}

	/**
	 * Decode JSON column.
	 *
	 * @param string $json JSON string.
	 * @return array<int, string>
	 */
	private function decode_json( string $json ): array {
		if ( '' === $json ) {
			return array();
		}
		$decoded = json_decode( $json, true );
		return is_array( $decoded ) ? array_values( array_map( 'strval', $decoded ) ) : array();
	}

	/**
	 * Prepare data for database insert/update.
	 *
	 * @param array<string, mixed> $data   Input data.
	 * @param bool                 $insert Whether inserting.
	 * @return array{data: array<string, mixed>, format: array<int, string>}
	 */
	private function prepare_for_db( array $data, bool $insert = true ): array {
		$allowed = array(
			'app_id'             => '%d',
			'version_name'       => '%s',
			'version_code'       => '%d',
			'release_date'       => '%s',
			'changelog'          => '%s',
			'min_android'        => '%s',
			'file_size_bytes'    => '%d',
			'file_type'          => '%s',
			'architectures_json' => '%s',
			'dpi_json'           => '%s',
			'download_type'      => '%s',
			'attachment_id'      => '%d',
			'external_url'       => '%s',
			'sha256'             => '%s',
			'signature_sha256'   => '%s',
			'virus_scan_status'  => '%s',
			'virus_scan_date'    => '%s',
			'is_current'         => '%d',
			'download_count'     => '%d',
		);

		$db_data   = array();
		$db_format = array();

		if ( isset( $data['architectures'] ) ) {
			$data['architectures_json'] = wp_json_encode( array_values( $data['architectures'] ) );
		}
		if ( isset( $data['dpi'] ) ) {
			$data['dpi_json'] = wp_json_encode( array_values( $data['dpi'] ) );
		}

		foreach ( $allowed as $key => $format ) {
			if ( array_key_exists( $key, $data ) ) {
				$db_data[ $key ] = $data[ $key ];
				$db_format[]     = $format;
			} elseif ( $insert && 'is_current' === $key ) {
				$db_data[ $key ] = 0;
				$db_format[]     = $format;
			}
		}

		return array(
			'data'   => $db_data,
			'format' => $db_format,
		);
	}
}
