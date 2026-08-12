<?php

namespace APD\Core\Tests;

use APD\Core\Support\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['adp_test_options']    = array();
		$GLOBALS['adp_test_filters']    = array();
		$GLOBALS['adp_test_transients'] = array();
		$_SERVER['REMOTE_ADDR']         = '198.51.100.7';
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
	}

	protected function tearDown(): void {
		$GLOBALS['adp_test_options']    = array();
		$GLOBALS['adp_test_filters']    = array();
		$GLOBALS['adp_test_transients'] = array();
		parent::tearDown();
	}

	public function test_allows_requests_up_to_the_limit(): void {
		for ( $i = 0; $i < 3; $i++ ) {
			$this->assertTrue( RateLimiter::hit( 'search', 3, 60 ), "Request $i should be allowed" );
		}

		$this->assertFalse( RateLimiter::hit( 'search', 3, 60 ) );
	}

	public function test_buckets_are_independent(): void {
		RateLimiter::hit( 'search', 1, 60 );
		$this->assertFalse( RateLimiter::hit( 'search', 1, 60 ) );
		$this->assertTrue( RateLimiter::hit( 'report', 1, 60 ) );
	}

	public function test_addresses_are_counted_separately(): void {
		RateLimiter::hit( 'search', 1, 60 );
		$this->assertFalse( RateLimiter::hit( 'search', 1, 60 ) );

		$_SERVER['REMOTE_ADDR'] = '198.51.100.8';
		$this->assertTrue( RateLimiter::hit( 'search', 1, 60 ) );
	}

	public function test_visitors_sharing_a_proxy_are_counted_separately(): void {
		$GLOBALS['adp_test_options']['adp_trusted_proxies'] = '203.0.113.10';
		$_SERVER['REMOTE_ADDR']                             = '203.0.113.10';

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';
		$this->assertTrue( RateLimiter::hit( 'search', 1, 60 ) );
		$this->assertFalse( RateLimiter::hit( 'search', 1, 60 ) );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.8';
		$this->assertTrue(
			RateLimiter::hit( 'search', 1, 60 ),
			'A second visitor behind the same proxy must get their own allowance.'
		);
	}

	public function test_window_resets_after_it_elapses(): void {
		RateLimiter::hit( 'search', 1, 60 );
		$this->assertFalse( RateLimiter::hit( 'search', 1, 60 ) );

		$key   = RateLimiter::key( 'search' );
		$state = get_transient( $key );
		set_transient( $key, array( 'start' => $state['start'] - 61, 'count' => $state['count'] ), 60 );

		$this->assertTrue( RateLimiter::hit( 'search', 1, 60 ) );
	}

	public function test_blocked_requests_do_not_extend_the_window(): void {
		RateLimiter::hit( 'search', 1, 60 );
		$start = get_transient( RateLimiter::key( 'search' ) )['start'];

		for ( $i = 0; $i < 5; $i++ ) {
			RateLimiter::hit( 'search', 1, 60 );
		}

		$this->assertSame(
			$start,
			get_transient( RateLimiter::key( 'search' ) )['start'],
			'The window must stay anchored to its first request.'
		);
	}

	public function test_zero_limit_disables_limiting(): void {
		for ( $i = 0; $i < 50; $i++ ) {
			$this->assertTrue( RateLimiter::hit( 'search', 0, 60 ) );
		}

		$this->assertSame( array(), $GLOBALS['adp_test_transients'] );
	}

	public function test_corrupt_state_is_recovered(): void {
		set_transient( RateLimiter::key( 'search' ), 'garbage', 60 );

		$this->assertTrue( RateLimiter::hit( 'search', 1, 60 ) );
		$this->assertFalse( RateLimiter::hit( 'search', 1, 60 ) );
	}

	public function test_future_start_is_reset(): void {
		$key = RateLimiter::key( 'search' );
		set_transient( $key, array( 'start' => time() + 5000, 'count' => 99 ), 60 );

		$this->assertTrue( RateLimiter::hit( 'search', 1, 60 ) );
	}
}
