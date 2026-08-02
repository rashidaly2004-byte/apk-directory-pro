<?php
$posts = new WP_Query( [
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
] );
if ( ! $posts->have_posts() ) {
	return;
}
?>
<section class="adp-section adp-container">
	<div class="adp-section__header">
		<h2 class="adp-section__title"><?php esc_html_e( 'Latest guides', 'apk-directory-pro' ); ?></h2>
	</div>
	<div class="adp-blog-grid">
		<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/blog-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
