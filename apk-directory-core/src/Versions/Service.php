<?php
/**
 * Versions business logic service.
 *
 * @package Adp\Core\Versions
 */

namespace Adp\Core\Versions;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Support\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Version service with one-current-version enforcement.
 */
class Service {

	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository|null $repository Optional repository instance.
	 */
	public function __construct( ?Repository $repository = null ) {
		$this->repository = $repository ?? new Repository();
	}

	/**
	 * Get repository instance.
	 *
	 * @return Repository
	 */
	public function get_repository(): Repository {
		return $this->repository;
	}

	/**
	 * Create a new version.
	 *
	 * @param array<string, mixed> $data Version data.
	 * @return int|\WP_Error Version ID or error.
	 */
	public function create( array $data ): int|\WP_Error {
		$validated = $this->validate( $data );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$set_current = ! empty( $data['is_current'] );
		$validated['is_current'] = 0;

		$this->begin_transaction();

		$id = $this->repository->insert( $validated );
		if ( $id <= 0 ) {
			$this->rollback();
			return new \WP_Error( 'adp_version_create_failed', __( 'Failed to create version.', 'apk-directory-core' ) );
		}

		if ( $set_current || null === $this->repository->find_current( (int) $validated['app_id'] ) ) {
			$this->repository->set_current( $id, (int) $validated['app_id'] );
			$this->sync_app_meta( (int) $validated['app_id'], $id );
		}

		$this->commit();
		return $id;
	}

