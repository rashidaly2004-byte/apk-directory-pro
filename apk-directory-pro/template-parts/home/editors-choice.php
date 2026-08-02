<?php
$apps = adp_query_apps( [
	'posts_per_page' => 8,
	'meta_key'       => '_adp_editor_rating',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
	'meta_query'     => [
		[
			'key'     => '_adp_editor_rating',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		],
	],
] );
if ( ! $apps->have_posts() ) {
	return;
}
?>
<section class="adp-section adp-container">
	<div class="adp-section__header">
		<h2 class="adp-section__title"><?php esc_html_e( "Editor's choice", 'apk-directory-pro' ); ?></h2>
	</div>
	<div class="adp-app-list">
		<?php while ( $apps->have_posts() ) : $apps->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/app-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
