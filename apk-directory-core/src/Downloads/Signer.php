<?php

namespace APD\Core\Downloads;

final class Signer {

	private const TOKEN_TTL = 3600;

	public function create_token( int $version_id, int $app_id ): string {
		$payload = wp_json_encode(
			[
				'v'  => $version_id,
				'a'  => $app_id,
				't'  => time(),
				'exp' => time() + self::TOKEN_TTL,
			]
		);
		$signature = hash_hmac( 'sha256', $payload, $this->get_secret() );
		return base64_encode( $payload . '.' . $signature ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	public function validate_token( string $token ): ?array {
		$decoded = base64_decode( $token, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( ! $decoded || ! str_contains( $decoded, '.' ) ) {
			return null;
		}

		[ $payload, $signature ] = explode( '.', $decoded, 2 );
		$expected = hash_hmac( 'sha256', $payload, $this->get_secret() );

		if ( ! hash_equals( $expected, $signature ) ) {
			return null;
		}

		$data = json_decode( $payload, true );
		if ( ! is_array( $data ) || empty( $data['exp'] ) || time() > (int) $data['exp'] ) {
			return null;
		}

		return [
			'version_id' => (int) $data['v'],
			'app_id'     => (int) $data['a'],
		];
	}

	private function get_secret(): string {
		if ( defined( 'ADP_DOWNLOAD_SECRET' ) ) {
			return ADP_DOWNLOAD_SECRET;
		}
		$secret = get_option( 'adp_download_secret' );
		if ( ! $secret ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( 'adp_download_secret', $secret, false );
		}
		return $secret;
	}
}
