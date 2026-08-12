<?php

namespace APD\Core\Support;

/**
 * Resolves the visitor address used to key rate limits.
 *
 * Forwarded headers are only read when REMOTE_ADDR matches a configured trusted
 * proxy: trusting them unconditionally lets any visitor spoof an address and
 * evade a limit, while ignoring them behind a CDN collapses every visitor
 * sharing an edge node — often a whole country — into one rate-limit bucket.
 */
final class ClientIp {

	/**
	 * Checked in order; the first header that yields a usable address wins.
	 */
	private const FORWARDED_HEADERS = array(
		'HTTP_CF_CONNECTING_IP',
		'HTTP_TRUE_CLIENT_IP',
		'HTTP_X_REAL_IP',
		'HTTP_X_FORWARDED_FOR',
	);

	/**
	 * Trust any proxy in front of the site. Only safe when the origin refuses
	 * traffic that does not arrive through that proxy.
	 */
	private const WILDCARD = '*';

	public static function get(): string {
		$remote = self::normalize( self::server_value( 'REMOTE_ADDR' ) );

		$trusted = self::trusted_proxies();
		if ( array() === $trusted ) {
			return $remote;
		}

		$wildcard = in_array( self::WILDCARD, $trusted, true );
		if ( ! $wildcard && ( '' === $remote || ! self::matches_any( $remote, $trusted ) ) ) {
			return $remote;
		}

		foreach ( self::FORWARDED_HEADERS as $header ) {
			$candidate = self::client_from_header( $header, $trusted, $wildcard );
			if ( '' !== $candidate ) {
				return $candidate;
			}
		}

		return $remote;
	}

	/**
	 * Trusted proxy addresses or CIDR ranges.
	 *
	 * Configure with the ADP_TRUSTED_PROXIES constant (array or comma/space
	 * separated string), the adp_trusted_proxies option, or the matching
	 * filter. Cloudflare and most CDNs publish the ranges to list here.
	 *
	 * @return array<int, string>
	 */
	public static function trusted_proxies(): array {
		$configured = defined( 'ADP_TRUSTED_PROXIES' )
			? constant( 'ADP_TRUSTED_PROXIES' )
			: get_option( 'adp_trusted_proxies', '' );

		/**
		 * Filters the trusted proxy list.
		 *
		 * @param mixed $configured Array, or comma/space separated string.
		 */
		return self::to_list( apply_filters( 'adp_trusted_proxies', $configured ) );
	}

	/**
	 * @param mixed $value Raw configuration value.
	 * @return array<int, string>
	 */
	private static function to_list( $value ): array {
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\s,]+/', $value );
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		$list = array();
		foreach ( $value as $entry ) {
			if ( ! is_string( $entry ) && ! is_numeric( $entry ) ) {
				continue;
			}
			$entry = trim( (string) $entry );
			if ( '' !== $entry ) {
				$list[] = $entry;
			}
		}

		return array_values( array_unique( $list ) );
	}

	/**
	 * @param string             $header   Superglobal key to read.
	 * @param array<int, string> $trusted  Trusted proxies.
	 * @param bool               $wildcard Whether any proxy is trusted.
	 */
	private static function client_from_header( string $header, array $trusted, bool $wildcard ): string {
		$value = self::server_value( $header );
		if ( '' === $value ) {
			return '';
		}

		$chain = array_map( array( self::class, 'normalize' ), explode( ',', $value ) );
		$chain = array_values( array_filter( $chain ) );

		// Nearest hop last, so walk backwards to reach the client the trusted
		// proxies vouch for. With wildcard trust there is nothing to skip and
		// the leftmost entry is the only claim of the original client.
		if ( ! $wildcard ) {
			$chain = array_reverse( $chain );
		}

		$fallback = '';
		foreach ( $chain as $ip ) {
			if ( ! $wildcard && self::matches_any( $ip, $trusted ) ) {
				continue;
			}
			if ( self::is_public( $ip ) ) {
				return $ip;
			}
			if ( '' === $fallback ) {
				$fallback = $ip;
			}
		}

		return $fallback;
	}

	/**
	 * @param string             $ip     Address to test.
	 * @param array<int, string> $ranges Addresses or CIDR ranges.
	 */
	private static function matches_any( string $ip, array $ranges ): bool {
		if ( '' === $ip ) {
			return false;
		}

		foreach ( $ranges as $range ) {
			if ( self::WILDCARD === $range ) {
				return true;
			}
			if ( self::matches( $ip, $range ) ) {
				return true;
			}
		}

		return false;
	}

	private static function matches( string $ip, string $range ): bool {
		if ( ! str_contains( $range, '/' ) ) {
			return self::normalize( $range ) === $ip;
		}

		[ $subnet, $bits ] = explode( '/', $range, 2 );

		$subnet = self::normalize( $subnet );
		if ( '' === $subnet || '' === trim( $bits ) || ! ctype_digit( trim( $bits ) ) ) {
			return false;
		}

		$ip_bin     = inet_pton( $ip );
		$subnet_bin = inet_pton( $subnet );
		if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
			return false;
		}

		$bits = (int) trim( $bits );
		if ( $bits > strlen( $ip_bin ) * 8 ) {
			return false;
		}

		$whole_bytes = intdiv( $bits, 8 );
		if ( $whole_bytes > 0 && 0 !== strncmp( $ip_bin, $subnet_bin, $whole_bytes ) ) {
			return false;
		}

		$remaining_bits = $bits % 8;
		if ( 0 === $remaining_bits ) {
			return true;
		}

		$mask = ~( ( 1 << ( 8 - $remaining_bits ) ) - 1 ) & 0xFF;

		return ( ord( $ip_bin[ $whole_bytes ] ) & $mask ) === ( ord( $subnet_bin[ $whole_bytes ] ) & $mask );
	}

	private static function is_public( string $ip ): bool {
		return false !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	}

	/**
	 * Strips the port some proxies append and rejects anything unparseable.
	 *
	 * @param string $value Raw address, optionally with a port.
	 */
	private static function normalize( string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		if ( str_starts_with( $value, '[' ) ) {
			$close = strpos( $value, ']' );
			if ( false !== $close ) {
				$value = substr( $value, 1, $close - 1 );
			}
		} elseif ( 1 === substr_count( $value, ':' ) ) {
			$host  = strstr( $value, ':', true );
			$value = false === $host ? $value : $host;
		}

		return false === filter_var( $value, FILTER_VALIDATE_IP ) ? '' : $value;
	}

	private static function server_value( string $key ): string {
		if ( ! isset( $_SERVER[ $key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	}
}
