<?php
/**
 * Featured categories section.
 *
 * @package AdpTheme
 */

if ( ! taxonomy_exists( 'adp_app_category' ) ) {
	return;
}

$categories = get_terms( array(
	'taxonomy'   => 'adp_app_category',
	'hide_empty' => true,
	'number'     => 8,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

if ( ! $categories || is_wp_error( $categories ) ) {
	return;
}
?>
<section class="adp-section adp-container" aria-labelledby="adp-featured-categories-title">
	<header class="adp-section__header">
		<h2 id="adp-featured-categories-title" class="adp-section__title"><?php esc_html_e( 'Browse by category', 'apk-directory-pro' ); ?></h2>
	</header>
	<div class="adp-category-grid">
		<?php foreach ( $categories as $cat ) : ?>
			<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="adp-category-card">
				<span class="adp-category-card__name"><?php echo esc_html( $cat->name ); ?></span>
				<span class="adp-category-card__count"><?php echo esc_html( sprintf(
					/* translators: %d: number of apps */
					_n( '%d app', '%d apps', $cat->count, 'apk-directory-pro' ),
					$cat->count
				) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
