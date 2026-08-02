<?php
/**
 * RateLimiter unit tests.
 *
 * @package Adp\Core\Tests\Unit
 */

namespace Adp\Core\Tests\Unit;

use Adp\Core\Support\RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Test RateLimiter helper.
 */
class RateLimiterTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['adp_test_transients'] = array();
	}

	public function test_allows_up_to_max_attempts(): void {
		$key = 'test:action';
		$this->assertTrue( RateLimiter::attempt( $key, 3, 60 ) );
		$this->assertTrue( RateLimiter::attempt( $key, 3, 60 ) );
		$this->assertTrue( RateLimiter::attempt( $key, 3, 60 ) );
		$this->assertFalse( RateLimiter::attempt( $key, 3, 60 ) );
	}

	public function test_remaining_decreases(): void {
		$key = 'test:remaining';
		RateLimiter::attempt( $key, 5, 60 );
		RateLimiter::attempt( $key, 5, 60 );
		$this->assertSame( 3, RateLimiter::remaining( $key, 5, 60 ) );
	}

	public function test_different_keys_are_independent(): void {
		$this->assertTrue( RateLimiter::attempt( 'key:a', 1, 60 ) );
		$this->assertFalse( RateLimiter::attempt( 'key:a', 1, 60 ) );
		$this->assertTrue( RateLimiter::attempt( 'key:b', 1, 60 ) );
	}

	public function test_key_builder(): void {
		$key = RateLimiter::key( 'search', 'app-1' );
		$this->assertStringContainsString( 'search:', $key );
		$this->assertStringContainsString( 'app-1', $key );
	}
}
