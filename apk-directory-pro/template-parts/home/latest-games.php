<?php
/**
 * Latest games section.
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) || ! taxonomy_exists( 'adp_app_category' ) ) {
	return;
}

$count = Adp\Theme\get_homepage_section_count( 'latest-games', 8 );
$query = Adp\Theme\query_apps( array(
	'posts_per_page' => $count,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'tax_query'      => array(
		array(
			'taxonomy' => 'adp_app_category',
			'field'    => 'slug',
			'terms'    => array( 'games', 'game' ),
		),
	),
) );

if ( ! $query->have_posts() ) {
	$query = Adp\Theme\query_apps( array(
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	if ( ! $query->have_posts() ) {
		return;
	}
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-latest-games-title">
	<header class="adp-section__header">
		<h2 id="adp-latest-games-title" class="adp-section__title"><?php esc_html_e( 'Latest games', 'apk-directory-pro' ); ?></h2>
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
