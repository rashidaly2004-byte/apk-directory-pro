<?php
/**
 * Neutral demo content importer.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Versions\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Imports idempotent neutral demo apps.
 */
class DemoImporter {

	/**
	 * Import demo content if not already imported.
	 *
	 * @return array<string, mixed> Import results.
	 */
	public static function import(): array {
		if ( get_option( 'adp_core_demo_imported' ) ) {
			return array(
				'skipped' => true,
				'message' => __( 'Demo content already imported.', 'apk-directory-core' ),
			);
		}

		$results = array( 'apps' => array(), 'terms' => array() );

		$terms = array(
			'adp_app_category' => array(
				array( 'name' => __( 'Tools', 'apk-directory-core' ), 'slug' => 'tools' ),
				array( 'name' => __( 'Games', 'apk-directory-core' ), 'slug' => 'games' ),
				array( 'name' => __( 'Productivity', 'apk-directory-core' ), 'slug' => 'productivity' ),
			),
			'adp_developer' => array(
				array( 'name' => __( 'Demo Studio', 'apk-directory-core' ), 'slug' => 'demo-studio' ),
				array( 'name' => __( 'Sample Labs', 'apk-directory-core' ), 'slug' => 'sample-labs' ),
			),
			'adp_platform' => array(
				array( 'name' => __( 'Android', 'apk-directory-core' ), 'slug' => 'android' ),
			),
		);

		$term_ids = array();
		foreach ( $terms as $taxonomy => $items ) {
			foreach ( $items as $item ) {
				$term_ids[ $taxonomy ][ $item['slug'] ] = self::ensure_term( $taxonomy, $item['name'], $item['slug'] );
				$results['terms'][]                     = $term_ids[ $taxonomy ][ $item['slug'] ];
			}
		}

		$demo_apps = array(
			array(
				'title'       => __( 'Sample Task Manager', 'apk-directory-core' ),
				'slug'        => 'sample-task-manager',
				'description' => __( 'A neutral demo productivity app for testing the APK Directory theme and plugin. Includes placeholder listing content only.', 'apk-directory-core' ),
				'package'     => 'com.demo.taskmanager',
				'version'     => '1.0.0',
				'category'    => 'productivity',
				'developer'   => 'demo-studio',
				'badge'       => 'new',
				'whats_new'   => __( '<ul><li>Initial demo release for directory testing.</li><li>Placeholder changelog content.</li></ul>', 'apk-directory-core' ),
			),
			array(
				'title'       => __( 'Sample Puzzle Game', 'apk-directory-core' ),
				'slug'        => 'sample-puzzle-game',
				'description' => __( 'A neutral demo game app for testing listings, archives, and version history pages.', 'apk-directory-core' ),
				'package'     => 'com.demo.puzzlegame',
				'version'     => '2.1.0',
				'category'    => 'games',
				'developer'   => 'sample-labs',
				'badge'       => 'updated',
				'whats_new'   => __( '<ul><li>Demo level pack added.</li><li>Performance improvements for test environments.</li></ul>', 'apk-directory-core' ),
			),
			array(
				'title'       => __( 'Sample Notes Utility', 'apk-directory-core' ),
				'slug'        => 'sample-notes-utility',
				'description' => __( 'A lightweight neutral notes app used to populate category and search demos.', 'apk-directory-core' ),
				'package'     => 'com.demo.notesutility',
				'version'     => '3.4.2',
				'category'    => 'tools',
				'developer'   => 'demo-studio',
				'badge'       => 'none',
				'whats_new'   => __( '<ul><li>Editorial demo content refresh.</li></ul>', 'apk-directory-core' ),
			),
			array(
				'title'       => __( 'Sample Arcade Runner', 'apk-directory-core' ),
				'slug'        => 'sample-arcade-runner',
				'description' => __( 'Arcade-style demo game with generated placeholder screenshots and neutral branding.', 'apk-directory-core' ),
				'package'     => 'com.demo.arcaderunner',
				'version'     => '1.5.0',
				'category'    => 'games',
				'developer'   => 'sample-labs',
				'badge'       => 'new',
				'whats_new'   => __( '<ul><li>Demo obstacle course mode.</li><li>Neutral placeholder assets.</li></ul>', 'apk-directory-core' ),
			),
		);

		$service = new Service();

		foreach ( $demo_apps as $app_data ) {
			$existing = get_page_by_path( $app_data['slug'], OBJECT, AppPostType::POST_TYPE );
			if ( $existing ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => $app_data['title'],
					'post_name'    => $app_data['slug'],
					'post_content' => self::build_content( $app_data ),
					'post_excerpt' => $app_data['description'],
					'post_status'  => 'publish',
					'post_type'    => AppPostType::POST_TYPE,
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			Meta::update( $post_id, '_adp_short_description', $app_data['description'] );
			Meta::update( $post_id, '_adp_package_name', $app_data['package'] );
			Meta::update( $post_id, '_adp_current_version', $app_data['version'] );
			Meta::update( $post_id, '_adp_file_size_bytes', 12582912 );
			Meta::update( $post_id, '_adp_android_requirement', 'Android 8.0+' );
			Meta::update( $post_id, '_adp_price_type', 'free' );
			Meta::update( $post_id, '_adp_status_badge', $app_data['badge'] );
			Meta::update( $post_id, '_adp_whats_new', $app_data['whats_new'] );
			Meta::update( $post_id, '_adp_editor_rating', 4.2 );
			Meta::update( $post_id, '_adp_download_count', wp_rand( 500, 5000 ) );
			Meta::update( $post_id, '_adp_screenshot_ids', self::create_placeholder_screenshots( $app_data['slug'] ) );

			if ( ! empty( $term_ids['adp_app_category'][ $app_data['category'] ] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids['adp_app_category'][ $app_data['category'] ] ), 'adp_app_category' );
			}
			if ( ! empty( $term_ids['adp_developer'][ $app_data['developer'] ] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids['adp_developer'][ $app_data['developer'] ] ), 'adp_developer' );
			}
			if ( ! empty( $term_ids['adp_platform']['android'] ) ) {
				wp_set_object_terms( $post_id, array( $term_ids['adp_platform']['android'] ), 'adp_platform' );
			}

			$service->create(
				array(
					'app_id'          => $post_id,
					'version_name'    => $app_data['version'],
					'version_code'    => 100,
					'file_size_bytes' => 12582912,
					'file_type'       => 'apk',
					'download_type'   => 'external',
					'external_url'    => 'https://example.com/demo/' . $app_data['slug'] . '.apk',
					'is_current'      => true,
					'architectures'   => array( 'arm64-v8a', 'armeabi-v7a' ),
					'changelog'       => wp_strip_all_tags( $app_data['whats_new'] ),
				)
			);

			$results['apps'][] = $post_id;
		}

