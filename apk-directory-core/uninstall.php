<?php
/**
 * Uninstall handler for APK Directory Core.
 *
 * @package Adp\Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$delete_data = get_option( 'adp_core_delete_data_on_uninstall', false );

if ( ! $delete_data ) {
	return;
}

global $wpdb;

$table = $wpdb->prefix . 'adp_versions';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

$options = array(
	'adp_core_settings',
	'adp_core_version',
	'adp_core_db_version',
	'adp_core_setup_wizard_done',
	'adp_core_demo_imported',
	'adp_core_migration_state',
	'adp_core_rewrites_flushed',
	'adp_core_delete_data_on_uninstall',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

$posts = get_posts(
	array(
		'post_type'      => array( 'adp_app', 'adp_report' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $posts as $post_id ) {
	wp_delete_post( (int) $post_id, true );
}

$taxonomies = array( 'adp_app_category', 'adp_developer', 'adp_platform', 'adp_tag' );
foreach ( $taxonomies as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( (int) $term_id, $taxonomy );
		}
	}
}

remove_role( 'apk_manager' );
