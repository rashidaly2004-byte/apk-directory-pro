<?php
/**
 * HMAC signed expiring download tokens.
 *
 * @package Adp\Core\Downloads
 */

namespace Adp\Core\Downloads;

defined( 'ABSPATH' ) || exit;

/**
 * Token signer and validator.
 */
class Signer {

	/**
	 * Create a signed download token.
	 *
	 * @param int $version_id Version ID.
	 * @param int $ttl        Time to live in seconds.
	 * @return string Signed token.
	 */
	public function create( int $version_id, int $ttl = 3600 ): string {
		$expires = $ttl > 0 ? time() + max( 60, $ttl ) : time() + $ttl;
		$payload = $version_id . ':' . $expires;
		$sig     = hash_hmac( 'sha256', $payload, $this->get_secret() );
		return base64_encode( $payload . ':' . $sig ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Validate token and return version ID.
	 *
	 * @param string $token Signed token.
	 * @return int|\WP_Error Version ID or error.
	 */
	public function validate( string $token ): int|\WP_Error {
		$decoded = base64_decode( $token, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $decoded ) {
			return new \WP_Error( 'adp_invalid_token', __( 'Invalid download token.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		$parts = explode( ':', $decoded );
		if ( count( $parts ) !== 3 ) {
			return new \WP_Error( 'adp_invalid_token', __( 'Invalid download token.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		list( $version_id, $expires, $sig ) = $parts;
		$version_id = (int) $version_id;
		$expires    = (int) $expires;

		if ( $version_id <= 0 ) {
			return new \WP_Error( 'adp_invalid_token', __( 'Invalid download token.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		if ( time() > $expires ) {
			return new \WP_Error( 'adp_token_expired', __( 'Download link has expired.', 'apk-directory-core' ), array( 'status' => 410 ) );
		}

		$expected = hash_hmac( 'sha256', $version_id . ':' . $expires, $this->get_secret() );
		if ( ! hash_equals( $expected, $sig ) ) {
			return new \WP_Error( 'adp_invalid_token', __( 'Invalid download token.', 'apk-directory-core' ), array( 'status' => 403 ) );
		}

		return $version_id;
	}

	/**
	 * Get interstitial URL for a version.
	 *
	 * @param int $version_id Version ID.
	 * @return string
	 */
	public function get_interstitial_url( int $version_id ): string {
		$settings = get_option( 'adp_core_settings', array() );
		$ttl      = (int) ( $settings['download_token_ttl'] ?? 3600 );
		$token    = $this->create( $version_id, $ttl );
		return home_url( user_trailingslashit( 'download/' . rawurlencode( $token ) ) );
	}

	/**
	 * Get HMAC secret.
	 *
	 * @return string
	 */
	private function get_secret(): string {
		if ( defined( 'ADP_DOWNLOAD_SECRET' ) && ADP_DOWNLOAD_SECRET ) {
			return (string) ADP_DOWNLOAD_SECRET;
		}
		$secret = get_option( 'adp_download_secret' );
		if ( ! is_string( $secret ) || strlen( $secret ) < 32 ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( 'adp_download_secret', $secret, false );
		}
		return $secret;
	}
}
