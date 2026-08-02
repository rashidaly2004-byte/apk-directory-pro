<?php

namespace APD\Core\Migration;

use APD\Core\Content\AppPostType;
use APD\Core\Content\Meta;
use APD\Core\Versions\Repository;
use APD\Core\Versions\Service;

/**
 * Full Appyn migration with batching, logging, idempotency, and rollback.
 */
final class MigrationService {

	public const BATCH_SIZE = 10;
	public const STATE_KEY  = 'adp_migration_state';
	public const LOG_KEY    = 'adp_migration_log';
	public const MAP_KEY    = 'adp_migration_map';

	/**
	 * @return array{total:int,processed:int,status:string,errors:array}
	 */
	public function get_state(): array {
		$state = get_option( self::STATE_KEY, array() );
		return wp_parse_args(
			$state,
			array(
				'total'     => 0,
				'processed' => 0,
				'status'    => 'idle',
				'errors'    => array(),
				'dry_run'   => false,
			)
		);
	}

	public function reset_state(): void {
		delete_option( self::STATE_KEY );
	}

	/**
	 * Preview migration without writes.
	 *
	 * @return array{count:int,preview:array}
	 */
	public function dry_run(): array {
		$posts   = $this->find_source_posts( 100 );
		$preview = array();
		foreach ( $posts as $post_id ) {
			$preview[] = $this->build_preview_row( $post_id );
		}
		return array(
			'count'   => $this->count_source_posts(),
			'preview' => $preview,
		);
	}

	public function start_migration( bool $dry_run = false ): array {
		$total = $this->count_source_posts();
		$state = array(
			'total'     => $total,
			'processed' => 0,
			'status'    => $dry_run ? 'dry_run_complete' : 'running',
			'errors'    => array(),
			'dry_run'   => $dry_run,
			'offset'    => 0,
		);
		update_option( self::STATE_KEY, $state, false );

		if ( $dry_run ) {
			$result           = $this->dry_run();
			$state['status']  = 'dry_run_complete';
			$state['preview'] = $result['preview'];
			update_option( self::STATE_KEY, $state, false );
			return $state;
		}

		return $this->process_batch();
	}

	public function process_batch(): array {
		$state = $this->get_state();
		if ( $state['status'] === 'complete' ) {
			return $state;
		}

		$offset = (int) ( $state['offset'] ?? 0 );
		$posts  = $this->find_source_posts( self::BATCH_SIZE, $offset );

		if ( empty( $posts ) ) {
			$state['status'] = 'complete';
			update_option( self::STATE_KEY, $state, false );
			return $state;
		}

		foreach ( $posts as $source_id ) {
			try {
				$this->migrate_post( (int) $source_id );
				++$state['processed'];
			} catch ( \Throwable $e ) {
				$state['errors'][] = sprintf( 'Post %d: %s', $source_id, $e->getMessage() );
				$this->log( 'error', $source_id, $e->getMessage() );
			}
		}

		$state['offset'] = $offset + count( $posts );
		$state['status'] = 'running';
		if ( $state['processed'] >= $state['total'] || count( $posts ) < self::BATCH_SIZE ) {
			$state['status'] = 'complete';
		}
		update_option( self::STATE_KEY, $state, false );

		return $state;
	}

	public function rollback(): array {
		$map    = get_option( self::MAP_KEY, array() );
		$rolled = 0;
		$errors = array();

		if ( empty( $map ) ) {
			return array(
				'rolled' => 0,
				'errors' => array( 'No migration map found.' ),
			);
		}

		$repo = new Repository();

		foreach ( $map as $entry ) {
			$new_id = (int) ( $entry['new_post_id'] ?? 0 );
			$source = (int) ( $entry['source_post_id'] ?? 0 );
			if ( ! $new_id ) {
				continue;
			}

			// Delete version rows created for this app.
			if ( ! empty( $entry['version_ids'] ) ) {
				foreach ( $entry['version_ids'] as $vid ) {
					$repo->delete( (int) $vid );
				}
			}

			// Delete migrated post only — never touch source.
			$post = get_post( $new_id );
			if ( $post && $post->post_type === AppPostType::POST_TYPE ) {
				$result = wp_delete_post( $new_id, true );
				if ( $result ) {
					++$rolled;
					$this->log( 'rollback', $source, 'Removed migrated post ' . $new_id );
				} else {
					$errors[] = sprintf( 'Failed to delete post %d', $new_id );
				}
			}
		}

		delete_option( self::MAP_KEY );
		$this->reset_state();
		$this->log( 'rollback_complete', 0, sprintf( 'Rolled back %d posts', $rolled ) );

		return array(
			'rolled' => $rolled,
			'errors' => $errors,
		);
	}

