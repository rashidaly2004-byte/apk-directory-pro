<?php

namespace APD\Theme;

final class Setup {

	public static function init(): void {
		add_action( 'after_setup_theme', array( self::class, 'setup' ) );
		add_action( 'widgets_init', array( self::class, 'widgets' ) );
	}

	public static function setup(): void {
		load_theme_textdomain( 'apk-directory-pro', ADP_THEME_PATH . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo', array(
			'height'      => 48,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'align-wide' );

		set_post_thumbnail_size( 300, 300, true );
		add_image_size( 'adp-app-icon', 144, 144, true );
		add_image_size( 'adp-app-card', 88, 88, true );
		add_image_size( 'adp-screenshot', 800, 450, false );

		register_nav_menus( array(
			'primary'        => __( 'Primary Menu', 'apk-directory-pro' ),
			'category-bar'   => __( 'Category Bar', 'apk-directory-pro' ),
			'mobile'         => __( 'Mobile Menu', 'apk-directory-pro' ),
			'footer-company' => __( 'Footer Company', 'apk-directory-pro' ),
			'footer-browse'  => __( 'Footer Browse', 'apk-directory-pro' ),
			'footer-legal'   => __( 'Footer Legal', 'apk-directory-pro' ),
		) );
	}

	public static function widgets(): void {
		register_sidebar( array(
			'name'          => __( 'App Sidebar', 'apk-directory-pro' ),
			'id'            => 'app-sidebar',
			'before_widget' => '<div class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
		register_sidebar( array(
			'name'          => __( 'Blog Sidebar', 'apk-directory-pro' ),
			'id'            => 'blog-sidebar',
			'before_widget' => '<div class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
		register_sidebar( array(
			'name'          => __( 'Footer', 'apk-directory-pro' ),
			'id'            => 'footer-widgets',
			'before_widget' => '<div class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="footer-widget-title">',
			'after_title'   => '</h3>',
		) );
	}
}
