<?php

namespace APD\Theme;

final class TemplateHooks {

	public static function init(): void {
		add_action( 'adp_before_content', [ self::class, 'breadcrumbs' ] );
		add_filter( 'body_class', [ self::class, 'body_classes' ] );
		add_filter( 'wp_robots', [ self::class, 'search_noindex' ] );
	}

	public static function breadcrumbs(): void {
		if ( is_front_page() ) {
			return;
		}
		get_template_part( 'template-parts/navigation/breadcrumbs' );
	}

	public static function body_classes( array $classes ): array {
		if ( get_theme_mod( 'adp_sticky_header', true ) ) {
			$classes[] = 'has-sticky-header';
		}
		return $classes;
	}

	public static function search_noindex( array $robots ): array {
		if ( is_search() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
}
