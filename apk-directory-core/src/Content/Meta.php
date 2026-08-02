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
					[
						'single'       => true,
						'type'         => $config['type'],
						'show_in_rest' => $config['show_in_rest'] ?? true,
						'default'      => $config['default'] ?? ( $config['type'] === 'boolean' ? false : ( $config['type'] === 'array' ? [] : '' ) ),
					],
					isset( $config['sanitize_callback'] ) ? [ 'sanitize_callback' => $config['sanitize_callback'] ] : [],
					isset( $config['auth_callback'] ) ? [ 'auth_callback' => $config['auth_callback'] ] : []
				)
			);
		}
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function get_field_definitions(): array {
		return [
			'short_description'   => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'package_name'          => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_package_name' ] ],
			'current_version'       => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'version_code'          => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'file_size_bytes'       => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'android_requirement'   => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'architectures'         => [ 'type' => 'array', 'sanitize_callback' => [ $this, 'sanitize_string_array' ] ],
			'dpi'                   => [ 'type' => 'array', 'sanitize_callback' => [ $this, 'sanitize_string_array' ] ],
			'release_date'          => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_date' ] ],
			'updated_date'          => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_date' ] ],
			'price_type'            => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_price_type' ] ],
			'price_amount'          => [ 'type' => 'number', 'sanitize_callback' => [ $this, 'sanitize_float' ] ],
			'price_currency'        => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'official_url'          => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_url' ] ],
			'store_url'             => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_url' ] ],
			'support_url'           => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_url' ] ],
			'privacy_url'           => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_url' ] ],
			'license'               => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'content_rating'        => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'status_badge'          => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_status_badge' ] ],
			'verified'              => [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ],
			'editor_rating'         => [ 'type' => 'number', 'sanitize_callback' => [ $this, 'sanitize_rating' ] ],
			'download_count'        => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'view_count'            => [ 'type' => 'integer', 'sanitize_callback' => 'absint' ],
			'screenshot_ids'        => [ 'type' => 'array', 'sanitize_callback' => [ $this, 'sanitize_int_array' ] ],
			'video_url'             => [ 'type' => 'string', 'sanitize_callback' => [ $this, 'sanitize_url' ] ],
			'whats_new'             => [ 'type' => 'string', 'sanitize_callback' => 'wp_kses_post' ],
			'mod_features'          => [ 'type' => 'string', 'sanitize_callback' => 'wp_kses_post' ],
			'disclaimer_note'       => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
			'disable_toc'           => [ 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean' ],
		];
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
			return [];
		}
		return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
	}

	public function sanitize_int_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
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
		$allowed = [ 'free', 'paid', 'freemium' ];
		$value   = sanitize_text_field( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : 'free';
	}

	public function sanitize_status_badge( mixed $value ): string {
		$allowed = [ 'none', 'new', 'updated', 'mod' ];
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
