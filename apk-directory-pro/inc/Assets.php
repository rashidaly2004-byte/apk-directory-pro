<?php
/**
 * Asset registration and enqueue.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles CSS and JS assets.
 */
final class Assets {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) );
		add_action( 'wp_head', array( self::class, 'inline_dark_mode_script' ), 0 );
		add_filter( 'script_loader_tag', array( self::class, 'add_module_type' ), 10, 3 );
		add_filter( 'style_loader_tag', array( self::class, 'add_preload' ), 10, 4 );
	}

	/**
	 * Get file version based on modification time.
	 */
	public static function version( string $relative_path ): string {
		$path = ADP_THEME_DIR . '/' . ltrim( $relative_path, '/' );
		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}
		return ADP_THEME_VERSION;
	}

	/**
	 * Enqueue stylesheets.
	 */
	public static function enqueue_styles(): void {
		$styles = array(
			'adp-tokens'     => 'assets/css/tokens.css',
			'adp-base'       => 'assets/css/base.css',
			'adp-layout'     => 'assets/css/layout.css',
			'adp-components' => 'assets/css/components.css',
			'adp-utilities'  => 'assets/css/utilities.css',
			'adp-dark'       => 'assets/css/dark.css',
		);

		foreach ( $styles as $handle => $path ) {
			wp_enqueue_style(
				$handle,
				ADP_THEME_URI . '/' . $path,
				$handle === 'adp-tokens' ? array() : array( 'adp-tokens' ),
				self::version( $path )
			);
		}

		wp_enqueue_style(
			'adp-theme',
			get_stylesheet_uri(),
			array( 'adp-dark' ),
			ADP_THEME_VERSION
		);
	}

	/**
	 * Enqueue JavaScript modules.
	 */
	public static function enqueue_scripts(): void {
		$main_deps = array();

		// Dark mode loads first (no defer needed, tiny).
		wp_enqueue_script(
			'adp-dark-mode',
			ADP_THEME_URI . '/assets/js/dark-mode.js',
			array(),
			self::version( 'assets/js/dark-mode.js' ),
			array( 'strategy' => 'defer' )
		);

		wp_enqueue_script(
			'adp-nav',
			ADP_THEME_URI . '/assets/js/nav.js',
			array(),
			self::version( 'assets/js/nav.js' ),
			array( 'strategy' => 'defer' )
		);
		$main_deps[] = 'adp-nav';

		wp_enqueue_script(
			'adp-main',
			ADP_THEME_URI . '/assets/js/main.js',
			$main_deps,
			self::version( 'assets/js/main.js' ),
			array( 'strategy' => 'defer' )
		);

		if ( is_search() || is_front_page() || is_archive() ) {
			wp_enqueue_script(
				'adp-search',
				ADP_THEME_URI . '/assets/js/search.js',
				array(),
				self::version( 'assets/js/search.js' ),
				array( 'strategy' => 'defer' )
			);
		}

		if ( is_post_type_archive( 'adp_app' ) || is_tax( array( 'adp_app_category', 'adp_developer', 'adp_platform', 'adp_tag' ) ) ) {
			wp_enqueue_script(
				'adp-filters',
				ADP_THEME_URI . '/assets/js/filters.js',
				array(),
				self::version( 'assets/js/filters.js' ),
				array( 'strategy' => 'defer' )
			);
		}

		if ( is_singular( 'adp_app' ) ) {
			wp_enqueue_script(
				'adp-lightbox',
				ADP_THEME_URI . '/assets/js/lightbox.js',
				array(),
				self::version( 'assets/js/lightbox.js' ),
				array( 'strategy' => 'defer' )
			);

			wp_enqueue_script(
				'adp-reviews',
				ADP_THEME_URI . '/assets/js/reviews.js',
				array(),
				self::version( 'assets/js/reviews.js' ),
				array( 'strategy' => 'defer' )
			);
		}

		wp_localize_script( 'adp-main', 'adpTheme', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'restUrl'      => esc_url_raw( rest_url( 'wp/v2/' ) ),
			'adpRestUrl'   => esc_url_raw( rest_url( 'adp/v1/' ) ),
			'searchUrl'    => esc_url_raw( rest_url( 'adp/v1/search' ) ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'reviewNonce'  => is_singular( 'adp_app' ) ? wp_create_nonce( 'adp_submit_review_' . get_queried_object_id() ) : '',
			'reportNonce'  => is_singular( 'adp_app' ) ? wp_create_nonce( 'adp_submit_report_' . get_queried_object_id() ) : '',
			'searchMin'    => 2,
			'debounce'     => 250,
			'i18n'      => array(
				'searchPlaceholder' => __( 'Search apps…', 'apk-directory-pro' ),
				'noResults'         => __( 'No results found', 'apk-directory-pro' ),
				'loading'           => __( 'Loading…', 'apk-directory-pro' ),
				'loadMore'          => __( 'Load more', 'apk-directory-pro' ),
				'darkMode'          => __( 'Toggle dark mode', 'apk-directory-pro' ),
				'closeMenu'         => __( 'Close menu', 'apk-directory-pro' ),
				'openMenu'          => __( 'Open menu', 'apk-directory-pro' ),
			),
		) );
	}

	/**
	 * Inline script to prevent dark mode FOUC.
	 */
	public static function inline_dark_mode_script(): void {
		?>
		<script>
		(function(){
			var s=localStorage.getItem('adp-color-scheme');
			if(s==='dark'||(!s&&window.matchMedia('(prefers-color-scheme:dark)').matches)){
				document.documentElement.setAttribute('data-theme','dark');
			}
		})();
		</script>
		<?php
	}

	/**
	 * Add type="module" to theme scripts.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Handle.
	 * @param string $src    Source URL.
	 */
	public static function add_module_type( string $tag, string $handle, string $src ): string {
		$modules = array(
			'adp-main',
			'adp-search',
			'adp-filters',
			'adp-dark-mode',
			'adp-nav',
			'adp-lightbox',
			'adp-reviews',
		);

		if ( in_array( $handle, $modules, true ) ) {
			// Replace existing type attribute or insert type="module".
			if ( preg_match( '/\stype=(["\'])[^"\']*\1/', $tag ) ) {
				return (string) preg_replace( '/\stype=(["\'])[^"\']*\1/', ' type="module"', $tag, 1 );
			}
			return (string) preg_replace( '/<script\b/', '<script type="module"', $tag, 1 );
		}

		return $tag;
	}

	/**
	 * Preload critical CSS.
	 *
	 * @param string $html   Link tag HTML.
	 * @param string $handle Handle.
	 * @param string $href   URL.
	 * @param string $media  Media attribute.
	 */
	public static function add_preload( string $html, string $handle, string $href, string $media ): string {
		if ( $handle === 'adp-tokens' ) {
			return '<link rel="preload" href="' . esc_url( $href ) . '" as="style" />' . $html;
		}
		return $html;
	}
}
