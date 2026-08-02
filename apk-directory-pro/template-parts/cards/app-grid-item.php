<?php
/**
 * App grid item card.
 *
 * @package AdpTheme
 */

$data = Adp\Theme\get_app_card_data( get_the_ID() );
if ( empty( $data ) ) {
	return;
}
?>
<article class="adp-app-grid-item">
	<a href="<?php echo esc_url( $data['permalink'] ); ?>" class="adp-app-grid-item__link">
		<?php if ( $data['icon'] ) : ?>
			<img src="<?php echo esc_url( $data['icon'] ); ?>" alt="" class="adp-app-grid-item__icon" width="88" height="88" loading="lazy">
		<?php else : ?>
			<div class="adp-app-grid-item__icon adp-app-grid-item__icon--placeholder" aria-hidden="true"></div>
		<?php endif; ?>
		<h3 class="adp-app-grid-item__title"><?php echo esc_html( $data['title'] ); ?></h3>
		<?php if ( $data['developer'] ) : ?>
			<p class="adp-app-grid-item__developer"><?php echo esc_html( $data['developer'] ); ?></p>
		<?php endif; ?>
		<?php if ( $data['has_rating'] ) : ?>
			<?php Adp\Theme\render_star_rating( $data['rating'], $data['rating_count'] ); ?>
		<?php endif; ?>
	</a>
</article>
