<?php

namespace APD\Theme;

final class Customizer {

	public static function init(): void {
		add_action( 'customize_register', array( self::class, 'register' ) );
	}

	public static function register( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section( 'adp_branding', array(
			'title'    => __( 'APK Directory', 'apk-directory-pro' ),
			'priority' => 30,
		) );

		$wp_customize->add_setting( 'adp_primary_color', array(
			'default'           => '#18a957',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'adp_primary_color', array(
			'label'   => __( 'Primary color', 'apk-directory-pro' ),
			'section' => 'adp_branding',
		) ) );

		$wp_customize->add_setting( 'adp_sticky_header', array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		) );
		$wp_customize->add_control( 'adp_sticky_header', array(
			'label'   => __( 'Sticky header', 'apk-directory-pro' ),
			'section' => 'adp_branding',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'adp_show_category_bar', array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		) );
		$wp_customize->add_control( 'adp_show_category_bar', array(
			'label'   => __( 'Show category bar', 'apk-directory-pro' ),
			'section' => 'adp_branding',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'adp_footer_text', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'adp_footer_text', array(
			'label'   => __( 'Footer brand text', 'apk-directory-pro' ),
			'section' => 'adp_branding',
			'type'    => 'textarea',
		) );
	}
}
