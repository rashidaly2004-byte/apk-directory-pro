<?php
/**
 * Theme setup and registration.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup class.
 */
final class Setup {

	/**
	 * Initialize hooks.
	 *
	 * Called from after_setup_theme — run setup immediately so title-tag
	 * and other theme supports register in time.
	 */
	public static function init(): void {
		self::setup();
		add_action( 'widgets_init', array( self::class, 'register_sidebars' ) );
		add_action( 'init', array( self::class, 'register_block_patterns' ) );
		add_filter( 'body_class', array( self::class, 'body_classes' ) );
		add_filter( 'excerpt_length', array( self::class, 'excerpt_length' ) );
		add_filter( 'excerpt_more', array( self::class, 'excerpt_more' ) );
		add_action( 'pre_get_posts', array( self::class, 'modify_archive_query' ) );
	}

	/**
	 * Theme supports and menus.
	 */
	public static function setup(): void {
		load_theme_textdomain( 'apk-directory-pro', ADP_THEME_DIR . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		) );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/tokens.css' );

		add_theme_support( 'custom-logo', array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		add_image_size( 'adp-app-icon', 176, 176, true );
		add_image_size( 'adp-app-icon-sm', 88, 88, true );
		add_image_size( 'adp-screenshot', 400, 800, false );
		add_image_size( 'adp-card', 360, 200, true );

		register_nav_menus( array(
			'primary'         => __( 'Primary Navigation', 'apk-directory-pro' ),
			'category-bar'    => __( 'Category Bar', 'apk-directory-pro' ),
			'mobile'          => __( 'Mobile Drawer', 'apk-directory-pro' ),
			'footer-company'  => __( 'Footer — Company', 'apk-directory-pro' ),
			'footer-browse'   => __( 'Footer — Browse', 'apk-directory-pro' ),
			'footer-legal'    => __( 'Footer — Legal', 'apk-directory-pro' ),
		) );
	}

	/**
	 * Register widget areas.
	 */
	public static function register_sidebars(): void {
		register_sidebar( array(
			'name'          => __( 'App Sidebar', 'apk-directory-pro' ),
			'id'            => 'app-sidebar',
			'description'   => __( 'Sidebar on single app pages.', 'apk-directory-pro' ),
			'before_widget' => '<section id="%1$s" class="adp-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="adp-widget__title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => __( 'Blog Sidebar', 'apk-directory-pro' ),
			'id'            => 'blog-sidebar',
			'description'   => __( 'Sidebar on blog posts and pages.', 'apk-directory-pro' ),
			'before_widget' => '<section id="%1$s" class="adp-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="adp-widget__title">',
			'after_title'   => '</h3>',
		) );

		register_sidebar( array(
			'name'          => __( 'Footer Optional', 'apk-directory-pro' ),
			'id'            => 'footer-optional',
			'description'   => __( 'Optional footer widget area.', 'apk-directory-pro' ),
			'before_widget' => '<div id="%1$s" class="adp-footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="adp-footer-widget__title">',
			'after_title'   => '</h4>',
		) );
	}

	/**
	 * Register block pattern category.
	 */
	public static function register_block_patterns(): void {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		register_block_pattern_category( 'adp-directory', array(
			'label' => __( 'APK Directory', 'apk-directory-pro' ),
		) );

		$patterns_dir = ADP_THEME_DIR . '/patterns';
		if ( ! is_dir( $patterns_dir ) ) {
			return;
		}

		foreach ( glob( $patterns_dir . '/*.php' ) as $pattern_file ) {
			$pattern = include $pattern_file;
			if ( is_array( $pattern ) && ! empty( $pattern['title'] ) ) {
				register_block_pattern(
					'adp/' . basename( $pattern_file, '.php' ),
					$pattern
				);
			}
		}
	}

	/**
	 * Add body classes.
	 *
	 * @param array<int, string> $classes Existing classes.
	 * @return array<int, string>
	 */
	public static function body_classes( array $classes ): array {
		if ( is_singular( 'adp_app' ) ) {
			$classes[] = 'adp-single-app';
		}

		if ( ! is_plugin_active_check() ) {
			$classes[] = 'adp-plugin-inactive';
		}

		if ( is_front_page() ) {
			$classes[] = 'adp-front-page';
		}

		return $classes;
	}

	/**
	 * Modify archive queries for sorting and filtering.
	 *
	 * @param \WP_Query $query Main query.
	 */
	public static function modify_archive_query( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_post_type_archive( 'adp_app' ) && ! $query->is_tax( array( 'adp_app_category', 'adp_developer', 'adp_platform', 'adp_tag' ) ) ) {
			return;
		}

		$args = get_archive_query_args();
		unset( $args['paged'] );

		foreach ( $args as $key => $value ) {
			$query->set( $key, $value );
		}
	}

	/**
	 * Custom excerpt length.
	 */
	public static function excerpt_length(): int {
		return 28;
	}

	/**
	 * Custom excerpt more.
	 */
	public static function excerpt_more(): string {
		return '&hellip;';
	}
}
