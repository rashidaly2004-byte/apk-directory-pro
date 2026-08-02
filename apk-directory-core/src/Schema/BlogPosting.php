<?php
/**
 * BlogPosting structured data.
 *
 * @package Adp\Core\Schema
 */

namespace Adp\Core\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Outputs BlogPosting JSON-LD when no SEO plugin owns it.
 */
class BlogPosting {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_head', array( self::class, 'output' ), 5 );
	}

	/**
	 * Output schema on single blog posts.
	 *
	 * @return void
	 */
	public static function output(): void {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		if ( self::seo_plugin_active() ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$schema = self::build( $post_id );
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Build BlogPosting schema.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>
	 */
	public static function build( int $post_id ): array {
		$author_id = (int) get_post_field( 'post_author', $post_id );
		$image     = get_the_post_thumbnail_url( $post_id, 'full' );

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title( $post_id ),
			'datePublished' => get_the_date( 'c', $post_id ),
			'dateModified'  => get_the_modified_date( 'c', $post_id ),
			'url'           => get_permalink( $post_id ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author_id ),
			),
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( $image ) {
			$schema['image'] = $image;
		}

		$excerpt = get_the_excerpt( $post_id );
		if ( $excerpt ) {
			$schema['description'] = wp_strip_all_tags( $excerpt );
		}

		return $schema;
	}

	/**
	 * Check if SEO plugin is active.
	 *
	 * @return bool
	 */
	private static function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
	}
}
