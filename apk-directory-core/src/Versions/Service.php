<?php

namespace APD\Core\Versions;

use APD\Core\Content\Meta;

final class Service {

	private Repository $repository;

	public function __construct( ?Repository $repository = null ) {
		$this->repository = $repository ?? new Repository();
	}

	public function create_version( int $app_id, array $data ): int {
		$allowed_types = [ 'apk', 'xapk', 'apks', 'zip', 'obb' ];
		$file_type     = in_array( $data['file_type'] ?? 'apk', $allowed_types, true ) ? $data['file_type'] : 'apk';

		$download_types = [ 'media', 'external', 'redirect' ];
		$download_type  = in_array( $data['download_type'] ?? 'media', $download_types, true ) ? $data['download_type'] : 'media';

		$row = [
			'app_id'              => $app_id,
			'version_name'        => sanitize_text_field( $data['version_name'] ?? '' ),
			'version_code'        => isset( $data['version_code'] ) ? absint( $data['version_code'] ) : null,
			'release_date'        => $data['release_date'] ?? null,
			'changelog'           => wp_kses_post( $data['changelog'] ?? '' ),
			'min_android'         => sanitize_text_field( $data['min_android'] ?? '' ),
			'file_size_bytes'     => absint( $data['file_size_bytes'] ?? 0 ),
			'file_type'           => $file_type,
			'architectures_json'  => wp_json_encode( $data['architectures'] ?? [] ),
			'dpi_json'            => wp_json_encode( $data['dpi'] ?? [] ),
			'download_type'       => $download_type,
			'attachment_id'       => isset( $data['attachment_id'] ) ? absint( $data['attachment_id'] ) : null,
			'external_url'        => esc_url_raw( $data['external_url'] ?? '' ),
			'sha256'              => $this->sanitize_hash( $data['sha256'] ?? '' ),
			'signature_sha256'    => $this->sanitize_hash( $data['signature_sha256'] ?? '' ),
			'virus_scan_status'   => $this->sanitize_scan_status( $data['virus_scan_status'] ?? 'unknown' ),
			'is_current'          => (int) ( $data['is_current'] ?? 0 ),
		];

		$id = $this->repository->insert( $row );

		if ( $row['is_current'] ) {
			$this->repository->set_current( $app_id, $id );
			$this->sync_app_meta( $app_id, $row );
		}

		return $id;
	}

	public function set_current( int $app_id, int $version_id ): void {
		$version = $this->repository->find( $version_id );
		if ( ! $version || (int) $version['app_id'] !== $app_id ) {
			return;
		}
		$this->repository->set_current( $app_id, $version_id );
		$this->sync_app_meta( $app_id, $version );
	}

	private function sync_app_meta( int $app_id, array $version ): void {
		update_post_meta( $app_id, '_adp_current_version', $version['version_name'] );
		update_post_meta( $app_id, '_adp_version_code', $version['version_code'] );
		update_post_meta( $app_id, '_adp_file_size_bytes', $version['file_size_bytes'] );
		if ( $version['min_android'] ) {
			update_post_meta( $app_id, '_adp_android_requirement', $version['min_android'] );
		}
		if ( $version['release_date'] ) {
			update_post_meta( $app_id, '_adp_updated_date', gmdate( 'Y-m-d', strtotime( $version['release_date'] ) ) );
		}
	}

	private function sanitize_hash( string $hash ): string {
		$hash = strtolower( sanitize_text_field( $hash ) );
		return preg_match( '/^[a-f0-9]{64}$/', $hash ) ? $hash : '';
	}

	private function sanitize_scan_status( string $status ): string {
		$allowed = [ 'unknown', 'pending', 'clean', 'warning', 'failed' ];
		return in_array( $status, $allowed, true ) ? $status : 'unknown';
	}
}
