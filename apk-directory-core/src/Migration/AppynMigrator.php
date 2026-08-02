<?php
/**
 * Appyn theme migration tool.
 *
 * @package Adp\Core\Migration
 */

namespace Adp\Core\Migration;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Versions\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Migrates Appyn-shaped data to adp_app posts.
 */
class AppynMigrator {

	public const STATE_OPTION = 'adp_core_migration_state';
	public const BATCH_SIZE   = 10;

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_post_adp_migration_run', array( self::class, 'handle_run' ) );
		add_action( 'adp_core_migration_batch', array( self::class, 'process_batch' ) );
	}

	/**
	 * Add migration admin page.
	 *
	 * @return void
	 */
	public static function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'Appyn Migration', 'apk-directory-core' ),
			__( 'Migration', 'apk-directory-core' ),
			'manage_options',
			'adp-migration',
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Render migration page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$state = self::get_state();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Appyn Migration', 'apk-directory-core' ); ?></h1>
			<p><?php esc_html_e( 'Migrate Appyn theme data to APK Directory Core. Original data is never deleted.', 'apk-directory-core' ); ?></p>
			<?php if ( ! empty( $state['log'] ) ) : ?>
				<h2><?php esc_html_e( 'Migration Log', 'apk-directory-core' ); ?></h2>
				<pre style="background:#f5f5f5;padding:1em;max-height:300px;overflow:auto;"><?php echo esc_html( implode( "\n", $state['log'] ) ); ?></pre>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'adp_migration', 'adp_migration_nonce' ); ?>
				<input type="hidden" name="action" value="adp_migration_run" />
				<p>
					<label>
						<input type="checkbox" name="backup_confirmed" value="1" required />
						<?php esc_html_e( 'I have backed up my database before proceeding.', 'apk-directory-core' ); ?>
					</label>
				</p>
				<p>
					<button type="submit" name="mode" value="dry-run" class="button"><?php esc_html_e( 'Dry Run', 'apk-directory-core' ); ?></button>
					<button type="submit" name="mode" value="import" class="button button-primary"><?php esc_html_e( 'Run Import', 'apk-directory-core' ); ?></button>
					<button type="submit" name="mode" value="resume" class="button"><?php esc_html_e( 'Resume', 'apk-directory-core' ); ?></button>
				</p>
			</form>
			<?php if ( ! empty( $state['stats'] ) ) : ?>
				<h2><?php esc_html_e( 'Statistics', 'apk-directory-core' ); ?></h2>
				<ul>
					<?php foreach ( $state['stats'] as $key => $value ) : ?>
						<li><strong><?php echo esc_html( $key ); ?>:</strong> <?php echo esc_html( (string) $value ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle migration form submission.
	 *
	 * @return void
	 */
	public static function handle_run(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'apk-directory-core' ) );
		}
		check_admin_referer( 'adp_migration', 'adp_migration_nonce' );

		$mode = sanitize_key( (string) ( $_POST['mode'] ?? 'dry-run' ) );
		if ( 'dry-run' === $mode ) {
			$result = self::dry_run();
		} elseif ( 'resume' === $mode ) {
			$result = self::resume();
		} else {
			$result = self::start_import();
		}

		self::update_state( $result );
		wp_safe_redirect( admin_url( 'edit.php?post_type=adp_app&page=adp-migration&done=1' ) );
		exit;
	}

	/**
	 * Run dry-run preview.
	 *
	 * @return array<string, mixed>
	 */
	public static function dry_run(): array {
		$posts = self::find_appyn_posts();
		$log   = array();
		$stats = array(
			'found'    => count( $posts ),
			'would_create' => 0,
			'would_skip'   => 0,
		);

		foreach ( $posts as $post ) {
			$mapping = self::map_post( $post, true );
			if ( $mapping['skip'] ) {
				++$stats['would_skip'];
				$log[] = sprintf( '[SKIP] Post %d: %s — %s', $post->ID, $post->post_title, $mapping['reason'] );
			} else {
				++$stats['would_create'];
				$log[] = sprintf( '[CREATE] Post %d: %s → adp_app (package: %s)', $post->ID, $post->post_title, $mapping['package'] ?? 'n/a' );
			}
		}

		return array(
			'mode'  => 'dry-run',
			'log'   => $log,
			'stats' => $stats,
		);
	}

	/**
	 * Start import process.
	 *
	 * @return array<string, mixed>
	 */
	public static function start_import(): array {
		$posts = self::find_appyn_posts();
		$state = array(
			'mode'       => 'import',
			'post_ids'   => wp_list_pluck( $posts, 'ID' ),
			'offset'     => 0,
			'imported'   => 0,
			'skipped'    => 0,
			'map'        => array(),
			'log'        => array( '[START] Migration import started.' ),
			'stats'      => array(),
		);
		update_option( self::STATE_OPTION, $state, false );

		if ( ! wp_next_scheduled( 'adp_core_migration_batch' ) ) {
			wp_schedule_single_event( time(), 'adp_core_migration_batch' );
		}

		return $state;
	}

	/**
	 * Resume interrupted import.
	 *
	 * @return array<string, mixed>
	 */
	public static function resume(): array {
		$state = self::get_state();
		if ( empty( $state['post_ids'] ) ) {
			$state['log'][] = '[RESUME] No pending posts found.';
			return $state;
		}
		$state['log'][] = '[RESUME] Resuming from offset ' . (int) ( $state['offset'] ?? 0 );
		if ( ! wp_next_scheduled( 'adp_core_migration_batch' ) ) {
			wp_schedule_single_event( time(), 'adp_core_migration_batch' );
		}
		return $state;
	}

	/**
	 * Process a batch of migrations.
	 *
	 * @return void
	 */
	public static function process_batch(): void {
		$state = self::get_state();
		if ( empty( $state['post_ids'] ) || 'import' !== ( $state['mode'] ?? '' ) ) {
			return;
		}

		$offset   = (int) ( $state['offset'] ?? 0 );
		$post_ids = array_slice( $state['post_ids'], $offset, self::BATCH_SIZE );

		if ( empty( $post_ids ) ) {
			$state['log'][] = '[DONE] Migration complete.';
			$state['stats'] = array(
				'imported' => (int) ( $state['imported'] ?? 0 ),
				'skipped'  => (int) ( $state['skipped'] ?? 0 ),
			);
			update_option( self::STATE_OPTION, $state, false );
			return;
		}

		foreach ( $post_ids as $post_id ) {
			$post = get_post( (int) $post_id );
			if ( ! $post ) {
				continue;
			}

			// Idempotency: skip if already migrated.
			$existing_map = $state['map'][ $post_id ] ?? null;
			if ( $existing_map && get_post( (int) $existing_map ) ) {
				++$state['skipped'];
				$state['log'][] = sprintf( '[SKIP] Post %d already mapped to %d', $post_id, $existing_map );
				continue;
			}

			$mapping = self::map_post( $post, false );
			if ( $mapping['skip'] ) {
				++$state['skipped'];
				$state['log'][] = sprintf( '[SKIP] Post %d: %s', $post_id, $mapping['reason'] );
				continue;
			}

			$new_id = self::import_post( $post, $mapping );
			if ( $new_id ) {
				$state['map'][ $post_id ] = $new_id;
				++$state['imported'];
				$state['log'][] = sprintf( '[IMPORT] Post %d → adp_app %d', $post_id, $new_id );
			}
		}

		$state['offset'] = $offset + self::BATCH_SIZE;
		update_option( self::STATE_OPTION, $state, false );

		if ( $state['offset'] < count( $state['post_ids'] ) ) {
			wp_schedule_single_event( time() + 5, 'adp_core_migration_batch' );
		} else {
			$state['log'][] = '[DONE] Migration complete.';
			$state['stats'] = array(
				'imported' => (int) $state['imported'],
				'skipped'  => (int) $state['skipped'],
			);
			update_option( self::STATE_OPTION, $state, false );
		}
	}

	/**
	 * Find posts with Appyn meta.
	 *
	 * @return array<int, \WP_Post>
	 */
	public static function find_appyn_posts(): array {
		$query = new \WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => 100,
				'meta_key'       => 'datos_informacion', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			)
		);
		return $query->posts;
	}

	/**
	 * Map Appyn post to adp_app fields.
	 *
	 * @param \WP_Post $post    Source post.
	 * @param bool     $dry_run Whether dry run.
	 * @return array<string, mixed>
	 */
	public static function map_post( \WP_Post $post, bool $dry_run = true ): array {
		unset( $dry_run );
		$info = get_post_meta( $post->ID, 'datos_informacion', true );
		if ( empty( $info ) || ! is_array( $info ) ) {
			return array( 'skip' => true, 'reason' => 'No datos_informacion meta' );
		}

		$package = '';
		if ( ! empty( $info['descripcion'] ) ) {
			$package = sanitize_text_field( (string) $info['descripcion'] );
		}

		return array(
			'skip'    => false,
			'package' => $package,
			'meta'    => array(
				'_adp_short_description'  => sanitize_text_field( (string) ( $info['descripcion'] ?? '' ) ),
				'_adp_package_name'       => $package,
				'_adp_current_version'    => sanitize_text_field( (string) ( $info['version'] ?? '' ) ),
				'_adp_file_size_bytes'    => self::parse_size( (string) ( $info['tamano'] ?? '' ) ),
				'_adp_release_date'       => self::parse_date( (string) ( $info['released_on'] ?? '' ) ),
				'_adp_updated_date'       => self::parse_date( (string) ( $info['fecha_actualizacion'] ?? '' ) ),
				'_adp_android_requirement' => sanitize_text_field( (string) ( $info['requerimientos'] ?? '' ) ),
				'_adp_store_url'          => esc_url_raw( (string) ( $info['consiguelo'] ?? '' ) ),
				'_adp_download_count'     => (int) ( $info['descargas'] ?? 0 ),
				'_adp_whats_new'          => wp_kses_post( (string) ( $info['novedades'] ?? '' ) ),
			),
			'download' => get_post_meta( $post->ID, 'datos_download', true ),
			'video'    => get_post_meta( $post->ID, 'datos_video', true ),
			'images'   => get_post_meta( $post->ID, 'datos_imagenes', true ),
			'category' => $info['categoria_app'] ?? '',
			'os'       => $info['os'] ?? '',
			'offer'    => $info['offer'] ?? '',
		);
	}

	/**
	 * Import a single post.
	 *
	 * @param \WP_Post             $post    Source post.
	 * @param array<string, mixed> $mapping Mapped data.
	 * @return int New post ID or 0.
	 */
	private static function import_post( \WP_Post $post, array $mapping ): int {
		$new_id = wp_insert_post(
			array(
				'post_title'   => $post->post_title,
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
				'post_status'  => $post->post_status,
				'post_type'    => AppPostType::POST_TYPE,
				'post_name'    => $post->post_name . '-migrated',
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			return 0;
		}

		foreach ( $mapping['meta'] as $key => $value ) {
			if ( '' !== $value && 0 !== $value ) {
				Meta::update( $new_id, $key, $value );
			}
		}

		update_post_meta( $new_id, '_adp_migrated_from', $post->ID );

		// Map developer taxonomy.
		$dev_terms = wp_get_post_terms( $post->ID, 'dev', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $dev_terms ) && ! empty( $dev_terms ) ) {
			$term_ids = array();
			foreach ( $dev_terms as $name ) {
				$term = wp_insert_term( $name, 'adp_developer' );
				if ( ! is_wp_error( $term ) ) {
					$term_ids[] = (int) $term['term_id'];
				}
			}
			if ( ! empty( $term_ids ) ) {
				wp_set_object_terms( $new_id, $term_ids, 'adp_developer' );
			}
		}

		// Map versions from datos_download.
		$download = $mapping['download'] ?? array();
		if ( is_array( $download ) && ! empty( $download ) ) {
			$service = new Service();
			$service->create(
				array(
					'app_id'          => $new_id,
					'version_name'    => (string) ( $mapping['meta']['_adp_current_version'] ?? '1.0' ),
					'file_size_bytes' => (int) ( $mapping['meta']['_adp_file_size_bytes'] ?? 0 ),
					'download_type'   => 'external',
					'external_url'    => is_string( $download['url'] ?? '' ) ? $download['url'] : ( is_array( $download ) ? (string) reset( $download ) : '' ),
					'is_current'      => true,
				)
			);
		}

		// Map child posts as version history.
		$children = get_children(
			array(
				'post_parent' => $post->ID,
				'post_type'   => 'post',
				'numberposts' => 50,
			)
		);
		foreach ( $children as $child ) {
			$child_info = get_post_meta( $child->ID, 'datos_informacion', true );
			if ( ! is_array( $child_info ) ) {
				continue;
			}
			$service = new Service();
			$service->create(
				array(
					'app_id'       => $new_id,
					'version_name' => sanitize_text_field( (string) ( $child_info['version'] ?? $child->post_title ) ),
					'changelog'    => wp_kses_post( $child->post_content ),
					'is_current'   => false,
					'download_type' => 'external',
					'external_url'  => '',
				)
			);
		}

		return (int) $new_id;
	}

	/**
	 * Parse Appyn size string to bytes.
	 *
	 * @param string $size Size string.
	 * @return int
	 */
	private static function parse_size( string $size ): int {
		$size = trim( $size );
		if ( '' === $size ) {
			return 0;
		}
		if ( preg_match( '/^([\d.]+)\s*(MB|KB|GB|B)?/i', $size, $matches ) ) {
			$value = (float) $matches[1];
			$unit  = strtoupper( $matches[2] ?? 'B' );
			$mult  = match ( $unit ) {
				'GB' => 1073741824,
				'MB' => 1048576,
				'KB' => 1024,
				default => 1,
			};
			return (int) ( $value * $mult );
		}
		return (int) $size;
	}

	/**
	 * Parse date to Y-m-d.
	 *
	 * @param string $date Date string.
	 * @return string
	 */
	private static function parse_date( string $date ): string {
		$timestamp = strtotime( $date );
		return false !== $timestamp ? gmdate( 'Y-m-d', $timestamp ) : '';
	}

	/**
	 * Get migration state.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_state(): array {
		$state = get_option( self::STATE_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	/**
	 * Update migration state.
	 *
	 * @param array<string, mixed> $state State data.
	 * @return void
	 */
	private static function update_state( array $state ): void {
		update_option( self::STATE_OPTION, $state, false );
	}
}
