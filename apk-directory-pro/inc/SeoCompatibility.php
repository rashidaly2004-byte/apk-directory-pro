<?php

namespace APD\Theme;

final class SeoCompatibility {

	public static function init(): void {
		add_filter( 'adp_output_breadcrumbs', [ self::class, 'maybe_disable_breadcrumbs' ] );
	}

	public static function maybe_disable_breadcrumbs( bool $output ): bool {
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return false;
		}
		return $output;
	}
}
