<?php
$post_id = get_the_ID();
if ( ! $post_id || 'adp_app' !== get_post_type( $post_id ) ) {
	return '';
}

$count = max( 1, min( 12, (int) ( $attributes['count'] ?? 4 ) ) );
$terms = wp_get_post_terms( $post_id, 'adp_app_category', array( 'fields' => 'ids' ) );
$devs  = wp_get_post_terms( $post_id, 'adp_developer', array( 'fields' => 'ids' ) );

$args = array(
	'posts_per_page' => $count,
	'post__not_in'   => array( $post_id ),
	'orderby'        => 'rand',
);

$tax_query = array( 'relation' => 'OR' );
if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	$tax_query[] = array( 'taxonomy' => 'adp_app_category', 'field' => 'term_id', 'terms' => $terms );
}
if ( ! is_wp_error( $devs ) && ! empty( $devs ) ) {
	$tax_query[] = array( 'taxonomy' => 'adp_developer', 'field' => 'term_id', 'terms' => $devs );
}
if ( count( $tax_query ) > 1 ) {
	$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
}

$posts = \Adp\Core\Blocks\Helpers::query_apps( $args );
if ( empty( $posts ) ) {
	return '';
}

$html = '<div class="adp-related-apps"><h3 class="adp-related-apps__title">' . esc_html__( 'Related Apps', 'apk-directory-core' ) . '</h3><div class="adp-app-grid">';
foreach ( $posts as $post ) {
	$html .= \Adp\Core\Blocks\Helpers::render_app_card( $post );
}
$html .= '</div></div>';
return $html;
