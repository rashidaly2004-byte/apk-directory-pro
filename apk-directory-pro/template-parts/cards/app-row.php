<?php
/**
 * App list row card.
 *
 * @package AdpTheme
 */

$data = Adp\Theme\get_app_card_data( get_the_ID() );
if ( empty( $data ) ) {
	return;
}
?>
<article class="adp-app-row" data-adp-app-row>
	<a href="<?php echo esc_url( $data['permalink'] ); ?>" class="adp-app-row__icon-link" tabindex="-1" aria-hidden="true">
		<?php if ( $data['icon'] ) : ?>
			<img src="<?php echo esc_url( $data['icon'] ); ?>" alt="" class="adp-app-row__icon" width="72" height="72" loading="lazy">
		<?php else : ?>
			<div class="adp-app-row__icon adp-app-row__icon--placeholder" aria-hidden="true"></div>
		<?php endif; ?>
	</a>

	<div class="adp-app-row__body">
		<h3 class="adp-app-row__title">
			<a href="<?php echo esc_url( $data['permalink'] ); ?>"><?php echo esc_html( $data['title'] ); ?></a>
		</h3>
		<p class="adp-app-row__meta">
			<?php if ( $data['developer'] ) : ?>
				<span class="adp-app-row__developer"><?php echo esc_html( $data['developer'] ); ?></span>
			<?php endif; ?>
			<?php if ( $data['category'] ) : ?>
				<span class="adp-app-row__category"><?php echo esc_html( $data['category'] ); ?></span>
			<?php endif; ?>
		</p>
		<dl class="adp-app-row__details">
			<?php if ( $data['version'] ) : ?>
				<div class="adp-app-row__detail">
					<dt class="adp-sr-only"><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></dt>
					<dd>v<?php echo esc_html( $data['version'] ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( $data['size'] ) : ?>
				<div class="adp-app-row__detail">
					<dt class="adp-sr-only"><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></dt>
					<dd><?php echo esc_html( $data['size'] ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( $data['updated'] ) : ?>
				<div class="adp-app-row__detail">
					<dt class="adp-sr-only"><?php esc_html_e( 'Updated', 'apk-directory-pro' ); ?></dt>
					<dd><?php echo esc_html( $data['updated'] ); ?></dd>
				</div>
			<?php endif; ?>
		</dl>
		<?php if ( $data['has_rating'] ) : ?>
			<?php Adp\Theme\render_star_rating( $data['rating'], $data['rating_count'] ); ?>
		<?php endif; ?>
	</div>

	<div class="adp-app-row__actions">
		<a href="<?php echo esc_url( $data['permalink'] ); ?>" class="adp-btn adp-btn--ghost adp-btn--sm">
			<?php Adp\Theme\icon( 'download', array( 'class' => 'adp-icon adp-icon--sm' ) ); ?>
			<span><?php esc_html_e( 'View', 'apk-directory-pro' ); ?></span>
		</a>
	</div>
</article>
