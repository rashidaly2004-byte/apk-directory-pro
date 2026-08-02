<?php
/**
 * Version Service unit tests.
 *
 * @package Adp\Core\Tests\Unit
 */

namespace Adp\Core\Tests\Unit;

use Adp\Core\Versions\Repository;
use Adp\Core\Versions\Service;
use PHPUnit\Framework\TestCase;

/**
 * Test one-current-version enforcement.
 */
class VersionServiceTest extends TestCase {

	private InMemoryRepository $repository;
	private Service $service;

	protected function setUp(): void {
		parent::setUp();
		$this->repository = new InMemoryRepository();
		$this->service    = new Service( $this->repository );

		if ( ! function_exists( 'get_post_type' ) ) {
			// Stub for validation.
			eval( 'function get_post_type( $id ) { return $id > 0 ? "adp_app" : false; }' ); // phpcs:ignore
		}

		global $wpdb;
		$wpdb = new class() {
			public function query( string $query ): int {
				unset( $query );
				return 1;
			}
		};
	}

	public function test_only_one_current_version_per_app(): void {
		$id1 = $this->service->create(
			array(
				'app_id'          => 1,
				'version_name'    => '1.0.0',
				'file_size_bytes' => 1000,
				'download_type'   => 'external',
				'external_url'    => 'https://example.com/app.apk',
				'is_current'      => true,
			)
		);
		$this->assertIsInt( $id1 );

		$id2 = $this->service->create(
			array(
				'app_id'          => 1,
				'version_name'    => '2.0.0',
				'file_size_bytes' => 2000,
				'download_type'   => 'external',
				'external_url'    => 'https://example.com/app2.apk',
				'is_current'      => true,
			)
		);
		$this->assertIsInt( $id2 );

		$current = $this->repository->find_current( 1 );
		$this->assertNotNull( $current );
		$this->assertSame( $id2, $current['id'] );

		$v1 = $this->repository->find( $id1 );
		$this->assertFalse( $v1['is_current'] );
	}

	public function test_set_current_switches_flag(): void {
		$id1 = $this->service->create(
			array(
				'app_id'          => 2,
				'version_name'    => '1.0',
				'file_size_bytes' => 500,
				'download_type'   => 'external',
				'external_url'    => 'https://example.com/a.apk',
				'is_current'      => true,
			)
		);
		$id2 = $this->service->create(
			array(
				'app_id'          => 2,
				'version_name'    => '2.0',
				'file_size_bytes' => 600,
				'download_type'   => 'external',
				'external_url'    => 'https://example.com/b.apk',
			)
		);

		$result = $this->service->set_current( $id2 );
		$this->assertTrue( $result );

		$current = $this->repository->find_current( 2 );
		$this->assertSame( $id2, $current['id'] );

		$old = $this->repository->find( $id1 );
		$this->assertFalse( $old['is_current'] );
	}

	public function test_validation_rejects_missing_version_name(): void {
		$result = $this->service->validate(
			array(
				'app_id'        => 1,
				'version_name'  => '',
				'download_type' => 'external',
				'external_url'  => 'https://example.com/a.apk',
			)
		);
		$this->assertInstanceOf( \WP_Error::class, $result );
	}
}

/**
 * In-memory repository for unit tests.
 */
class InMemoryRepository extends Repository {

	/** @var array<int, array<string, mixed>> */
	private array $rows = array();
	private int $next_id = 1;

	public function find( int $id ): ?array {
		return $this->rows[ $id ] ?? null;
	}

	public function find_current( int $app_id ): ?array {
		foreach ( $this->rows as $row ) {
			if ( $row['app_id'] === $app_id && $row['is_current'] ) {
				return $row;
			}
		}
		return null;
	}

	public function list_by_app( int $app_id, int $limit = 50, int $offset = 0 ): array {
		unset( $limit, $offset );
		return array_values(
			array_filter( $this->rows, static fn( array $r ): bool => $r['app_id'] === $app_id )
		);
	}

	public function insert( array $data ): int {
		$id = $this->next_id++;
		$row = array_merge(
			array(
				'id'              => $id,
				'is_current'      => false,
				'download_count'  => 0,
				'architectures'   => array(),
				'dpi'             => array(),
				'file_type'       => 'apk',
				'virus_scan_status' => 'unknown',
			),
			$data
		);
		$row['id']         = $id;
		$row['app_id']     = (int) $row['app_id'];
		$row['is_current'] = (bool) ( $row['is_current'] ?? false );
		$this->rows[ $id ] = $row;
		return $id;
	}

	public function update( int $id, array $data ): bool {
		if ( ! isset( $this->rows[ $id ] ) ) {
			return false;
		}
		$this->rows[ $id ] = array_merge( $this->rows[ $id ], $data );
		return true;
	}

	public function delete( int $id ): bool {
		if ( ! isset( $this->rows[ $id ] ) ) {
			return false;
		}
		unset( $this->rows[ $id ] );
		return true;
	}

	public function clear_current( int $app_id ): void {
		foreach ( $this->rows as &$row ) {
			if ( $row['app_id'] === $app_id ) {
				$row['is_current'] = false;
			}
		}
	}

	public function set_current( int $id, int $app_id ): void {
		$this->clear_current( $app_id );
		if ( isset( $this->rows[ $id ] ) ) {
			$this->rows[ $id ]['is_current'] = true;
		}
	}
}
