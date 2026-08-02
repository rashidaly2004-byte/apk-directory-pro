<?php

namespace APD\Core\Downloads;

use APD\Core\Versions\Repository;

final class Counter {

	private const COOKIE_PREFIX = 'adp_dl_';

	public function should_count( int $version_id ): bool {
		$key = self::COOKIE_PREFIX . $version_id;
		if ( isset( $_COOKIE[ $key ] ) ) {
			return false;
		}
		return true;
	}

	public function mark_counted( int $version_id ): void {
		$key = self::COOKIE_PREFIX . $version_id;
		if ( ! headers_sent() ) {
			setcookie( $key, '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	public function increment( int $version_id ): void {
		if ( ! $this->should_count( $version_id ) ) {
			return;
		}

		global $wpdb;
		$table = Repository::table_name();
		$wpdb->query(
			$wpdb->prepare( 'UPDATE %i SET download_count = download_count + 1 WHERE id = %d', $table, $version_id )
		);

		$version = $wpdb->get_row(
			$wpdb->prepare( 'SELECT app_id FROM %i WHERE id = %d', $table, $version_id ),
			ARRAY_A
		);
		if ( $version ) {
			$app_id = (int) $version['app_id'];
			$count  = (int) get_post_meta( $app_id, '_adp_download_count', true );
			update_post_meta( $app_id, '_adp_download_count', $count + 1 );
		}

		$this->mark_counted( $version_id );
	}
}
