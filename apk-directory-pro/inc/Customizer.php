<?php
/**
 * Theme Customizer settings.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer registration.
 */
final class Customizer {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'customize_register', array( self::class, 'register' ) );
		add_action( 'customize_preview_init', array( self::class, 'preview_scripts' ) );
	}

	/**
	 * Register Customizer sections and settings.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer manager.
	 */
	public static function register( \WP_Customize_Manager $wp_customize ): void {
		self::register_branding( $wp_customize );
		self::register_header( $wp_customize );
		self::register_homepage( $wp_customize );
		self::register_app_page( $wp_customize );
		self::register_archive( $wp_customize );
		self::register_footer( $wp_customize );
		self::register_ads( $wp_customize );
		self::register_performance( $wp_customize );
		self::register_integrations( $wp_customize );
	}

	/**
	 * Branding section.
	 */
	private static function register_branding( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_branding', array(
			'title'    => __( 'Branding', 'apk-directory-pro' ),
			'priority' => 20,
		) );

		$wp_customize->add_setting( 'adp_site_tagline', array(
			'default'           => __( 'Discover apps you can trust', 'apk-directory-pro' ),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'adp_site_tagline', array(
			'label'   => __( 'Site Tagline', 'apk-directory-pro' ),
			'section' => 'adp_branding',
			'type'    => 'text',
		) );
	}

	/**
	 * Header section.
	 */
	private static function register_header( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_header', array(
			'title'    => __( 'Header', 'apk-directory-pro' ),
			'priority' => 30,
		) );

		$wp_customize->add_setting( 'adp_show_search', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_show_search', array(
			'label'   => __( 'Show header search', 'apk-directory-pro' ),
			'section' => 'adp_header',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'adp_show_category_bar', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_show_category_bar', array(
			'label'   => __( 'Show category bar', 'apk-directory-pro' ),
			'section' => 'adp_header',
			'type'    => 'checkbox',
		) );
	}

	/**
	 * Homepage sections.
	 */
	private static function register_homepage( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_homepage', array(
			'title'    => __( 'Homepage Sections', 'apk-directory-pro' ),
			'priority' => 40,
		) );

		$sections = array(
			'hero_search'          => __( 'Hero Search', 'apk-directory-pro' ),
			'featured_categories'  => __( 'Featured Categories', 'apk-directory-pro' ),
			'trending'             => __( 'Trending', 'apk-directory-pro' ),
			'latest_updates'       => __( 'Latest Updates', 'apk-directory-pro' ),
			'latest_games'         => __( 'Latest Games', 'apk-directory-pro' ),
			'editors_choice'       => __( "Editor's Choice", 'apk-directory-pro' ),
			'popular'              => __( 'Popular', 'apk-directory-pro' ),
			'latest_blog'          => __( 'Latest Blog', 'apk-directory-pro' ),
			'seo_intro'            => __( 'SEO Intro', 'apk-directory-pro' ),
		);

		foreach ( $sections as $key => $label ) {
			$wp_customize->add_setting( 'adp_homepage_section_' . $key, array(
				'default'           => true,
				'sanitize_callback' => array( self::class, 'sanitize_bool' ),
			) );
			$wp_customize->add_control( 'adp_homepage_section_' . $key, array(
				'label'   => sprintf(
					/* translators: %s: section name */
					__( 'Enable %s', 'apk-directory-pro' ),
					$label
				),
				'section' => 'adp_homepage',
				'type'    => 'checkbox',
			) );

			if ( $key !== 'hero_search' && $key !== 'seo_intro' && $key !== 'featured_categories' ) {
				$wp_customize->add_setting( 'adp_homepage_count_' . $key, array(
					'default'           => 8,
					'sanitize_callback' => 'absint',
				) );
				$wp_customize->add_control( 'adp_homepage_count_' . $key, array(
					'label'   => sprintf(
						/* translators: %s: section name */
						__( '%s — item count', 'apk-directory-pro' ),
						$label
					),
					'section' => 'adp_homepage',
					'type'    => 'number',
					'input_attrs' => array( 'min' => 1, 'max' => 24 ),
				) );
			}
		}

		$wp_customize->add_setting( 'adp_homepage_sections_order', array(
			'default'           => 'hero-search,featured-categories,trending,latest-updates,latest-games,editors-choice,popular,latest-blog,seo-intro',
			'sanitize_callback' => array( self::class, 'sanitize_sections_order' ),
		) );
		$wp_customize->add_control( 'adp_homepage_sections_order', array(
			'label'       => __( 'Section order (comma-separated slugs)', 'apk-directory-pro' ),
			'description' => __( 'Slugs: hero-search, featured-categories, trending, latest-updates, latest-games, editors-choice, popular, latest-blog, seo-intro', 'apk-directory-pro' ),
			'section'     => 'adp_homepage',
			'type'        => 'textarea',
		) );

		$wp_customize->add_setting( 'adp_seo_intro_content', array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		) );
		$wp_customize->add_control( 'adp_seo_intro_content', array(
			'label'   => __( 'SEO intro content', 'apk-directory-pro' ),
			'section' => 'adp_homepage',
			'type'    => 'textarea',
		) );
	}

	/**
	 * App page settings.
	 */
	private static function register_app_page( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_app_page', array(
			'title'    => __( 'App Page', 'apk-directory-pro' ),
			'priority' => 50,
		) );

		$wp_customize->add_setting( 'adp_show_safety_notice', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_show_safety_notice', array(
			'label'   => __( 'Show safety notice', 'apk-directory-pro' ),
			'section' => 'adp_app_page',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'adp_show_install_guidance', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_show_install_guidance', array(
			'label'   => __( 'Show install guidance', 'apk-directory-pro' ),
			'section' => 'adp_app_page',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'adp_sticky_download_bar', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_sticky_download_bar', array(
			'label'   => __( 'Sticky mobile download bar', 'apk-directory-pro' ),
			'section' => 'adp_app_page',
			'type'    => 'checkbox',
		) );
	}

	/**
	 * Archive settings.
	 */
	private static function register_archive( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_archive', array(
			'title'    => __( 'Archive', 'apk-directory-pro' ),
			'priority' => 60,
		) );

		$wp_customize->add_setting( 'adp_archive_per_page', array(
			'default'           => 24,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( 'adp_archive_per_page', array(
			'label'   => __( 'Apps per page', 'apk-directory-pro' ),
			'section' => 'adp_archive',
			'type'    => 'number',
			'input_attrs' => array( 'min' => 6, 'max' => 48 ),
		) );

		$wp_customize->add_setting( 'adp_archive_default_view', array(
			'default'           => 'list',
			'sanitize_callback' => array( self::class, 'sanitize_view' ),
		) );
		$wp_customize->add_control( 'adp_archive_default_view', array(
			'label'   => __( 'Default view', 'apk-directory-pro' ),
			'section' => 'adp_archive',
			'type'    => 'select',
			'choices' => array(
				'list' => __( 'List', 'apk-directory-pro' ),
				'grid' => __( 'Grid', 'apk-directory-pro' ),
			),
		) );

		$wp_customize->add_setting( 'adp_archive_load_more', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_archive_load_more', array(
			'label'   => __( 'Enable load more button', 'apk-directory-pro' ),
			'section' => 'adp_archive',
			'type'    => 'checkbox',
		) );
	}

	/**
	 * Footer settings.
	 */
	private static function register_footer( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_footer', array(
			'title'    => __( 'Footer', 'apk-directory-pro' ),
			'priority' => 70,
		) );

		$wp_customize->add_setting( 'adp_footer_copyright', array(
			'default'           => sprintf(
				/* translators: %s: current year */
				__( '© %s APK Directory Pro. All rights reserved.', 'apk-directory-pro' ),
				gmdate( 'Y' )
			),
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'adp_footer_copyright', array(
			'label'   => __( 'Copyright text', 'apk-directory-pro' ),
			'section' => 'adp_footer',
			'type'    => 'text',
		) );
	}

	/**
	 * Ad slots — sanitized by capability on output.
	 */
	private static function register_ads( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_ads', array(
			'title'       => __( 'Ad Slots', 'apk-directory-pro' ),
			'description' => __( 'Ad code is output only for users with unfiltered_html capability.', 'apk-directory-pro' ),
			'priority'    => 80,
		) );

		$slots = array(
			'header'       => __( 'Header banner', 'apk-directory-pro' ),
			'archive_top'  => __( 'Archive top', 'apk-directory-pro' ),
			'app_sidebar'  => __( 'App sidebar', 'apk-directory-pro' ),
			'app_content'  => __( 'App content mid', 'apk-directory-pro' ),
			'footer'       => __( 'Footer', 'apk-directory-pro' ),
		);

		foreach ( $slots as $key => $label ) {
			$wp_customize->add_setting( 'adp_ad_' . $key, array(
				'default'           => '',
				'sanitize_callback' => array( self::class, 'sanitize_ad' ),
			) );
			$wp_customize->add_control( 'adp_ad_' . $key, array(
				'label'   => $label,
				'section' => 'adp_ads',
				'type'    => 'textarea',
			) );
		}
	}

	/**
	 * Performance settings.
	 */
	private static function register_performance( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_performance', array(
			'title'    => __( 'Performance', 'apk-directory-pro' ),
			'priority' => 90,
		) );

		$wp_customize->add_setting( 'adp_lazy_load_images', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_lazy_load_images', array(
			'label'   => __( 'Lazy load images', 'apk-directory-pro' ),
			'section' => 'adp_performance',
			'type'    => 'checkbox',
		) );
	}

	/**
	 * Integration settings.
	 */
	private static function register_integrations( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_integrations', array(
			'title'    => __( 'Integrations', 'apk-directory-pro' ),
			'priority' => 100,
		) );

		$wp_customize->add_setting( 'adp_defer_seo_to_plugin', array(
			'default'           => true,
			'sanitize_callback' => array( self::class, 'sanitize_bool' ),
		) );
		$wp_customize->add_control( 'adp_defer_seo_to_plugin', array(
			'label'   => __( 'Defer meta tags to SEO plugin', 'apk-directory-pro' ),
			'section' => 'adp_integrations',
			'type'    => 'checkbox',
		) );
	}

	/**
	 * Sanitize boolean.
	 *
	 * @param mixed $value Input value.
	 */
	public static function sanitize_bool( $value ): bool {
		return (bool) $value;
	}

	/**
	 * Sanitize view option.
	 *
	 * @param mixed $value Input value.
	 */
	public static function sanitize_view( $value ): string {
		return in_array( $value, array( 'list', 'grid' ), true ) ? $value : 'list';
	}

	/**
	 * Sanitize sections order.
	 *
	 * @param mixed $value Input value.
	 */
	public static function sanitize_sections_order( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$allowed = array(
			'hero-search', 'featured-categories', 'trending', 'latest-updates',
			'latest-games', 'editors-choice', 'popular', 'latest-blog', 'seo-intro',
		);
		$parts   = array_map( 'trim', explode( ',', $value ) );
		$parts   = array_filter( $parts, fn( $s ) => in_array( $s, $allowed, true ) );
		return implode( ',', $parts );
	}

	/**
	 * Sanitize ad slot content.
	 *
	 * @param mixed $value Input value.
	 */
	public static function sanitize_ad( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $value;
		}
		return wp_kses_post( $value );
	}

	/**
	 * Enqueue preview scripts.
	 */
	public static function preview_scripts(): void {
		wp_enqueue_script(
			'adp-customizer-preview',
			ADP_THEME_URI . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			Assets::version( 'assets/js/customizer-preview.js' ),
			true
		);
	}
}
