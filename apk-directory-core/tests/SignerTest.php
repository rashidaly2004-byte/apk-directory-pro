<?php

namespace APD\Core\Tests;

use APD\Core\Downloads\Signer;
use PHPUnit\Framework\TestCase;

class SignerTest extends TestCase {

	public function test_token_roundtrip(): void {
		if ( ! function_exists( 'wp_generate_password' ) ) {
			$this->markTestSkipped( 'WordPress not loaded' );
		}
		$signer = new Signer();
		$token  = $signer->create_token( 42, 7 );
		$data   = $signer->validate_token( $token );
		$this->assertNotNull( $data );
		$this->assertSame( 42, $data['version_id'] );
		$this->assertSame( 7, $data['app_id'] );
	}

	public function test_invalid_token(): void {
		$signer = new Signer();
		$this->assertNull( $signer->validate_token( 'invalid-token' ) );
	}
}
