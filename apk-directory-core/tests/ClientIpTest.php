<?php

namespace APD\Core\Tests;

use APD\Core\Support\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['adp_test_options'] = array();
		$GLOBALS['adp_test_filters'] = array();
		unset(
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_X_FORWARDED_FOR'],
			$_SERVER['HTTP_CF_CONNECTING_IP'],
			$_SERVER['HTTP_X_REAL_IP'],
			$_SERVER['HTTP_TRUE_CLIENT_IP']
		);
	}

	protected function tearDown(): void {
		$GLOBALS['adp_test_options'] = array();
		$GLOBALS['adp_test_filters'] = array();
		parent::tearDown();
	}

	private function trust( string $proxies ): void {
		$GLOBALS['adp_test_options']['adp_trusted_proxies'] = $proxies;
	}

	public function test_returns_remote_addr_by_default(): void {
		$_SERVER['REMOTE_ADDR'] = '203.0.113.10';
		$this->assertSame( '203.0.113.10', ClientIp::get() );
	}

	public function test_forwarded_header_is_ignored_without_trusted_proxies(): void {
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertSame(
			'203.0.113.10',
			ClientIp::get(),
			'An unconfigured site must not trust a header any visitor can set.'
		);
	}

	public function test_forwarded_header_is_ignored_from_untrusted_remote_addr(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '198.51.100.99';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertSame( '198.51.100.99', ClientIp::get() );
	}

	public function test_resolves_client_behind_trusted_proxy(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_visitors_behind_one_proxy_resolve_to_distinct_addresses(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR'] = '203.0.113.10';

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';
		$first                           = ClientIp::get();

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.8';
		$second                          = ClientIp::get();

		$this->assertNotSame( $first, $second );
	}

	public function test_skips_trusted_hops_in_forwarded_chain(): void {
		$this->trust( "203.0.113.10\n203.0.113.11" );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7, 203.0.113.11, 203.0.113.10';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_prefers_cloudflare_header(): void {
		$this->trust( '203.0.113.0/24' );
		$_SERVER['REMOTE_ADDR']           = '203.0.113.10';
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.7';
		$_SERVER['HTTP_X_FORWARDED_FOR']  = '198.51.100.200';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_matches_ipv4_cidr_range(): void {
		$this->trust( '203.0.113.0/24' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.200';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_rejects_address_outside_cidr_range(): void {
		$this->trust( '203.0.113.0/24' );
		$_SERVER['REMOTE_ADDR']          = '203.0.114.1';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';

		$this->assertSame( '203.0.114.1', ClientIp::get() );
	}

	public function test_matches_ipv6_cidr_range(): void {
		$this->trust( '2400:cb00::/32' );
		$_SERVER['REMOTE_ADDR']          = '2400:cb00:1234::1';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '2001:db8:abcd::5';

		$this->assertSame( '2001:db8:abcd::5', ClientIp::get() );
	}

	public function test_non_byte_aligned_prefix(): void {
		$this->trust( '203.0.113.0/25' );

		$_SERVER['REMOTE_ADDR']          = '203.0.113.100';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7';
		$this->assertSame( '198.51.100.7', ClientIp::get() );

		$_SERVER['REMOTE_ADDR'] = '203.0.113.200';
		$this->assertSame( '203.0.113.200', ClientIp::get() );
	}

	public function test_wildcard_trusts_any_proxy(): void {
		$this->trust( '*' );
		$_SERVER['REMOTE_ADDR']          = '10.0.0.5';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7, 10.0.0.5';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_strips_port_from_forwarded_value(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7:54321';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_strips_port_from_bracketed_ipv6_value(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '[2001:db8::1]:8443';

		$this->assertSame( '2001:db8::1', ClientIp::get() );
	}

	public function test_falls_back_when_forwarded_value_is_unusable(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip, unknown';

		$this->assertSame( '203.0.113.10', ClientIp::get() );
	}

	public function test_falls_back_to_private_hop_when_no_public_client(): void {
		$this->trust( '203.0.113.10' );
		$_SERVER['REMOTE_ADDR']          = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.8';

		$this->assertSame( '10.0.0.8', ClientIp::get() );
	}

	public function test_filter_can_supply_trusted_proxies(): void {
		$GLOBALS['adp_test_filters']['adp_trusted_proxies'] = static function () {
			return array( '203.0.113.10' );
		};
		$_SERVER['REMOTE_ADDR']                            = '203.0.113.10';
		$_SERVER['HTTP_X_FORWARDED_FOR']                   = '198.51.100.7';

		$this->assertSame( '198.51.100.7', ClientIp::get() );
	}

	public function test_missing_remote_addr_is_handled(): void {
		$this->assertSame( '', ClientIp::get() );
	}
}
