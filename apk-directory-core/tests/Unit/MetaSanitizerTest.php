<?php
/**
 * Meta sanitizer unit tests.
 *
 * @package Adp\Core\Tests\Unit
 */

namespace Adp\Core\Tests\Unit;

use Adp\Core\Support\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Test Sanitizer helper methods used by MetaSchema.
 */
class MetaSanitizerTest extends TestCase {

	public function test_package_name_valid(): void {
		$this->assertSame( 'com.example.app', Sanitizer::package_name( 'com.example.app' ) );
	}

	public function test_package_name_sanitizes_invalid(): void {
		$result = Sanitizer::package_name( 'My App Name' );
		$this->assertNotEmpty( $result );
	}

	public function test_url_rejects_javascript(): void {
		$this->assertSame( '', Sanitizer::url( 'javascript:alert(1)' ) );
	}

	public function test_url_accepts_https(): void {
		$this->assertSame( 'https://example.com/path', Sanitizer::url( 'https://example.com/path' ) );
	}

	public function test_url_rejects_credentials(): void {
		$this->assertSame( '', Sanitizer::url( 'https://user:pass@example.com' ) );
	}

	public function test_date_valid_format(): void {
		$this->assertSame( '2024-01-15', Sanitizer::date( '2024-01-15' ) );
	}

	public function test_date_rejects_invalid(): void {
		$this->assertSame( '', Sanitizer::date( 'not-a-date' ) );
	}

	public function test_enum_returns_default(): void {
		$this->assertSame( 'free', Sanitizer::enum( 'invalid', array( 'free', 'paid' ), 'free' ) );
	}

	public function test_enum_accepts_valid(): void {
		$this->assertSame( 'paid', Sanitizer::enum( 'paid', array( 'free', 'paid' ), 'free' ) );
	}

	public function test_id_array_filters_invalid(): void {
		$this->assertSame( array( 1, 2, 3 ), Sanitizer::id_array( array( 1, 0, 2, '3', 'abc' ) ) );
	}

	public function test_string_array_sanitizes(): void {
		$this->assertSame( array( 'arm64-v8a', 'x86_64' ), Sanitizer::string_array( array( 'arm64-v8a', '', 'x86_64' ) ) );
	}

	public function test_rating_bounds(): void {
		$this->assertSame( 1, Sanitizer::rating( 0 ) );
		$this->assertSame( 5, Sanitizer::rating( 99 ) );
		$this->assertSame( 3, Sanitizer::rating( 3 ) );
	}

	public function test_currency_valid(): void {
		$this->assertSame( 'USD', Sanitizer::currency( 'usd' ) );
		$this->assertSame( '', Sanitizer::currency( 'INVALID' ) );
	}

	public function test_hex_color(): void {
		$this->assertSame( '#18a957', Sanitizer::hex_color( '#18a957' ) );
		$this->assertSame( '', Sanitizer::hex_color( 'red' ) );
	}
}