		update_option( 'adp_core_demo_imported', true, false );

		return $results;
	}

	/**
	 * Build long-form demo content.
	 *
	 * @param array<string, string> $app_data App data.
	 * @return string
	 */
	private static function build_content( array $app_data ): string {
		return sprintf(
			'<h2>%1$s</h2><p>%2$s</p><h3>%3$s</h3><p>%4$s</p>',
			esc_html__( 'About this demo app', 'apk-directory-core' ),
			esc_html( $app_data['description'] ),
			esc_html__( 'Features', 'apk-directory-core' ),
			esc_html__( 'This listing uses neutral placeholder copy for theme and plugin QA. No real third-party brands are referenced.', 'apk-directory-core' )
		);
	}

	/**
	 * Create placeholder screenshot attachments.
	 *
	 * @param string $slug App slug.
	 * @return array<int, int>
	 */
	private static function create_placeholder_screenshots( string $slug ): array {
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$ids = array();
		for ( $i = 1; $i <= 3; $i++ ) {
			$upload = wp_upload_bits(
				$slug . '-screenshot-' . $i . '.png',
				null,
				self::placeholder_png( $slug, $i )
			);
			if ( ! empty( $upload['error'] ) ) {
				continue;
			}

			$attachment_id = wp_insert_attachment(
				array(
					'post_title'     => sprintf(
						/* translators: 1: app slug, 2: screenshot number */
						__( '%1$s screenshot %2$d', 'apk-directory-core' ),
						$slug,
						$i
					),
					'post_mime_type' => 'image/png',
					'post_status'    => 'inherit',
					'guid'           => $upload['url'],
				),
				$upload['file']
			);

			if ( is_wp_error( $attachment_id ) ) {
				continue;
			}

			$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $upload['file'] );
			wp_update_attachment_metadata( (int) $attachment_id, $metadata );
			$ids[] = (int) $attachment_id;
		}

		return $ids;
	}

	/**
	 * Generate a tiny neutral PNG placeholder.
	 *
	 * @param string $slug App slug.
	 * @param int    $num  Screenshot number.
	 * @return string Binary PNG data.
	 */
	private static function placeholder_png( string $slug, int $num ): string {
		unset( $slug );
		if ( function_exists( 'imagecreatetruecolor' ) ) {
			$image = imagecreatetruecolor( 360, 640 );
			if ( false !== $image ) {
				$colors = array(
					array( 79, 124, 255 ),
					array( 56, 178, 172 ),
					array( 237, 137, 54 ),
				);
				$color = $colors[ ( $num - 1 ) % count( $colors ) ];
				$bg    = imagecolorallocate( $image, $color[0], $color[1], $color[2] );
				imagefilledrectangle( $image, 0, 0, 360, 640, $bg );
				ob_start();
				imagepng( $image );
				$data = (string) ob_get_clean();
				imagedestroy( $image );
				if ( '' !== $data ) {
					return $data;
				}
			}
		}

		return base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO5VlJsAAAAASUVORK5CYII=', true ) ?: '';
	}

	/**
	 * Ensure a taxonomy term exists.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param string $name     Term name.
	 * @param string $slug     Term slug.
	 * @return int Term ID.
	 */
	private static function ensure_term( string $taxonomy, string $name, string $slug ): int {
		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing ) {
			return (int) $existing->term_id;
		}
		$result = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
		if ( is_wp_error( $result ) ) {
			return 0;
		}
		return (int) $result['term_id'];
	}
}
