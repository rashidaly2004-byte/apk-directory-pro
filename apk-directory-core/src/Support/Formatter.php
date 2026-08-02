<?php
/**
 * Output formatting helpers.
 *
 * @package Adp\Core\Support
 */

namespace Adp\Core\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Formatter utility class.
 */
class Formatter {

	/**
	 * Format bytes to human-readable size.
	 *
	 * @param int $bytes File size in bytes.
	 * @return string
	 */
	public static function bytes( int $bytes ): string {
		if ( $bytes <= 0 ) {
			return '0 B';
		}
		return size_format( $bytes, 2 );
	}

	/**
	 * Format date for display.
	 *
	 * @param string $date   Date string (Y-m-d or datetime).
	 * @param string $format PHP date format.
	 * @return string
	 */
	public static function date( string $date, string $format = '' ): string {
		if ( '' === $date ) {
			return '';
		}
		$timestamp = strtotime( $date );
		if ( false === $timestamp ) {
			return '';
		}
		if ( '' === $format ) {
			$format = get_option( 'date_format', 'F j, Y' );
		}
		return wp_date( $format, $timestamp );
	}

	/**
	 * Format price with currency.
	 *
	 * @param float  $amount   Price amount.
	 * @param string $currency ISO 4217 code.
	 * @param string $type     Price type (free, paid, freemium).
	 * @return string
	 */
	public static function price( float $amount, string $currency, string $type = 'free' ): string {
		if ( 'free' === $type || ( 'freemium' === $type && $amount <= 0 ) ) {
			return __( 'Free', 'apk-directory-core' );
		}
		if ( $amount > 0 && '' !== $currency ) {
			return sprintf(
				/* translators: 1: currency code, 2: amount */
				__( '%1$s %2$s', 'apk-directory-core' ),
				esc_html( $currency ),
				number_format_i18n( $amount, 2 )
			);
		}
		return __( 'Free', 'apk-directory-core' );
	}

	/**
	 * Format version label.
	 *
	 * @param string   $version_name Version name.
	 * @param int|null $version_code Version code.
	 * @return string
	 */
	public static function version( string $version_name, ?int $version_code = null ): string {
		if ( '' === $version_name ) {
			return '';
		}
		if ( null !== $version_code && $version_code > 0 ) {
			return sprintf(
				/* translators: 1: version name, 2: version code */
				__( '%1$s (%2$d)', 'apk-directory-core' ),
				$version_name,
				$version_code
			);
		}
		return $version_name;
	}

	/**
	 * Escape and highlight search term in text.
	 *
	 * @param string $text    Source text.
	 * @param string $query   Search query.
	 * @return string Safe HTML with mark tags.
	 */
	public static function highlight( string $text, string $query ): string {
		$escaped = esc_html( $text );
		if ( '' === $query ) {
			return $escaped;
		}
		$pattern = '/' . preg_quote( $query, '/' ) . '/iu';
		return (string) preg_replace(
			$pattern,
			'<mark>$0</mark>',
			$escaped
		);
	}

	/**
	 * Format architectures list.
	 *
	 * @param array<int, string> $architectures Architecture identifiers.
	 * @return string
	 */
	public static function architectures( array $architectures ): string {
		if ( empty( $architectures ) ) {
			return '';
		}
		return esc_html( implode( ', ', $architectures ) );
	}
}
