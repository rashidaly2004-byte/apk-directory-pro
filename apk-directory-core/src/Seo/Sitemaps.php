<?php
/**
 * Sitemap exclusions for download tokens.
 *
 * @package Adp\Core\Seo
 */

namespace Adp\Core\Seo;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap and robots adjustments.
 */
class Sitemaps {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'wp_sitemaps_posts_query_args', array( self::class, 'exclude_private_types' ), 10, 2 );
		add_filter( 'wp_sitemaps_post_types', array( self::class, 'add_app_post_type' ) );
		add_filter( 'wp_sitemaps_taxonomies', array( self::class, 'add_taxonomies' ) );
		add_filter( 'robots_txt', array( self::class, 'robots_txt' ), 10, 2 );
	}

	/**
	 * Exclude private post types from sitemaps.
	 *
	 * @param array<string, mixed> $args      Query args.
	 * @param string               $post_type Post type.
	 * @return array<string, mixed>
	 */
	public static function exclude_private_types( array $args, string $post_type ): array {
		if ( 'adp_report' === $post_type ) {
			$args['post__in'] = array( 0 );
		}
		return $args;
	}

	/**
	 * Ensure app post type is in sitemaps.
	 *
	 * @param array<string, \WP_Post_Type> $post_types Post types.
	 * @return array<string, \WP_Post_Type>
	 */
	public static function add_app_post_type( array $post_types ): array {
		if ( post_type_exists( 'adp_app' ) ) {
			$post_types['adp_app'] = get_post_type_object( 'adp_app' );
		}
		return $post_types;
	}

	/**
	 * Ensure app taxonomies are in sitemaps.
	 *
	 * @param array<string, \WP_Taxonomy> $taxonomies Taxonomies.
	 * @return array<string, \WP_Taxonomy>
	 */
	public static function add_taxonomies( array $taxonomies ): array {
		foreach ( array( 'adp_app_category', 'adp_developer', 'adp_platform', 'adp_tag' ) as $tax ) {
			if ( taxonomy_exists( $tax ) ) {
				$taxonomies[ $tax ] = get_taxonomy( $tax );
			}
		}
		return $taxonomies;
	}

	/**
	 * Add robots.txt rules for download URLs.
	 *
	 * @param string $output    Robots.txt output.
	 * @param bool   $is_public Whether site is public.
	 * @return string
	 */
	public static function robots_txt( string $output, bool $is_public ): string {
		if ( $is_public ) {
			$output .= "\nDisallow: /download/\n";
		}
		return $output;
	}
}
