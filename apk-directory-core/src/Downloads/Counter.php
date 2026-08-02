<?php
/**
 * Privacy-aware download counter.
 *
 * @package Adp\Core\Downloads
 */

namespace Adp\Core\Downloads;

use Adp\Core\Content\Meta;
use Adp\Core\Versions\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Counts downloads once per privacy-preserving token.
 */
class Counter {

	private Repository $repository;

	/**
	 * Constructor.
	 *
	 * @param Repository|null $repository Optional repository.
	 */
	public function __construct( ?Repository $repository = null ) {
		$this->repository = $repository ?? new Repository();
	}

	/**
	 * Record a download if not already counted for this session token.
	 *
	 * @param int    $version_id Version ID.
	 * @param string $token      Privacy token (from cookie or generated).
	 * @return bool True if counted, false if already counted.
	 */
	public function record( int $version_id, string $token ): bool {
		$key = 'adp_dl_' . md5( $version_id . ':' . $token );
		if ( get_transient( $key ) ) {
			return false;
		}

		set_transient( $key, 1, DAY_IN_SECONDS );
		$this->repository->increment_download_count( $version_id );

		$version = $this->repository->find( $version_id );
		if ( null !== $version ) {
			$app_id = (int) $version['app_id'];
			$count  = (int) Meta::get( $app_id, '_adp_download_count' );
			Meta::update( $app_id, '_adp_download_count', $count + 1 );
		}

		return true;
	}

	/**
	 * Get or create privacy token for visitor.
	 *
	 * @return string
	 */
	public function get_visitor_token(): string {
		$cookie_name = 'adp_dl_token';
		if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
			$token = sanitize_text_field( wp_unslash( (string) $_COOKIE[ $cookie_name ] ) );
			if ( preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
				return $token;
			}
		}

		$token = wp_generate_password( 32, false );
		if ( ! headers_sent() ) {
			setcookie(
				$cookie_name,
				$token,
				array(
					'expires'  => time() + YEAR_IN_SECONDS,
					'path'     => COOKIEPATH,
					'domain'   => COOKIE_DOMAIN,
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
		}
		return $token;
	}
}
