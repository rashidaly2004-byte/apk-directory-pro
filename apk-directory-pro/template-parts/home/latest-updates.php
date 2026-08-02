<?php
/**
 * Latest updates section (2-column layout).
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) ) {
	return;
}

$count = Adp\Theme\get_homepage_section_count( 'latest-updates', 8 );
$query = Adp\Theme\query_apps( array(
	'posts_per_page' => $count,
	'orderby'        => 'modified',
	'order'          => 'DESC',
) );

if ( ! $query->have_posts() ) {
	return;
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-latest-updates-title">
	<header class="adp-section__header">
		<h2 id="adp-latest-updates-title" class="adp-section__title"><?php esc_html_e( 'Latest updates', 'apk-directory-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'sort', 'updated', get_post_type_archive_link( 'adp_app' ) ) ); ?>" class="adp-section__link"><?php esc_html_e( 'See all', 'apk-directory-pro' ); ?></a>
	</header>
	<div class="adp-app-list adp-app-list--list adp-app-list--two-col">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part( 'template-parts/cards/app-row' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
