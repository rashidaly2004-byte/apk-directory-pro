<?php

namespace APD\Core\Tests;

use APD\Core\Migration\MigrationService;
use PHPUnit\Framework\TestCase;

class MigrationServiceTest extends TestCase {

	public function test_state_defaults(): void {
		if ( ! function_exists( 'delete_option' ) ) {
			$this->markTestSkipped( 'WordPress not loaded' );
		}
		$service = new MigrationService();
		$service->reset_state();
		$state = $service->get_state();
		$this->assertSame( 'idle', $state['status'] );
		$this->assertSame( 0, $state['processed'] );
	}

	public function test_map_price_logic(): void {
		$service = new MigrationService();
		$ref     = new \ReflectionClass( $service );
		$method  = $ref->getMethod( 'map_price' );
		$method->setAccessible( true );
		$this->assertSame( 'free', $method->invoke( $service, 'Free app' ) );
		$this->assertSame( 'paid', $method->invoke( $service, 'paid download' ) );
	}
}
