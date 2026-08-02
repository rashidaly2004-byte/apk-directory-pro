<?php
/**
 * Download controller — rewrite, interstitial, and file delivery.
 *
 * @package Adp\Core\Downloads
 */

namespace Adp\Core\Downloads;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Support\Formatter;
use Adp\Core\Versions\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Handles download routes and file streaming.
 */
class Controller {

	private Signer $signer;
	private Counter $counter;
	private Repository $repository;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->signer     = new Signer();
		$this->counter    = new Counter();
		$this->repository = new Repository();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		$instance = new self();
		add_action( 'init', array( $instance, 'register_rewrite' ) );
		add_filter( 'query_vars', array( $instance, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $instance, 'handle_request' ) );
		add_action( 'wp_head', array( $instance, 'add_noindex' ) );
	}

	/**
	 * Register download rewrite rule.
	 *
	 * @return void
	 */
	public function register_rewrite(): void {
		add_rewrite_rule(
			'^download/([^/]+)/?$',
			'index.php?adp_download_token=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array<int, string> $vars Query vars.
	 * @return array<int, string>
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'adp_download_token';
		$vars[] = 'adp_download_action';
		return $vars;
	}

	/**
	 * Handle download requests.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$token = get_query_var( 'adp_download_token' );
		if ( empty( $token ) ) {
			return;
		}

		$token      = sanitize_text_field( rawurldecode( (string) $token ) );
		$version_id = $this->signer->validate( $token );
		if ( is_wp_error( $version_id ) ) {
			wp_die( esc_html( $version_id->get_error_message() ), esc_html__( 'Download Error', 'apk-directory-core' ), array( 'response' => 403 ) );
		}

		$version = $this->repository->find( $version_id );
		if ( null === $version ) {
			wp_die( esc_html__( 'Version not found.', 'apk-directory-core' ), esc_html__( 'Download Error', 'apk-directory-core' ), array( 'response' => 404 ) );
		}

		$action = get_query_var( 'adp_download_action' );
		if ( 'file' === $action || isset( $_GET['adp_download_action'] ) && 'file' === sanitize_key( wp_unslash( (string) $_GET['adp_download_action'] ) ) ) {
			$this->deliver_file( $version );
			return;
		}

		$this->render_interstitial( $version, $token );
	}

	/**
	 * Render download interstitial page.
	 *
	 * @param array<string, mixed> $version Version data.
	 * @param string               $token   Download token.
	 * @return void
	 */
	private function render_interstitial( array $version, string $token ): void {
		$app_id = (int) $version['app_id'];
		$app    = get_post( $app_id );
		if ( ! $app || AppPostType::POST_TYPE !== $app->post_type ) {
			wp_die( esc_html__( 'App not found.', 'apk-directory-core' ), '', array( 'response' => 404 ) );
		}

		$context = array(
			'app'         => $app,
			'version'     => $version,
			'token'       => $token,
			'file_url'    => add_query_arg( 'adp_download_action', 'file', home_url( '/download/' . rawurlencode( $token ) . '/' ) ),
			'icon_url'    => get_the_post_thumbnail_url( $app_id, 'thumbnail' ),
			'file_size'   => Formatter::bytes( (int) $version['file_size_bytes'] ),
			'sha256'      => $version['sha256'] ?? '',
			'scan_status' => $version['virus_scan_status'] ?? 'unknown',
			'disclaimer'  => Meta::get( $app_id, '_adp_disclaimer_note' ),
			'is_external' => in_array( $version['download_type'], array( 'external', 'redirect' ), true ),
		);

		status_header( 200 );
		nocache_headers();

		$template = ADP_CORE_PATH . 'templates/download.php';
		if ( file_exists( $template ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			extract( $context, EXTR_SKIP ); // nosemgrep: php.lang.security.extract.extract
			include $template;
			exit;
		}

		wp_die( esc_html__( 'Download template not found.', 'apk-directory-core' ) );
	}

	/**
	 * Deliver the actual file.
	 *
	 * @param array<string, mixed> $version Version data.
	 * @return void
	 */
	private function deliver_file( array $version ): void {
		$visitor_token = $this->counter->get_visitor_token();
		$this->counter->record( (int) $version['id'], $visitor_token );

		$download_type = $version['download_type'] ?? 'media';

		if ( 'redirect' === $download_type ) {
			$url = $version['external_url'] ?? '';
			if ( $this->is_url_allowed( $url ) ) {
				wp_safe_redirect( $url, 302 );
				exit;
			}
			wp_die( esc_html__( 'External download URL is not allowed.', 'apk-directory-core' ), '', array( 'response' => 403 ) );
		}

		if ( 'external' === $download_type ) {
			$url = $version['external_url'] ?? '';
			if ( ! $this->is_url_allowed( $url ) ) {
				wp_die( esc_html__( 'External download URL is not allowed.', 'apk-directory-core' ), '', array( 'response' => 403 ) );
			}
			// External URLs show warning on interstitial; direct file action redirects.
			wp_safe_redirect( $url, 302 );
			exit;
		}

		$this->stream_local_file( $version );
	}

	/**
	 * Stream local attachment safely.
	 *
	 * @param array<string, mixed> $version Version data.
	 * @return void
	 */
	private function stream_local_file( array $version ): void {
		$attachment_id = (int) ( $version['attachment_id'] ?? 0 );
		if ( $attachment_id <= 0 ) {
			wp_die( esc_html__( 'No file attached.', 'apk-directory-core' ), '', array( 'response' => 404 ) );
		}

		$path = get_attached_file( $attachment_id );
		if ( ! $path || ! file_exists( $path ) || ! is_readable( $path ) ) {
			wp_die( esc_html__( 'File not found.', 'apk-directory-core' ), '', array( 'response' => 404 ) );
		}

		$real_path = realpath( $path );
		$upload_dir = wp_upload_dir();
		$base_dir   = realpath( $upload_dir['basedir'] );

		if ( false === $real_path || false === $base_dir || ! str_starts_with( $real_path, $base_dir ) ) {
			wp_die( esc_html__( 'Invalid file path.', 'apk-directory-core' ), '', array( 'response' => 403 ) );
		}

		$filename = basename( $real_path );
		$size     = filesize( $real_path );
		$mime     = 'application/vnd.android.package-archive';

		nocache_headers();
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Content-Length: ' . (string) $size );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: private, no-cache, no-store, must-revalidate' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $real_path );
		exit;
	}

	/**
	 * Check if external URL is allowed.
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	public function is_url_allowed( string $url ): bool {
		$url = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( '' === $url ) {
			return false;
		}

		$settings  = get_option( 'adp_core_settings', array() );
		$allowlist = $settings['external_url_allowlist'] ?? array();

		if ( empty( $allowlist ) ) {
			return true;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! is_string( $host ) ) {
			return false;
		}

		foreach ( (array) $allowlist as $allowed ) {
			$allowed = strtolower( sanitize_text_field( (string) $allowed ) );
			if ( $host === $allowed || str_ends_with( $host, '.' . $allowed ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add noindex for download pages.
	 *
	 * @return void
	 */
	public function add_noindex(): void {
		if ( get_query_var( 'adp_download_token' ) ) {
			echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
		}
	}
}
