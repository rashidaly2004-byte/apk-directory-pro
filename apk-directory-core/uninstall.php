<?php
/**
 * Uninstall handler — content retention is user-controlled.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$retain = get_option( 'adp_retain_data_on_uninstall', true );

if ( ! $retain ) {
	global $wpdb;

	$table = $wpdb->prefix . 'adp_versions';
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table ) );

	delete_option( 'adp_db_version' );
	delete_option( 'adp_retain_data_on_uninstall' );
	delete_option( 'adp_setup_complete' );
}
