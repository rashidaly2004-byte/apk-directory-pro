<?php
$apps = adp_query_apps( [
	'posts_per_page' => 10,
	'meta_key'       => '_adp_updated_date',
	'orderby'        => 'meta_value',
	'order'          => 'DESC',
] );
?>
<section class="adp-section adp-container">
	<div class="adp-section__header">
		<h2 class="adp-section__title"><?php esc_html_e( 'Latest updates', 'apk-directory-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'sort', 'updated', get_post_type_archive_link( 'adp_app' ) ) ); ?>" class="adp-section__link"><?php esc_html_e( 'View all', 'apk-directory-pro' ); ?></a>
	</div>
	<div class="adp-app-list adp-app-list--two-col">
		<?php while ( $apps->have_posts() ) : $apps->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/app-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
