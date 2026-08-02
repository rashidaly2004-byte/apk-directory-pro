<?php

namespace APD\Core\Tests;

use PHPUnit\Framework\TestCase;

class MetaSanitizationTest extends TestCase {

	public function test_package_name_valid(): void {
		$meta = new \APD\Core\Content\Meta();
		$this->assertSame( 'com.example.app', $meta->sanitize_package_name( 'com.example.app' ) );
	}

	public function test_package_name_invalid(): void {
		$meta = new \APD\Core\Content\Meta();
		$this->assertSame( '', $meta->sanitize_package_name( 'invalid' ) );
	}

	public function test_price_type_allowed(): void {
		$meta = new \APD\Core\Content\Meta();
		$this->assertSame( 'free', $meta->sanitize_price_type( 'free' ) );
		$this->assertSame( 'free', $meta->sanitize_price_type( 'invalid' ) );
	}

	public function test_url_blocks_javascript(): void {
		$meta = new \APD\Core\Content\Meta();
		$this->assertSame( '', $meta->sanitize_url( 'javascript:alert(1)' ) );
	}
}
