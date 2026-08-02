<?php
$categories = get_terms( [
	'taxonomy'   => 'adp_app_category',
	'hide_empty' => false,
	'number'     => 8,
] );
if ( empty( $categories ) || is_wp_error( $categories ) ) {
	return;
}
?>
<section class="adp-section adp-container">
	<h2 class="adp-section__title"><?php esc_html_e( 'Categories', 'apk-directory-pro' ); ?></h2>
	<div class="adp-category-chips">
		<?php foreach ( $categories as $cat ) : ?>
			<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="adp-chip">
				<?php echo esc_html( $cat->name ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</section>
