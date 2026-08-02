<?php
/**
 * AppynMigrator dry-run unit tests.
 *
 * @package Adp\Core\Tests\Unit
 */

namespace Adp\Core\Tests\Unit;

use Adp\Core\Migration\AppynMigrator;
use PHPUnit\Framework\TestCase;

/**
 * Test AppynMigrator dry-run and mapping.
 */
class AppynMigratorTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['adp_test_meta'] = array();
	}

	public function test_map_post_skips_without_meta(): void {
		$post = new \WP_Post();
		$post->ID         = 100;
		$post->post_title = 'Test App';

		$mapping = AppynMigrator::map_post( $post, true );
		$this->assertTrue( $mapping['skip'] );
	}

	public function test_map_post_with_datos_informacion(): void {
		$GLOBALS['adp_test_meta'] = array(
			100 => array(
				'datos_informacion' => array(
					'descripcion'         => 'com.demo.app',
					'version'             => '3.2.1',
					'tamano'              => '15 MB',
					'released_on'         => '2020-01-01',
					'fecha_actualizacion' => '2024-06-15',
					'requerimientos'      => 'Android 8.0+',
					'consiguelo'          => 'https://play.google.com/store/apps/details?id=com.demo',
					'descargas'           => 5000,
					'novedades'           => '<p>Bug fixes</p>',
				),
			),
		);

		$post = new \WP_Post();
		$post->ID         = 100;
		$post->post_title = 'Demo App';

		$mapping = AppynMigrator::map_post( $post, true );
		$this->assertFalse( $mapping['skip'] );
		$this->assertSame( 'com.demo.app', $mapping['package'] );
		$this->assertSame( '3.2.1', $mapping['meta']['_adp_current_version'] );
		$this->assertSame( 15728640, $mapping['meta']['_adp_file_size_bytes'] );
		$this->assertSame( '2020-01-01', $mapping['meta']['_adp_release_date'] );
	}

	public function test_dry_run_returns_stats(): void {
		$result = AppynMigrator::dry_run();
		$this->assertSame( 'dry-run', $result['mode'] );
		$this->assertArrayHasKey( 'stats', $result );
		$this->assertArrayHasKey( 'log', $result );
		$this->assertArrayHasKey( 'found', $result['stats'] );
	}
}