	/**
	 * Update an existing version.
	 *
	 * @param int                  $id   Version ID.
	 * @param array<string, mixed> $data Version data.
	 * @return true|\WP_Error
	 */
	public function update( int $id, array $data ): true|\WP_Error {
		$existing = $this->repository->find( $id );
		if ( null === $existing ) {
			return new \WP_Error( 'adp_version_not_found', __( 'Version not found.', 'apk-directory-core' ) );
		}

		$merged    = array_merge( $existing, $data );
		$validated = $this->validate( $merged, $id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		unset( $validated['id'], $validated['architectures'], $validated['dpi'], $validated['created_at'], $validated['updated_at'] );

		$set_current = ! empty( $data['is_current'] );

		$this->begin_transaction();

		$success = $this->repository->update( $id, $validated );
		if ( ! $success ) {
			$this->rollback();
			return new \WP_Error( 'adp_version_update_failed', __( 'Failed to update version.', 'apk-directory-core' ) );
		}

		if ( $set_current ) {
			$this->repository->set_current( $id, (int) $existing['app_id'] );
			$this->sync_app_meta( (int) $existing['app_id'], $id );
		}

		$this->commit();
		return true;
	}

	/**
	 * Duplicate a version.
	 *
	 * @param int $id Version ID to duplicate.
	 * @return int|\WP_Error New version ID.
	 */
	public function duplicate( int $id ): int|\WP_Error {
		$existing = $this->repository->find( $id );
		if ( null === $existing ) {
			return new \WP_Error( 'adp_version_not_found', __( 'Version not found.', 'apk-directory-core' ) );
		}

		unset( $existing['id'], $existing['created_at'], $existing['updated_at'] );
		$existing['version_name'] .= ' (copy)';
		$existing['is_current']    = false;
		$existing['download_count'] = 0;

		return $this->create( $existing );
	}

	/**
	 * Set version as current for its app.
	 *
	 * @param int $id Version ID.
	 * @return true|\WP_Error
	 */
	public function set_current( int $id ): true|\WP_Error {
		$version = $this->repository->find( $id );
		if ( null === $version ) {
			return new \WP_Error( 'adp_version_not_found', __( 'Version not found.', 'apk-directory-core' ) );
		}

		$this->begin_transaction();
		$this->repository->set_current( $id, (int) $version['app_id'] );
		$this->sync_app_meta( (int) $version['app_id'], $id );
		$this->commit();

		return true;
	}

	/**
	 * Delete a version.
	 *
	 * @param int  $id              Version ID.
	 * @param bool $delete_attachment Whether to delete media attachment.
	 * @return true|\WP_Error
	 */
	public function delete( int $id, bool $delete_attachment = false ): true|\WP_Error {
		$version = $this->repository->find( $id );
		if ( null === $version ) {
			return new \WP_Error( 'adp_version_not_found', __( 'Version not found.', 'apk-directory-core' ) );
		}

		$was_current = $version['is_current'];
		$app_id      = (int) $version['app_id'];

		$this->begin_transaction();

		$deleted = $this->repository->delete( $id );
		if ( ! $deleted ) {
			$this->rollback();
			return new \WP_Error( 'adp_version_delete_failed', __( 'Failed to delete version.', 'apk-directory-core' ) );
		}

		if ( $delete_attachment && ! empty( $version['attachment_id'] ) ) {
			wp_delete_attachment( (int) $version['attachment_id'], true );
		}

		if ( $was_current ) {
			$versions = $this->repository->list_by_app( $app_id, 1 );
			if ( ! empty( $versions ) ) {
				$this->repository->set_current( (int) $versions[0]['id'], $app_id );
				$this->sync_app_meta( $app_id, (int) $versions[0]['id'] );
			}
		}

		$this->commit();
		return true;
	}

	/**
	 * Validate version data.
	 *
	 * @param array<string, mixed> $data Version data.
	 * @param int                  $id   Optional version ID for updates.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function validate( array $data, int $id = 0 ): array|\WP_Error {
		unset( $id );

		$app_id = (int) ( $data['app_id'] ?? 0 );
		if ( $app_id <= 0 || AppPostType::POST_TYPE !== get_post_type( $app_id ) ) {
			return new \WP_Error( 'adp_invalid_app', __( 'Invalid app ID.', 'apk-directory-core' ) );
		}

		$file_types = array( 'apk', 'xapk', 'apks', 'zip', 'obb' );
		$download_types = array( 'media', 'external', 'redirect' );
		$scan_statuses  = array( 'unknown', 'pending', 'clean', 'warning', 'failed' );

		$validated = array(
			'app_id'            => $app_id,
			'version_name'      => sanitize_text_field( (string) ( $data['version_name'] ?? '' ) ),
			'version_code'      => isset( $data['version_code'] ) ? (int) $data['version_code'] : null,
			'release_date'      => ! empty( $data['release_date'] ) ? sanitize_text_field( (string) $data['release_date'] ) : null,
			'changelog'         => wp_kses_post( (string) ( $data['changelog'] ?? '' ) ),
			'min_android'       => sanitize_text_field( (string) ( $data['min_android'] ?? '' ) ),
			'file_size_bytes'   => Sanitizer::positive_int( $data['file_size_bytes'] ?? 0 ),
			'file_type'         => Sanitizer::enum( $data['file_type'] ?? 'apk', $file_types, 'apk' ),
			'architectures'     => Sanitizer::string_array( $data['architectures'] ?? array() ),
			'dpi'               => Sanitizer::string_array( $data['dpi'] ?? array() ),
			'download_type'     => Sanitizer::enum( $data['download_type'] ?? 'media', $download_types, 'media' ),
			'attachment_id'     => ! empty( $data['attachment_id'] ) ? (int) $data['attachment_id'] : null,
			'external_url'      => Sanitizer::url( $data['external_url'] ?? '' ),
			'sha256'            => self::sanitize_hash( $data['sha256'] ?? '' ),
			'signature_sha256'  => self::sanitize_hash( $data['signature_sha256'] ?? '' ),
			'virus_scan_status' => Sanitizer::enum( $data['virus_scan_status'] ?? 'unknown', $scan_statuses, 'unknown' ),
			'virus_scan_date'   => ! empty( $data['virus_scan_date'] ) ? sanitize_text_field( (string) $data['virus_scan_date'] ) : null,
			'is_current'        => ! empty( $data['is_current'] ),
		);

		if ( '' === $validated['version_name'] ) {
			return new \WP_Error( 'adp_version_name_required', __( 'Version name is required.', 'apk-directory-core' ) );
		}

		if ( 'media' === $validated['download_type'] && empty( $validated['attachment_id'] ) ) {
			return new \WP_Error( 'adp_attachment_required', __( 'Media attachment is required for local downloads.', 'apk-directory-core' ) );
		}

		if ( in_array( $validated['download_type'], array( 'external', 'redirect' ), true ) && '' === $validated['external_url'] ) {
			return new \WP_Error( 'adp_external_url_required', __( 'External URL is required.', 'apk-directory-core' ) );
		}

		$validated['architectures_json'] = wp_json_encode( $validated['architectures'] );
		$validated['dpi_json']           = wp_json_encode( $validated['dpi'] );

		return $validated;
	}

	/**
	 * Sync current version data to app post meta.
	 *
	 * @param int $app_id     App post ID.
	 * @param int $version_id Version ID.
	 * @return void
	 */
	public function sync_app_meta( int $app_id, int $version_id ): void {
		$version = $this->repository->find( $version_id );
		if ( null === $version ) {
			return;
		}

		Meta::update( $app_id, '_adp_current_version', $version['version_name'] );
		if ( null !== $version['version_code'] ) {
			Meta::update( $app_id, '_adp_version_code', $version['version_code'] );
		}
		Meta::update( $app_id, '_adp_file_size_bytes', $version['file_size_bytes'] );
		if ( ! empty( $version['min_android'] ) ) {
			Meta::update( $app_id, '_adp_android_requirement', $version['min_android'] );
		}
		if ( ! empty( $version['architectures'] ) ) {
			Meta::update( $app_id, '_adp_architectures', $version['architectures'] );
		}
		if ( ! empty( $version['dpi'] ) ) {
			Meta::update( $app_id, '_adp_dpi', $version['dpi'] );
		}
		if ( ! empty( $version['changelog'] ) ) {
			Meta::update( $app_id, '_adp_whats_new', $version['changelog'] );
		}
		if ( ! empty( $version['release_date'] ) ) {
			$date = substr( (string) $version['release_date'], 0, 10 );
			Meta::update( $app_id, '_adp_updated_date', $date );
		}
	}

	/**
	 * Sanitize SHA-256 hash.
	 *
	 * @param mixed $value Raw hash.
	 * @return string|null
	 */
	private static function sanitize_hash( mixed $value ): ?string {
		$value = strtolower( sanitize_text_field( (string) $value ) );
		if ( '' === $value ) {
			return null;
		}
		if ( preg_match( '/^[a-f0-9]{64}$/', $value ) ) {
			return $value;
		}
		return null;
	}

	/**
	 * Begin database transaction if supported.
	 *
	 * @return void
	 */
	private function begin_transaction(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * Commit transaction.
	 *
	 * @return void
	 */
	private function commit(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'COMMIT' );
	}

	/**
	 * Rollback transaction.
	 *
	 * @return void
	 */
	private function rollback(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( 'ROLLBACK' );
	}
}
