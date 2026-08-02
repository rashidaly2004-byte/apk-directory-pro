<?php
/**
 * SEO plugin compatibility.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prevents duplicate meta tags when SEO plugins are active.
 */
final class SeoCompatibility {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		if ( ! get_theme_mod_bool( 'adp_defer_seo_to_plugin', true ) ) {
			return;
		}

		add_action( 'wp_head', array( self::class, 'maybe_remove_theme_meta' ), 0 );
		add_filter( 'adp_theme_output_meta_description', array( self::class, 'suppress_if_seo_active' ) );
		add_filter( 'adp_theme_output_canonical', array( self::class, 'suppress_if_seo_active' ) );
		add_filter( 'adp_theme_output_og_tags', array( self::class, 'suppress_if_seo_active' ) );
	}

	/**
	 * Check if a known SEO plugin is active.
	 */
	public static function is_seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| defined( 'AIOSEO_VERSION' )
			|| defined( 'SEOPRESS_VERSION' )
			|| class_exists( 'The_SEO_Framework\Load' );
	}

	/**
	 * Remove theme meta output early if SEO plugin handles it.
	 */
	public static function maybe_remove_theme_meta(): void {
		if ( ! self::is_seo_plugin_active() ) {
			add_action( 'wp_head', array( self::class, 'output_fallback_meta' ), 1 );
		}
	}

	/**
	 * Suppress theme meta when SEO plugin is active.
	 *
	 * @param mixed $value Value to suppress.
	 */
	public static function suppress_if_seo_active( $value ): mixed {
		return self::is_seo_plugin_active() ? false : $value;
	}

	/**
	 * Fallback meta tags when no SEO plugin is present.
	 */
	public static function output_fallback_meta(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();
		$desc    = get_post_meta( $post_id, '_adp_short_description', true );

		if ( ! is_string( $desc ) || $desc === '' ) {
			$desc = wp_trim_words( get_the_excerpt( $post_id ), 30, '…' );
		}

		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '" />' . "\n";
		}

		$canonical = get_permalink( $post_id );
		if ( $canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
		}

		$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( $thumb ) {
			echo '<meta property="og:image" content="' . esc_url( $thumb ) . '" />' . "\n";
		}

		echo '<meta property="og:title" content="' . esc_attr( get_the_title( $post_id ) ) . '" />' . "\n";
		echo '<meta property="og:type" content="article" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $canonical ) . '" />' . "\n";
	}
}
