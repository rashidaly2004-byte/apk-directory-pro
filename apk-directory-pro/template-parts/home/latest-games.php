<?php
$games = get_term_by( 'slug', 'games', 'adp_app_category' );
$args  = [ 'posts_per_page' => 8 ];
if ( $games ) {
	$args['tax_query'] = [
		[
			'taxonomy' => 'adp_app_category',
			'field'    => 'term_id',
			'terms'    => $games->term_id,
		],
	];
}
$apps = adp_query_apps( $args );
?>
<section class="adp-section adp-container">
	<div class="adp-section__header">
		<h2 class="adp-section__title"><?php esc_html_e( 'Latest games', 'apk-directory-pro' ); ?></h2>
	</div>
	<div class="adp-app-list">
		<?php while ( $apps->have_posts() ) : $apps->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/app-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
