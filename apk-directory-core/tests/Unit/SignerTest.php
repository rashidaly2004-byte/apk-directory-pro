<?php
/**
 * Signer unit tests.
 *
 * @package Adp\Core\Tests\Unit
 */

namespace Adp\Core\Tests\Unit;

use Adp\Core\Downloads\Signer;
use PHPUnit\Framework\TestCase;

/**
 * Test Signer token creation and validation.
 */
class SignerTest extends TestCase {

	private Signer $signer;

	protected function setUp(): void {
		parent::setUp();
		$this->signer = new Signer();
	}

	public function test_create_and_validate_token(): void {
		$token      = $this->signer->create( 42, 3600 );
		$version_id = $this->signer->validate( $token );

		$this->assertSame( 42, $version_id );
	}

	public function test_expired_token_returns_error(): void {
		$token = $this->signer->create( 1, -3600 );
		$result = $this->signer->validate( $token );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'adp_token_expired', $result->get_error_code() );
	}

	public function test_invalid_token_returns_error(): void {
		$result = $this->signer->validate( 'not-a-valid-token' );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'adp_invalid_token', $result->get_error_code() );
	}

	public function test_tampered_token_returns_error(): void {
		$token   = $this->signer->create( 5, 3600 );
		$decoded = base64_decode( $token, true );
		$parts   = explode( ':', (string) $decoded );
		$parts[0] = '999';
		$tampered = base64_encode( implode( ':', $parts ) );

		$result = $this->signer->validate( $tampered );
		$this->assertInstanceOf( \WP_Error::class, $result );
	}

	public function test_interstitial_url_contains_download_path(): void {
		$url = $this->signer->get_interstitial_url( 10 );
		$this->assertStringContainsString( '/download/', $url );
	}
}
