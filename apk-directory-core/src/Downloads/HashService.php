<?php
/**
 * SHA-256 hash service for local attachments.
 *
 * @package Adp\Core\Downloads
 */

namespace Adp\Core\Downloads;

use Adp\Core\Versions\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Computes and stores file hashes.
 */
class HashService {

	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository|null $repository Optional repository.
	 */
	public function __construct( ?Repository $repository = null ) {
		$this->repository = $repository ?? new Repository();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'adp_core_hash_pending_files', array( self::class, 'process_pending' ) );
		if ( ! wp_next_scheduled( 'adp_core_hash_pending_files' ) ) {
			wp_schedule_event( time(), 'hourly', 'adp_core_hash_pending_files' );
		}
	}

	/**
	 * Compute SHA-256 for an attachment file.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|null Hash or null on failure.
	 */
	public function compute_for_attachment( int $attachment_id ): ?string {
		$path = get_attached_file( $attachment_id );
		if ( ! $path || ! file_exists( $path ) || ! is_readable( $path ) ) {
			return null;
		}
		$hash = hash_file( 'sha256', $path );
		return false !== $hash ? $hash : null;
	}

	/**
	 * Update version hash from attachment.
	 *
	 * @param int $version_id    Version ID.
	 * @param int $attachment_id Attachment ID.
	 * @return string|null Computed hash.
	 */
	public function update_version_hash( int $version_id, int $attachment_id ): ?string {
		$hash = $this->compute_for_attachment( $attachment_id );
		if ( null !== $hash ) {
			$this->repository->update( $version_id, array( 'sha256' => $hash ) );
		}
		return $hash;
	}

	/**
	 * Process versions missing hashes (cron).
	 *
	 * @return void
	 */
	public static function process_pending(): void {
		global $wpdb;
		$table   = \Adp\Core\Versions\Schema::table_name();
		$service = new self();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT id, attachment_id FROM {$table} WHERE download_type = 'media' AND attachment_id IS NOT NULL AND (sha256 IS NULL OR sha256 = '') LIMIT 10",
			ARRAY_A
		);
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $row ) {
			$service->update_version_hash( (int) $row['id'], (int) $row['attachment_id'] );
		}
	}

	/**
	 * Get hash status for display.
	 *
	 * @param int         $version_id Version ID.
	 * @param string|null $sha256     Existing hash.
	 * @param int|null    $attachment_id Attachment ID.
	 * @return array{status: string, hash: string|null}
	 */
	public function get_status( int $version_id, ?string $sha256, ?int $attachment_id ): array {
		unset( $version_id );
		if ( null !== $sha256 && '' !== $sha256 ) {
			return array(
				'status' => 'complete',
				'hash'   => $sha256,
			);
		}
		if ( null === $attachment_id || $attachment_id <= 0 ) {
			return array(
				'status' => 'none',
				'hash'   => null,
			);
		}
		return array(
			'status' => 'pending',
			'hash'   => null,
		);
	}
}
