<?php

namespace APD\Core\Content;

final class Meta {

	private const META_PREFIX = '_adp_';

	public function register(): void {
		$fields = $this->get_field_definitions();
		foreach ( $fields as $key => $config ) {
			register_post_meta(
				AppPostType::POST_TYPE,
				self::META_PREFIX . $key,
				array_merge(
					array(
						'single'        => true,
						'type'          => $config['type'],
						'show_in_rest'  => $config['show_in_rest'] ?? true,
						'default'       => $config['default'] ?? ( $config['type'] === 'boolean' ? false : ( $config['type'] === 'array' ? array() : '' ) ),
						'auth_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					),
					isset( $config['sanitize_callback'] ) ? array( 'sanitize_callback' => $config['sanitize_callback'] ) : array()
				)
			);
		}
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function get_field_definitions(): array {
		return array(
			'short_description'   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'package_name'        => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_package_name' ),
			),
			'current_version'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'version_code'        => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'file_size_bytes'     => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'android_requirement' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'architectures'       => array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_string_array' ),
			),
			'dpi'                 => array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_string_array' ),
			),
			'release_date'        => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_date' ),
			),
			'updated_date'        => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_date' ),
			),
			'price_type'          => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_price_type' ),
			),
			'price_amount'        => array(
				'type'              => 'number',
				'sanitize_callback' => array( $this, 'sanitize_float' ),
			),
			'price_currency'      => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'official_url'        => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' ),
			),
			'store_url'           => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' ),
			),
			'support_url'         => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' ),
			),
			'privacy_url'         => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' ),
			),
			'license'             => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'content_rating'      => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'status_badge'        => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_status_badge' ),
			),
			'verified'            => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			),
			'editor_rating'       => array(
				'type'              => 'number',
				'sanitize_callback' => array( $this, 'sanitize_rating' ),
			),
			'download_count'      => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'view_count'          => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'screenshot_ids'      => array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_int_array' ),
			),
			'video_url'           => array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' ),
			),
			'whats_new'           => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'mod_features'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'disclaimer_note'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'disable_toc'         => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			),
		);
	}

	public function sanitize_package_name( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );
		if ( $value && ! preg_match( '/^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)+$/', $value ) ) {
			return '';
		}
		return $value;
	}

	public function sanitize_string_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
	}

	public function sanitize_int_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'absint', $value ) ) );
	}

	public function sanitize_date( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );
		if ( $value && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return '';
		}
		return $value;
	}

	public function sanitize_price_type( mixed $value ): string {
		$allowed = array( 'free', 'paid', 'freemium' );
		$value   = sanitize_text_field( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : 'free';
	}

	public function sanitize_status_badge( mixed $value ): string {
		$allowed = array( 'none', 'new', 'updated', 'mod' );
		$value   = sanitize_text_field( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : 'none';
	}

	public function sanitize_float( mixed $value ): float {
		return (float) $value;
	}

	public function sanitize_rating( mixed $value ): float {
		$value = (float) $value;
		return max( 0, min( 5, $value ) );
	}

	public function sanitize_url( mixed $value ): string {
		$url = esc_url_raw( (string) $value );
		if ( $url && preg_match( '/^(javascript|data):/i', $url ) ) {
			return '';
		}
		return $url;
	}

	public static function get( int $post_id, string $key, mixed $default = null ): mixed {
		$value = get_post_meta( $post_id, '_adp_' . $key, true );
		return ( '' === $value && null !== $default ) ? $default : $value;
	}
}
