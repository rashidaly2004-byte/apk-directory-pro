<?php
/**
 * Trending apps rail section.
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) ) {
	return;
}

$count = Adp\Theme\get_homepage_section_count( 'trending', 8 );
$query = Adp\Theme\query_apps( array(
	'posts_per_page' => $count,
	'meta_key'       => '_adp_download_count',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-trending-title">
	<header class="adp-section__header">
		<h2 id="adp-trending-title" class="adp-section__title"><?php esc_html_e( 'Trending now', 'apk-directory-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'sort', 'popular', get_post_type_archive_link( 'adp_app' ) ) ); ?>" class="adp-section__link"><?php esc_html_e( 'See all', 'apk-directory-pro' ); ?></a>
	</header>
	<div class="adp-rail" data-adp-rail>
		<div class="adp-rail__track">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/cards/app-grid-item' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
