<?php
/**
 * Accessibility enhancements.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility features.
 */
final class Accessibility {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'wp_body_open', array( self::class, 'skip_link' ), 1 );
		add_filter( 'nav_menu_link_attributes', array( self::class, 'nav_link_attributes' ), 10, 4 );
		add_filter( 'wp_get_attachment_image_attributes', array( self::class, 'image_attributes' ), 10, 3 );
		add_action( 'wp_footer', array( self::class, 'live_region' ), 5 );
	}

	/**
	 * Output skip to content link.
	 */
	public static function skip_link(): void {
		echo '<a class="adp-skip-link" href="#main-content">' . esc_html__( 'Skip to content', 'apk-directory-pro' ) . '</a>';
	}

	/**
	 * Add aria attributes to nav links.
	 *
	 * @param array<string, string> $atts   Link attributes.
	 * @param \WP_Post               $item   Menu item.
	 * @param \stdClass              $args   Menu args.
	 * @param int                    $depth  Depth.
	 * @return array<string, string>
	 */
	public static function nav_link_attributes( array $atts, \WP_Post $item, \stdClass $args, int $depth ): array {
		if ( isset( $args->theme_location ) && in_array( $args->theme_location, array( 'primary', 'mobile', 'category-bar' ), true ) ) {
			if ( in_array( 'current-menu-item', $item->classes, true ) ) {
				$atts['aria-current'] = 'page';
			}
		}
		return $atts;
	}

	/**
	 * Ensure images have alt text.
	 *
	 * @param array<string, string> $attr       Image attributes.
	 * @param \WP_Post              $attachment Attachment post.
	 * @param string|int[]          $size       Image size.
	 * @return array<string, string>
	 */
	public static function image_attributes( array $attr, \WP_Post $attachment, $size ): array {
		if ( empty( $attr['alt'] ) ) {
			$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			if ( ! is_string( $alt ) || $alt === '' ) {
				$alt = $attachment->post_title;
			}
			$attr['alt'] = $alt;
		}

		if ( get_theme_mod_bool( 'adp_lazy_load_images', true ) ) {
			$attr['loading'] = 'lazy';
			$attr['decoding'] = 'async';
		}

		return $attr;
	}

	/**
	 * ARIA live region for dynamic updates.
	 */
	public static function live_region(): void {
		echo '<div id="adp-live-region" class="adp-sr-only" aria-live="polite" aria-atomic="true"></div>';
	}
}
