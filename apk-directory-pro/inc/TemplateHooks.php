<?php
/**
 * Template hooks and filters.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template-level hooks.
 */
final class TemplateHooks {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_filter( 'the_content', array( self::class, 'add_toc_to_content' ), 20 );
		add_action( 'wp_head', array( self::class, 'output_toc_styles' ), 5 );
	}

	/**
	 * Generate table of contents from H2/H3 headings.
	 *
	 * @param string $content Post content.
	 */
	public static function add_toc_to_content( string $content ): string {
		if ( ! is_singular( array( 'adp_app', 'post', 'page' ) ) ) {
			return $content;
		}

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( is_singular( 'adp_app' ) ) {
			return $content;
		}

		$headings = array();
		$pattern  = '/<h([23])[^>]*>(.*?)<\/h\1>/is';

		if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
			return $content;
		}

		foreach ( $matches as $index => $match ) {
			$level = (int) $match[1];
			$text  = wp_strip_all_tags( $match[2] );
			$id    = 'adp-toc-' . sanitize_title( $text ) . '-' . $index;

			$content = preg_replace(
				'/' . preg_quote( $match[0], '/' ) . '/',
				'<h' . $level . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h' . $level . '>',
				$content,
				1
			);

			$headings[] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => $level,
			);
		}

		if ( count( $headings ) < 2 ) {
			return $content;
		}

		$toc = '<nav class="adp-toc" aria-label="' . esc_attr__( 'Table of contents', 'apk-directory-pro' ) . '">';
		$toc .= '<h2 class="adp-toc__title">' . esc_html__( 'On this page', 'apk-directory-pro' ) . '</h2>';
		$toc .= '<ol class="adp-toc__list">';

		foreach ( $headings as $heading ) {
			$class = $heading['level'] === 3 ? ' class="adp-toc__item--nested"' : '';
			$toc  .= '<li' . $class . '><a href="#' . esc_attr( $heading['id'] ) . '">' . esc_html( $heading['text'] ) . '</a></li>';
		}

		$toc .= '</ol></nav>';

		return $toc . $content;
	}

	/**
	 * Output minimal TOC styles in head for pages using filter.
	 */
	public static function output_toc_styles(): void {
		if ( ! is_singular() ) {
			return;
		}
		// Styles handled in components.css.
	}
}
