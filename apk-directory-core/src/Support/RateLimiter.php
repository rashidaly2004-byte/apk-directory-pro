<?php

namespace APD\Core\Support;

/**
 * Fixed-window request counter keyed by visitor address.
 *
 * The window is anchored to the first request in it rather than to the
 * transient TTL, so a burst of blocked requests cannot keep pushing the
 * expiry back and lock a visitor out indefinitely.
 */
final class RateLimiter {

	private const PREFIX = 'adp_rl_';

	/**
	 * Records a request and reports whether it stays within the limit.
	 *
	 * @param string $bucket Identifier for the limited action.
	 * @param int    $limit  Requests allowed per window; 0 or less disables limiting.
	 * @param int    $window Window length in seconds.
	 */
	public static function hit( string $bucket, int $limit, int $window ): bool {
		if ( $limit <= 0 ) {
			return true;
		}

		$window = max( 1, $window );
		$key    = self::key( $bucket );
		$now    = time();
		$state  = get_transient( $key );

		$start = is_array( $state ) && isset( $state['start'] ) ? (int) $state['start'] : 0;
		$count = is_array( $state ) && isset( $state['count'] ) ? (int) $state['count'] : 0;

		if ( $start <= 0 || $start > $now || ( $now - $start ) >= $window ) {
			$start = $now;
			$count = 0;
		}

		++$count;

		set_transient(
			$key,
			array(
				'start' => $start,
				'count' => $count,
			),
			max( 1, $window - ( $now - $start ) )
		);

		return $count <= $limit;
	}

	public static function key( string $bucket ): string {
		return self::PREFIX . $bucket . '_' . md5( ClientIp::get() );
	}
}
