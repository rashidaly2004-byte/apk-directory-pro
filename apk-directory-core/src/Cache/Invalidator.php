<?php
/**
 * Cache invalidation on content changes.
 *
 * @package Adp\Core\Cache
 */

namespace Adp\Core\Cache;

use Adp\Core\Content\AppPostType;

defined( 'ABSPATH' ) || exit;

/**
 * Clears transients and caches on relevant saves.
 */
class Invalidator {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'save_post_' . AppPostType::POST_TYPE, array( self::class, 'on_app_save' ), 10, 2 );
		add_action( 'edited_term', array( self::class, 'on_term_edit' ), 10, 3 );
		add_action( 'delete_post', array( self::class, 'on_post_delete' ) );
	}

	/**
	 * Invalidate caches when an app is saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function on_app_save( int $post_id, \WP_Post $post ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		unset( $post );
		self::clear_search_transients();
		self::clear_app_transients( $post_id );
	}

	/**
	 * Invalidate caches when a term is edited.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return void
	 */
	public static function on_term_edit( int $term_id, int $tt_id, string $taxonomy ): void {
		unset( $term_id, $tt_id );
		if ( in_array( $taxonomy, array( 'adp_app_category', 'adp_developer', 'adp_platform', 'adp_tag' ), true ) ) {
			self::clear_search_transients();
		}
	}

	/**
	 * Invalidate on post delete.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function on_post_delete( int $post_id ): void {
		if ( AppPostType::POST_TYPE === get_post_type( $post_id ) ) {
			self::clear_app_transients( $post_id );
			self::clear_search_transients();
		}
	}

	/**
	 * Clear search-related transients.
	 *
	 * @return void
	 */
	public static function clear_search_transients(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_adp_search_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_adp_search_' ) . '%'
			)
		);
	}

	/**
	 * Clear app-specific transients.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public static function clear_app_transients( int $post_id ): void {
		delete_transient( 'adp_app_rail_' . $post_id );
		delete_transient( 'adp_trending_apps' );
		delete_transient( 'adp_featured_categories' );
	}
}
