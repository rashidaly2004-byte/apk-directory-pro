<?php
$terms = get_terms( array(
	'taxonomy'   => 'adp_app_category',
	'hide_empty' => false,
	'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'   => 'adp_featured',
			'value' => '1',
		),
	),
	'number'     => max( 1, min( 12, (int) ( $attributes['count'] ?? 6 ) ) ),
	'orderby'    => 'meta_value_num',
	'meta_key'   => 'adp_display_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	'order'      => 'ASC',
) );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	$terms = get_terms( array(
		'taxonomy'   => 'adp_app_category',
		'hide_empty' => true,
		'number'     => (int) ( $attributes['count'] ?? 6 ),
	) );
}

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return '<p class="adp-block-empty">' . esc_html__( 'No categories found.', 'apk-directory-core' ) . '</p>';
}

$html = '<div class="adp-featured-categories">';
foreach ( $terms as $term ) {
	$color = get_term_meta( $term->term_id, 'adp_color', true );
	$link  = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		continue;
	}
	$style = $color ? ' style="--term-color:' . esc_attr( (string) $color ) . '"' : '';
	$html .= sprintf(
		'<a href="%s" class="adp-featured-category"%s><span class="adp-featured-category__name">%s</span></a>',
		esc_url( $link ),
		$style,
		esc_html( $term->name )
	);
}
$html .= '</div>';
return $html;
