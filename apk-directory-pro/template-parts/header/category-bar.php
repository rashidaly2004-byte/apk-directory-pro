<?php
/**
 * Category bar navigation.
 *
 * @package AdpTheme
 */

if ( ! Adp\Theme\get_theme_mod_bool( 'adp_show_category_bar', true ) ) {
	return;
}

if ( ! taxonomy_exists( 'adp_app_category' ) ) {
	return;
}

$categories = get_terms( array(
	'taxonomy'   => 'adp_app_category',
	'hide_empty' => true,
	'number'     => 12,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

if ( ! $categories || is_wp_error( $categories ) ) {
	return;
}
?>
<nav class="adp-category-bar" aria-label="<?php esc_attr_e( 'App categories', 'apk-directory-pro' ); ?>">
	<div class="adp-category-bar__inner adp-container">
		<ul class="adp-category-bar__list">
			<?php foreach ( $categories as $cat ) : ?>
				<li class="adp-category-bar__item">
					<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="adp-category-bar__link">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
