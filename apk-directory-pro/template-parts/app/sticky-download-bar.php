<?php
/**
 * Sticky mobile download bar.
 *
 * @package AdpTheme
 */

$post_id  = get_the_ID();
$download = Adp\Theme\get_app_meta( $post_id, 'download_url' );
$title    = get_the_title();
$icon     = get_the_post_thumbnail_url( $post_id, 'adp-app-icon-sm' );
?>
<div class="adp-sticky-download" data-adp-sticky-download hidden>
	<div class="adp-sticky-download__inner">
		<?php if ( $icon ) : ?>
			<img src="<?php echo esc_url( $icon ); ?>" alt="" class="adp-sticky-download__icon" width="40" height="40">
		<?php endif; ?>
		<div class="adp-sticky-download__info">
			<span class="adp-sticky-download__title"><?php echo esc_html( $title ); ?></span>
		</div>
		<?php if ( $download ) : ?>
			<a href="<?php echo esc_url( $download ); ?>" class="adp-btn adp-btn--primary adp-btn--sm" rel="nofollow noopener" download>
				<?php esc_html_e( 'Download', 'apk-directory-pro' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