	public function migrate_post( int $source_id ): int {
		$map = get_option( self::MAP_KEY, array() );

		// Idempotency: skip if already migrated.
		foreach ( $map as $entry ) {
			if ( (int) $entry['source_post_id'] === $source_id && ! empty( $entry['new_post_id'] ) ) {
				return (int) $entry['new_post_id'];
			}
		}

		$source = get_post( $source_id );
		if ( ! $source ) {
			throw new \RuntimeException( 'Source post not found' );
		}

		$info = get_post_meta( $source_id, 'datos_informacion', true );
		if ( ! is_array( $info ) ) {
			$info = maybe_unserialize( $info );
		}
		if ( ! is_array( $info ) ) {
			throw new \RuntimeException( 'No datos_informacion meta' );
		}

		$new_id = wp_insert_post(
			array(
				'post_type'    => AppPostType::POST_TYPE,
				'post_status'  => $source->post_status === 'publish' ? 'publish' : 'draft',
				'post_title'   => $source->post_title,
				'post_content' => $source->post_content,
				'post_excerpt' => $source->post_excerpt,
				'post_author'  => $source->post_author,
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			/** @var \WP_Error $new_id */
			$message = sanitize_text_field( $new_id->get_error_message() );
			throw new \RuntimeException( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$new_post_id = (int) $new_id;
		$this->map_meta( $new_post_id, $info, $source_id );
		$this->map_taxonomies( $new_post_id, $source_id );
		$version_ids = $this->map_versions( $new_post_id, $source_id );

		$entry = array(
			'source_post_id' => $source_id,
			'new_post_id'    => $new_post_id,
			'version_ids'    => $version_ids,
			'migrated_at'    => gmdate( 'c' ),
		);
		$map[] = $entry;
		update_option( self::MAP_KEY, $map, false );

		$this->log( 'migrated', $source_id, 'Created adp_app ' . $new_post_id );

		return $new_post_id;
	}

	private function map_meta( int $new_id, array $info, int $source_id ): void {
		$map = array(
			'short_description'   => $info['descripcion'] ?? '',
			'current_version'     => $info['version'] ?? '',
			'file_size_bytes'     => $this->parse_size( $info['tamano'] ?? '' ),
			'release_date'        => $this->parse_date( $info['released_on'] ?? '' ),
			'updated_date'        => $this->parse_date( $info['fecha_actualizacion'] ?? '' ),
			'android_requirement' => $info['requerimientos'] ?? '',
			'store_url'           => $info['consiguelo'] ?? '',
			'package_name'        => $info['package'] ?? $info['package_name'] ?? '',
			'price_type'          => $this->map_price( $info['offer'] ?? '' ),
			'whats_new'           => $info['novedades'] ?? '',
		);

		foreach ( $map as $key => $value ) {
			if ( $value !== '' && $value !== 0 ) {
				update_post_meta( $new_id, '_adp_' . $key, $value );
			}
		}

		if ( isset( $info['descargas'] ) ) {
			update_post_meta( $new_id, '_adp_download_count', absint( $info['descargas'] ) );
		}

		// Screenshots from datos_imagenes.
		$images = get_post_meta( $source_id, 'datos_imagenes', true );
		if ( is_array( $images ) ) {
			$ids = array();
			foreach ( $images as $img ) {
				if ( is_numeric( $img ) ) {
					$ids[] = (int) $img;
				} elseif ( is_array( $img ) && isset( $img['id'] ) ) {
					$ids[] = (int) $img['id'];
				}
			}
			if ( $ids ) {
				update_post_meta( $new_id, '_adp_screenshot_ids', $ids );
			}
		}

		// Video.
		$video = get_post_meta( $source_id, 'datos_video', true );
		if ( is_array( $video ) && ! empty( $video['id'] ) ) {
			$url = 'https://www.youtube.com/watch?v=' . sanitize_text_field( $video['id'] );
			update_post_meta( $new_id, '_adp_video_url', esc_url_raw( $url ) );
		}

		// Thumbnail from source.
		$thumb = get_post_thumbnail_id( $source_id );
		if ( $thumb ) {
			set_post_thumbnail( $new_id, $thumb );
		}
	}

	private function map_taxonomies( int $new_id, int $source_id ): void {
		$dev = get_post_meta( $source_id, 'dev', true );
		if ( $dev ) {
			$term = term_exists( $dev, 'adp_developer' );
			if ( ! $term ) {
				$term = wp_insert_term( $dev, 'adp_developer' );
			}
			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $new_id, (int) $term['term_id'], 'adp_developer' );
			}
		}

		$cat = get_post_meta( $source_id, 'categoria_app', true );
		if ( $cat ) {
			$term = term_exists( $cat, 'adp_app_category' );
			if ( ! $term ) {
				$term = wp_insert_term( $cat, 'adp_app_category' );
			}
			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $new_id, (int) $term['term_id'], 'adp_app_category' );
			}
		}

