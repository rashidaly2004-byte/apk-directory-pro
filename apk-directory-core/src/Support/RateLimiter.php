<?php
/**
 * Transient-based rate limiter.
 *
 * @package Adp\Core\Support
 */

namespace Adp\Core\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Rate limiter helper.
 */
class RateLimiter {

	/**
	 * Check whether an action is allowed and increment counter.
	 *
	 * @param string $key       Unique action key (will be hashed).
	 * @param int    $max       Maximum attempts allowed.
	 * @param int    $window    Time window in seconds.
	 * @return bool True if allowed, false if rate limited.
	 */
	public static function attempt( string $key, int $max, int $window ): bool {
		$transient_key = 'adp_rl_' . md5( $key );
		$data          = get_transient( $transient_key );

		if ( ! is_array( $data ) ) {
			$data = array(
				'count' => 0,
				'start' => time(),
			);
		}

		$elapsed = time() - (int) $data['start'];
		if ( $elapsed >= $window ) {
			$data = array(
				'count' => 0,
				'start' => time(),
			);
		}

		if ( (int) $data['count'] >= $max ) {
			return false;
		}

		++$data['count'];
		$remaining = max( 1, $window - ( time() - (int) $data['start'] ) );
		set_transient( $transient_key, $data, $remaining );

		return true;
	}

	/**
	 * Get client identifier for rate limiting.
	 *
	 * @return string
	 */
	public static function client_id(): string {
		$ip = '';
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) );
		}
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			return 'user_' . $user_id;
		}
		return 'ip_' . md5( $ip );
	}

	/**
	 * Build rate limit key for an action.
	 *
	 * @param string $action Action name.
	 * @param string $scope  Optional scope (e.g. app ID).
	 * @return string
	 */
	public static function key( string $action, string $scope = '' ): string {
		return $action . ':' . self::client_id() . ( '' !== $scope ? ':' . $scope : '' );
	}

	/**
	 * Remaining attempts for a key.
	 *
	 * @param string $key    Rate limit key.
	 * @param int    $max    Maximum attempts.
	 * @param int    $window Time window.
	 * @return int
	 */
	public static function remaining( string $key, int $max, int $window ): int {
		$transient_key = 'adp_rl_' . md5( $key );
		$data          = get_transient( $transient_key );

		if ( ! is_array( $data ) ) {
			return $max;
		}

		$elapsed = time() - (int) $data['start'];
		if ( $elapsed >= $window ) {
			return $max;
		}

		return max( 0, $max - (int) $data['count'] );
	}
}
