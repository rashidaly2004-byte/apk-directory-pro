<?php
$apps = adp_query_apps( [
	'posts_per_page' => 8,
	'meta_key'       => '_adp_download_count',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
] );
?>
<section class="adp-section adp-container">
	<div class="adp-section__header">
		<h2 class="adp-section__title"><?php esc_html_e( 'Popular apps', 'apk-directory-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'sort', 'popular', get_post_type_archive_link( 'adp_app' ) ) ); ?>" class="adp-section__link"><?php esc_html_e( 'View all', 'apk-directory-pro' ); ?></a>
	</div>
	<div class="adp-app-list">
		<?php while ( $apps->have_posts() ) : $apps->the_post(); ?>
			<?php get_template_part( 'template-parts/cards/app-card' ); ?>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
