<?php

namespace APD\Core\Downloads;

use APD\Core\Content\AppPostType;
use APD\Core\Versions\Repository;

final class Controller {

	private Signer $signer;
	private Counter $counter;
	private Repository $repository;

	public function __construct() {
		$this->signer     = new Signer();
		$this->counter    = new Counter();
		$this->repository = new Repository();
	}

	public function register(): void {
		add_action( 'init', [ $this, 'register_rewrites' ] );
		add_action( 'template_redirect', [ $this, 'handle_download_page' ] );
		add_action( 'template_redirect', [ $this, 'handle_versions_page' ] );
		add_filter( 'wp_robots', [ $this, 'noindex_download_pages' ] );
	}

	public function register_rewrites(): void {
		add_rewrite_rule(
			'^download/([^/]+)/?$',
			'index.php?adp_download_token=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^app/([^/]+)/versions/?$',
			'index.php?adp_app=$matches[1]&adp_versions=1',
			'top'
		);
		add_rewrite_tag( '%adp_download_token%', '([^&]+)' );
		add_rewrite_tag( '%adp_versions%', '([0-1])' );
		add_rewrite_tag( '%adp_app%', '([^&]+)' );
	}

	public function noindex_download_pages( array $robots ): array {
		if ( get_query_var( 'adp_download_token' ) ) {
			$robots['noindex'] = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	public function handle_download_page(): void {
		$token = get_query_var( 'adp_download_token' );
		if ( ! $token ) {
			return;
		}

		$data = $this->signer->validate_token( $token );
		if ( ! $data ) {
			wp_die( esc_html__( 'This download link has expired or is invalid.', 'apk-directory-pro' ), 403 );
		}

		$version = $this->repository->find( $data['version_id'] );
		if ( ! $version || (int) $version['app_id'] !== $data['app_id'] ) {
			wp_die( esc_html__( 'Version not found.', 'apk-directory-pro' ), 404 );
		}

		$app = get_post( $data['app_id'] );
		if ( ! $app || $app->post_type !== AppPostType::POST_TYPE ) {
			wp_die( esc_html__( 'App not found.', 'apk-directory-pro' ), 404 );
		}

		if ( isset( $_GET['adp_file'] ) && $_GET['adp_file'] === '1' ) {
			$this->deliver_file( $version );
			return;
		}

		$this->render_interstitial( $app, $version, $token );
		exit;
	}

	public function handle_file_delivery(): void {
		// Handled via adp_file query param on download page.
	}

	public function handle_versions_page(): void {
		if ( ! get_query_var( 'adp_versions' ) ) {
			return;
		}

		$slug = get_query_var( 'adp_app' );
		if ( ! $slug ) {
			return;
		}

		$app = get_page_by_path( $slug, OBJECT, AppPostType::POST_TYPE );
		if ( ! $app ) {
			wp_die( esc_html__( 'App not found.', 'apk-directory-pro' ), 404 );
		}

		global $post;
		$post = $app;
		setup_postdata( $post );

		$template = ADP_CORE_PATH . 'templates/versions.php';
		if ( file_exists( $template ) ) {
			include $template;
			exit;
		}
	}

	private function render_interstitial( \WP_Post $app, array $version, string $token ): void {
		$download_url = add_query_arg( 'adp_file', '1', home_url( '/download/' . rawurlencode( $token ) . '/' ) );

		status_header( 200 );
		nocache_headers();

		$template = ADP_CORE_PATH . 'templates/download-interstitial.php';
		if ( file_exists( $template ) ) {
			include $template;
			return;
		}

		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . esc_html( $app->post_title ) . '</title></head><body>';
		echo '<h1>' . esc_html( $app->post_title ) . ' — ' . esc_html( $version['version_name'] ) . '</h1>';
		if ( $version['sha256'] ) {
			echo '<p>SHA-256: <code>' . esc_html( $version['sha256'] ) . '</code></p>';
		}
		echo '<p><a href="' . esc_url( $download_url ) . '">' . esc_html__( 'Download file', 'apk-directory-pro' ) . '</a></p>';
		echo '</body></html>';
	}

	private function deliver_file( array $version ): void {
		$this->counter->increment( (int) $version['id'] );

		if ( $version['download_type'] === 'external' || $version['download_type'] === 'redirect' ) {
			$url = esc_url_raw( $version['external_url'] ?? '' );
			if ( $url ) {
				wp_safe_redirect( $url, 302 );
				exit;
			}
			wp_die( esc_html__( 'External download URL not available.', 'apk-directory-pro' ), 404 );
		}

		$attachment_id = (int) ( $version['attachment_id'] ?? 0 );
		if ( ! $attachment_id ) {
			wp_die( esc_html__( 'File not available.', 'apk-directory-pro' ), 404 );
		}

		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			wp_die( esc_html__( 'File not found.', 'apk-directory-pro' ), 404 );
		}

		$real_path = realpath( $file_path );
		$upload_dir = wp_upload_dir();
		$base_path  = realpath( $upload_dir['basedir'] );

		if ( ! $real_path || ! $base_path || ! str_starts_with( $real_path, $base_path ) ) {
			wp_die( esc_html__( 'Invalid file path.', 'apk-directory-pro' ), 403 );
		}

		$filename = basename( $real_path );
		$size     = filesize( $real_path );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Content-Length: ' . $size );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $real_path );
		exit;
	}

	public function get_download_url( int $version_id, int $app_id ): string {
		$token = $this->signer->create_token( $version_id, $app_id );
		return home_url( '/download/' . rawurlencode( $token ) . '/' );
	}
}
