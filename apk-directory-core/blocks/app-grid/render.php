<?php
$count    = max( 1, min( 24, (int) ( $attributes['count'] ?? 6 ) ) );
$category = sanitize_key( (string) ( $attributes['category'] ?? '' ) );

$args = array( 'posts_per_page' => $count );
if ( '' !== $category ) {
	$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		array(
			'taxonomy' => 'adp_app_category',
			'field'    => 'slug',
			'terms'    => $category,
		),
	);
}

$posts = \Adp\Core\Blocks\Helpers::query_apps( $args );
if ( empty( $posts ) ) {
	return '<p class="adp-block-empty">' . esc_html__( 'No apps found.', 'apk-directory-core' ) . '</p>';
}

$html = '<div class="adp-app-grid">';
foreach ( $posts as $post ) {
	$html .= \Adp\Core\Blocks\Helpers::render_app_card( $post );
}
$html .= '</div>';
return $html;
