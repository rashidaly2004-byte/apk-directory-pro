<?php
/**
 * Editor's choice section.
 *
 * @package AdpTheme
 */

if ( ! post_type_exists( 'adp_app' ) ) {
	return;
}

$count = Adp\Theme\get_homepage_section_count( 'editors-choice', 6 );
$query = Adp\Theme\query_apps( array(
	'posts_per_page' => $count,
	'meta_query'     => array(
		array(
			'key'   => '_adp_verified',
			'value' => '1',
		),
	),
) );

if ( ! $query->have_posts() ) {
	$query = Adp\Theme\query_apps( array(
		'posts_per_page' => $count,
	'meta_key'       => '_adp_editor_rating',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );
	if ( ! $query->have_posts() ) {
		return;
	}
}
?>
<section class="adp-section adp-section--highlight adp-container" aria-labelledby="adp-editors-choice-title">
	<header class="adp-section__header">
		<h2 id="adp-editors-choice-title" class="adp-section__title">
			<?php Adp\Theme\icon( 'verified' ); ?>
			<?php esc_html_e( "Editor's choice", 'apk-directory-pro' ); ?>
		</h2>
	</header>
	<div class="adp-app-list adp-app-list--grid">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			get_template_part( 'template-parts/cards/app-grid-item' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