		$os = get_post_meta( $source_id, 'os', true );
		if ( $os ) {
			$term = term_exists( $os, 'adp_platform' );
			if ( ! $term ) {
				$term = wp_insert_term( $os, 'adp_platform' );
			}
			if ( ! is_wp_error( $term ) ) {
				wp_set_object_terms( $new_id, (int) $term['term_id'], 'adp_platform' );
			}
		}
	}

	/**
	 * Map download rows and child posts to version records.
	 *
	 * @param int $new_id    Migrated app post ID.
	 * @param int $source_id Source Appyn post ID.
	 * @return int[]
	 */
	private function map_versions( int $new_id, int $source_id ): array {
		$download = get_post_meta( $source_id, 'datos_download', true );
		if ( ! is_array( $download ) ) {
			$download = maybe_unserialize( $download );
		}
		if ( ! is_array( $download ) ) {
			return array();
		}

		$service = new Service();
		$ids     = array();

		foreach ( $download as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$vid   = $service->create_version(
				$new_id,
				array(
					'version_name'    => $row['version'] ?? $row['version_name'] ?? '1.0',
					'file_size_bytes' => $this->parse_size( $row['tamano'] ?? $row['size'] ?? '' ),
					'external_url'    => $row['url'] ?? $row['link'] ?? '',
					'download_type'   => ! empty( $row['url'] ) ? 'external' : 'media',
					'attachment_id'   => isset( $row['attachment_id'] ) ? absint( $row['attachment_id'] ) : null,
					'is_current'      => ! empty( $row['current'] ),
					'changelog'       => $row['changelog'] ?? '',
				)
			);
			$ids[] = $vid;
		}

		// Child posts as version history.
		$children = get_posts(
			array(
				'post_parent'    => $source_id,
				'post_type'      => $source_id ? get_post_type( $source_id ) : 'post',
				'posts_per_page' => 50,
				'fields'         => 'ids',
			)
		);
		foreach ( $children as $child_id ) {
			$child = get_post( $child_id );
			if ( ! $child ) {
				continue;
			}
			$child_info = get_post_meta( $child_id, 'datos_informacion', true );
			if ( ! is_array( $child_info ) ) {
				$child_info = maybe_unserialize( $child_info );
			}
			$ids[] = $service->create_version(
				$new_id,
				array(
					'version_name'    => is_array( $child_info ) ? ( $child_info['version'] ?? $child->post_title ) : $child->post_title,
					'file_size_bytes' => is_array( $child_info ) ? $this->parse_size( $child_info['tamano'] ?? '' ) : 0,
					'changelog'       => $child->post_content,
					'is_current'      => false,
				)
			);
		}

		return $ids;
	}

	private function build_preview_row( int $post_id ): array {
		$post = get_post( $post_id );
		$info = get_post_meta( $post_id, 'datos_informacion', true );
		if ( ! is_array( $info ) ) {
			$info = maybe_unserialize( $info );
		}
		return array(
			'id'      => $post_id,
			'title'   => $post ? $post->post_title : '',
			'version' => is_array( $info ) ? ( $info['version'] ?? '' ) : '',
		);
	}

	public function count_source_posts(): int {
		global $wpdb;
		return (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = 'datos_informacion'"
		);
	}

	/**
	 * Find source posts with Appyn meta.
	 *
	 * @param int $limit  Batch size.
	 * @param int $offset Offset for pagination.
	 * @return int[]
	 */
	public function find_source_posts( int $limit, int $offset = 0 ): array {
		global $wpdb;
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'datos_informacion' ORDER BY post_id ASC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
		return array_map( 'intval', $ids );
	}

	private function parse_size( string $size ): int {
		if ( is_numeric( $size ) ) {
			return (int) $size;
		}
		if ( preg_match( '/([\d.]+)\s*(MB|KB|GB)/i', $size, $m ) ) {
			$val  = (float) $m[1];
			$unit = strtoupper( $m[2] );
			return (int) match ( $unit ) {
				'KB' => $val * 1024,
				'MB' => $val * 1024 * 1024,
				'GB' => $val * 1024 * 1024 * 1024,
				default => (int) $val,
			};
		}
		return 0;
	}

	private function parse_date( string $date ): string {
		if ( ! $date ) {
			return '';
		}
		$ts = strtotime( $date );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}

	private function map_price( string $offer ): string {
		$offer = strtolower( $offer );
		if ( str_contains( $offer, 'free' ) ) {
			return 'free';
		}
		if ( str_contains( $offer, 'paid' ) ) {
			return 'paid';
		}
		return 'freemium';
	}

	private function log( string $action, int $post_id, string $message ): void {
		$log   = get_option( self::LOG_KEY, array() );
		$log[] = array(
			'time'    => gmdate( 'c' ),
			'action'  => $action,
			'post_id' => $post_id,
			'message' => $message,
		);
		// Keep last 500 entries.
		if ( count( $log ) > 500 ) {
			$log = array_slice( $log, -500 );
		}
		update_option( self::LOG_KEY, $log, false );
	}

	public function get_log(): array {
		return get_option( self::LOG_KEY, array() );
	}
}
