<?php
/**
 * Version manager admin UI.
 *
 * @package Adp\Core\Versions
 */

namespace Adp\Core\Versions;

use Adp\Core\Content\AppPostType;
use Adp\Core\Downloads\HashService;

defined( 'ABSPATH' ) || exit;

/**
 * Admin version manager metabox and assets.
 */
class Admin {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'add_meta_boxes', array( self::class, 'register_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Register version manager metabox.
	 *
	 * @return void
	 */
	public static function register_metabox(): void {
		add_meta_box(
			'adp-version-manager',
			__( 'Version Manager', 'apk-directory-core' ),
			array( self::class, 'render_metabox' ),
			AppPostType::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue version manager assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || AppPostType::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'adp-version-manager',
			ADP_CORE_URL . 'assets/admin/js/version-manager.js',
			array( 'wp-api-fetch', 'wp-i18n', 'wp-element', 'media-editor' ),
			ADP_CORE_VERSION,
			true
		);

		wp_enqueue_media();

		wp_enqueue_style(
			'adp-version-manager',
			ADP_CORE_URL . 'assets/admin/css/version-manager.css',
			array(),
			ADP_CORE_VERSION
		);

		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$settings = get_option( 'adp_core_settings', array() );
		$default_download = is_array( $settings ) && isset( $settings['default_download_type'] )
			? (string) $settings['default_download_type']
			: 'media';

		wp_localize_script(
			'adp-version-manager',
			'adpVersionManager',
			array(
				'appId'       => $post_id,
				'restBase'    => rest_url( 'adp/v1/apps/' . $post_id . '/versions' ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'canUpload'   => \Adp\Core\Content\Capabilities::can_upload_apk(),
				'defaultDownloadType' => $default_download,
				'fileTypes'   => array( 'apk', 'xapk', 'apks', 'zip', 'obb' ),
				'allowedMimeTypes' => array(
					'application/vnd.android.package-archive',
					'application/zip',
					'application/octet-stream',
				),
				'downloadTypes' => array(
					array( 'value' => 'media', 'label' => __( 'Local Upload', 'apk-directory-core' ) ),
					array( 'value' => 'external', 'label' => __( 'External URL', 'apk-directory-core' ) ),
					array( 'value' => 'redirect', 'label' => __( 'Redirect', 'apk-directory-core' ) ),
				),
				'i18n'        => array(
					'add'           => __( 'Add Version', 'apk-directory-core' ),
					'edit'          => __( 'Edit', 'apk-directory-core' ),
					'duplicate'     => __( 'Duplicate', 'apk-directory-core' ),
					'setCurrent'    => __( 'Set Current', 'apk-directory-core' ),
					'delete'        => __( 'Delete', 'apk-directory-core' ),
					'confirmDelete' => __( 'Are you sure you want to delete this version?', 'apk-directory-core' ),
					'current'       => __( 'Current', 'apk-directory-core' ),
					'save'          => __( 'Save', 'apk-directory-core' ),
					'cancel'        => __( 'Cancel', 'apk-directory-core' ),
					'deleteFile'    => __( 'Also delete media file', 'apk-directory-core' ),
					'selectFile'    => __( 'Select APK File', 'apk-directory-core' ),
					'useFile'       => __( 'Use this file', 'apk-directory-core' ),
					'changeFile'    => __( 'Change file', 'apk-directory-core' ),
					'removeFile'    => __( 'Remove file', 'apk-directory-core' ),
					'noFileSelected'=> __( 'No APK file selected.', 'apk-directory-core' ),
					'apkFile'       => __( 'APK / Package File', 'apk-directory-core' ),
					'fileSize'      => __( 'File size', 'apk-directory-core' ),
					'computingHash' => __( 'Computing SHA-256…', 'apk-directory-core' ),
					'hashPending'   => __( 'Pending', 'apk-directory-core' ),
					'attachmentRequired' => __( 'Please select a media file for local download.', 'apk-directory-core' ),
					'mediaUnavailable' => __( 'Media library is unavailable.', 'apk-directory-core' ),
				),
			)
		);
	}

	/**
	 * Render version manager metabox container.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public static function render_metabox( \WP_Post $post ): void {
		$service  = new Service();
		$versions = $service->get_repository()->list_by_app( $post->ID );
		$hash_svc = new HashService();

		echo '<div id="adp-version-manager-root" data-app-id="' . esc_attr( (string) $post->ID ) . '">';
		echo '<noscript>';
		self::render_fallback_table( $versions, $hash_svc );
		echo '</noscript>';
		echo '</div>';
	}

	/**
	 * Render noscript fallback table.
	 *
	 * @param array<int, array<string, mixed>> $versions Versions list.
	 * @param HashService                    $hash_svc Hash service.
	 * @return void
	 */
	private static function render_fallback_table( array $versions, HashService $hash_svc ): void {
		if ( empty( $versions ) ) {
			echo '<p>' . esc_html__( 'No versions yet.', 'apk-directory-core' ) . '</p>';
			return;
		}
		echo '<table class="widefat"><thead><tr>';
		echo '<th>' . esc_html__( 'Version', 'apk-directory-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Type', 'apk-directory-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Size', 'apk-directory-core' ) . '</th>';
		echo '<th>' . esc_html__( 'Current', 'apk-directory-core' ) . '</th>';
		echo '</tr></thead><tbody>';
		foreach ( $versions as $version ) {
			$hash_status = $hash_svc->get_status( (int) $version['id'], $version['sha256'] ?? null, $version['attachment_id'] ?? null );
			echo '<tr>';
			echo '<td>' . esc_html( $version['version_name'] ) . '</td>';
			echo '<td>' . esc_html( $version['download_type'] ) . '</td>';
			echo '<td>' . esc_html( size_format( (int) $version['file_size_bytes'] ) ) . '</td>';
			echo '<td>' . ( $version['is_current'] ? esc_html__( 'Yes', 'apk-directory-core' ) : '—' ) . ' (' . esc_html( $hash_status['status'] ) . ')</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
