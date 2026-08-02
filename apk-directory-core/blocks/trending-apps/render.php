<?php
$posts = \Adp\Core\Blocks\Helpers::query_apps( array(
	'posts_per_page' => max( 1, min( 12, (int) ( $attributes['count'] ?? 8 ) ) ),
	'meta_key'       => '_adp_download_count', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );

if ( empty( $posts ) ) {
	$posts = \Adp\Core\Blocks\Helpers::query_apps( array(
		'posts_per_page' => (int) ( $attributes['count'] ?? 8 ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}

if ( empty( $posts ) ) {
	return '<p class="adp-block-empty">' . esc_html__( 'No trending apps.', 'apk-directory-core' ) . '</p>';
}

$html = '<div class="adp-trending-apps">';
foreach ( $posts as $post ) {
	$html .= \Adp\Core\Blocks\Helpers::render_app_card( $post );
}
$html .= '</div>';
return $html;
