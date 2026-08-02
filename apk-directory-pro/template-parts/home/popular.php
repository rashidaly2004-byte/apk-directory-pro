<?php
/**
 * Popular apps by downloads section.
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) ) {
	return;
}

$count = Adp\Theme\get_homepage_section_count( 'popular', 8 );
$query = Adp\Theme\query_apps( array(
	'posts_per_page' => $count,
	'meta_key'       => '_adp_download_count',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
	'meta_query'     => array(
		array(
			'key'     => '_adp_download_count',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		),
	),
) );

if ( ! $query->have_posts() ) {
	$query = Adp\Theme\query_apps( array(
		'posts_per_page' => $count,
		'meta_key'       => '_adp_download_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );
	if ( ! $query->have_posts() ) {
		return;
	}
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-popular-title">
	<header class="adp-section__header">
		<h2 id="adp-popular-title" class="adp-section__title"><?php esc_html_e( 'Popular downloads', 'apk-directory-pro' ); ?></h2>
		<a href="<?php echo esc_url( add_query_arg( 'sort', 'popular', get_post_type_archive_link( 'adp_app' ) ) ); ?>" class="adp-section__link"><?php esc_html_e( 'See all', 'apk-directory-pro' ); ?></a>
	</header>
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
