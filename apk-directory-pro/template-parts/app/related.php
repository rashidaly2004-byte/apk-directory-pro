<?php
$post_id   = get_the_ID();
$cat_terms = wp_get_post_terms( $post_id, 'adp_app_category', [ 'fields' => 'ids' ] );
$args      = [
	'post_type'      => 'adp_app',
	'posts_per_page' => 6,
	'post__not_in'   => [ $post_id ],
	'orderby'        => 'rand',
];
if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
	$args['tax_query'] = [
		[
			'taxonomy' => 'adp_app_category',
			'field'    => 'term_id',
			'terms'    => $cat_terms,
		],
	];
}
$related = new WP_Query( $args );
if ( ! $related->have_posts() ) {
	return;
}
$is_sidebar = str_contains( basename( __FILE__, '.php' ), 'sidebar' ) || ( isset( $args['sidebar'] ) );
?>
<section class="adp-section adp-related<?php echo $is_sidebar ? ' adp-related--sidebar' : ''; ?>">
	<?php if ( ! $is_sidebar ) : ?>
		<h2><?php esc_html_e( 'Related apps', 'apk-directory-pro' ); ?></h2>
	<?php else : ?>
		<h3><?php esc_html_e( 'Related', 'apk-directory-pro' ); ?></h3>
	<?php endif; ?>
	<div class="adp-app-list adp-app-list--compact">
		<?php while ( $related->have_posts() ) : $related->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/app-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
