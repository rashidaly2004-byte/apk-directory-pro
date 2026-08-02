<?php

namespace APD\Theme;

final class Assets {

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
		add_action( 'wp_head', array( self::class, 'inline_theme_vars' ), 1 );
	}

	public static function enqueue(): void {
		wp_enqueue_style(
			'adp-main',
			ADP_THEME_URI . '/assets/css/main.css',
			array(),
			ADP_THEME_VERSION
		);

		wp_enqueue_script(
			'adp-theme-mode',
			ADP_THEME_URI . '/assets/js/theme-mode.js',
			array(),
			ADP_THEME_VERSION,
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'adp-navigation',
			ADP_THEME_URI . '/assets/js/navigation.js',
			array(),
			ADP_THEME_VERSION,
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'adp-search',
			ADP_THEME_URI . '/assets/js/search.js',
			array(),
			ADP_THEME_VERSION,
			array( 'strategy' => 'defer' )
		);

		if ( is_singular( 'adp_app' ) ) {
			wp_enqueue_script(
				'adp-lightbox',
				ADP_THEME_URI . '/assets/js/lightbox.js',
				array(),
				ADP_THEME_VERSION,
				array( 'strategy' => 'defer' )
			);
			wp_enqueue_script(
				'adp-report',
				ADP_THEME_URI . '/assets/js/report.js',
				array(),
				ADP_THEME_VERSION,
				array( 'strategy' => 'defer' )
			);
			wp_add_inline_script(
				'adp-report',
				'window.adpReportNonce = "' . esc_js( wp_create_nonce( 'wp_rest' ) ) . '";',
				'before'
			);
		}

		if ( is_post_type_archive( 'adp_app' ) || is_tax( array( 'adp_app_category', 'adp_developer', 'adp_platform' ) ) ) {
			wp_enqueue_script(
				'adp-archive-filters',
				ADP_THEME_URI . '/assets/js/archive-filters.js',
				array(),
				ADP_THEME_VERSION,
				array( 'strategy' => 'defer' )
			);
		}

		wp_localize_script( 'adp-search', 'adpSearch', array(
			'restUrl' => esc_url_raw( rest_url( 'adp/v1/search' ) ),
			'i18n'    => array(
				'noResults' => __( 'No results found', 'apk-directory-pro' ),
			),
		) );
	}

	public static function inline_theme_vars(): void {
		$primary = get_theme_mod( 'adp_primary_color', '#18a957' );
		echo '<style id="adp-theme-vars">:root{--adp-primary:' . esc_attr( $primary ) . ';}</style>';
		echo '<script>try{var m=localStorage.getItem("adp-theme-mode");if(m)document.documentElement.setAttribute("data-theme",m);}catch(e){}</script>';
	}
}
