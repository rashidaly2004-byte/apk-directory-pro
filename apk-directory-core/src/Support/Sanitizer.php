<?php
/**
 * Input sanitization helpers.
 *
 * @package Adp\Core\Support
 */

namespace Adp\Core\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Sanitizer utility class.
 */
class Sanitizer {

	/**
	 * Sanitize package name (Android-style).
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function package_name( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );
		$value = strtolower( trim( $value ) );
		if ( '' === $value ) {
			return '';
		}
		if ( preg_match( '/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/', $value ) ) {
			return $value;
		}
		return sanitize_key( str_replace( array( ' ', '/' ), '.', $value ) );
	}

	/**
	 * Sanitize URL with scheme allowlist.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function url( mixed $value ): string {
		$url = esc_url_raw( (string) $value, array( 'http', 'https' ) );
		if ( '' === $url ) {
			return '';
		}
		$parsed = wp_parse_url( $url );
		if ( empty( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], array( 'http', 'https' ), true ) ) {
			return '';
		}
		if ( ! empty( $parsed['user'] ) || ! empty( $parsed['pass'] ) ) {
			return '';
		}
		return $url;
	}

	/**
	 * Sanitize date string (Y-m-d).
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function date( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );
		if ( '' === $value ) {
			return '';
		}
		$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d', $value );
		if ( false === $dt || $dt->format( 'Y-m-d' ) !== $value ) {
			return '';
		}
		return $value;
	}

	/**
	 * Sanitize enum value against allowed list.
	 *
	 * @param mixed              $value   Raw value.
	 * @param array<int, string> $allowed Allowed values.
	 * @param string             $default Default when invalid.
	 * @return string
	 */
	public static function enum( mixed $value, array $allowed, string $default = '' ): string {
		$value = sanitize_key( (string) $value );
		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	/**
	 * Sanitize array of strings.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int, string>
	 */
	public static function string_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values(
			array_filter(
				array_map( 'sanitize_text_field', $value ),
				static fn( string $item ): bool => '' !== $item
			)
		);
	}

	/**
	 * Sanitize array of positive integers (attachment IDs).
	 *
	 * @param mixed $value Raw value.
	 * @return array<int, int>
	 */
	public static function id_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$ids = array_map( 'absint', $value );
		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
	}

	/**
	 * Sanitize HTML for changelog/mod fields.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function html( mixed $value ): string {
		return wp_kses_post( (string) $value );
	}

	/**
	 * Sanitize positive integer.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function positive_int( mixed $value ): int {
		return max( 0, (int) $value );
	}

	/**
	 * Sanitize float amount.
	 *
	 * @param mixed $value Raw value.
	 * @return float
	 */
	public static function float( mixed $value ): float {
		return (float) $value;
	}

	/**
	 * Sanitize boolean.
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	public static function bool( mixed $value ): bool {
		return (bool) filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Sanitize ISO 4217 currency code.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function currency( mixed $value ): string {
		$value = strtoupper( sanitize_text_field( (string) $value ) );
		if ( preg_match( '/^[A-Z]{3}$/', $value ) ) {
			return $value;
		}
		return '';
	}

	/**
	 * Sanitize rating 1-5.
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function rating( mixed $value ): int {
		$rating = (int) $value;
		return max( 1, min( 5, $rating ) );
	}

	/**
	 * Sanitize hex color.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function hex_color( mixed $value ): string {
		$value = sanitize_text_field( (string) $value );
		if ( preg_match( '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value ) ) {
			return $value;
		}
		return '';
	}
}
