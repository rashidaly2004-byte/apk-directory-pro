<?php
/**
 * Related apps section.
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) ) {
	return;
}

$post_id = get_the_ID();
$terms   = get_the_terms( $post_id, 'adp_app_category' );

$args = array(
	'posts_per_page' => 6,
	'post__not_in'   => array( $post_id ),
	'orderby'        => 'rand',
);

if ( $terms && ! is_wp_error( $terms ) ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'adp_app_category',
			'field'    => 'term_id',
			'terms'    => wp_list_pluck( $terms, 'term_id' ),
		),
	);
}

$query = Adp\Theme\query_apps( $args );

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="adp-related adp-container" aria-labelledby="adp-related-title">
	<h2 id="adp-related-title" class="adp-section-title"><?php esc_html_e( 'Related apps', 'apk-directory-pro' ); ?></h2>
	<div class="adp-app-list adp-app-list--list">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part( 'template-parts/cards/app-row' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
