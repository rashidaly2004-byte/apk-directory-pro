<?php
$post_id = get_the_ID();
$ids     = adp_get_meta( $post_id, 'screenshot_ids', [] );
if ( ! is_array( $ids ) || empty( $ids ) ) {
	return;
}
?>
<section class="adp-section adp-screenshots" aria-label="<?php esc_attr_e( 'Screenshots', 'apk-directory-pro' ); ?>">
	<h2><?php esc_html_e( 'Screenshots', 'apk-directory-pro' ); ?></h2>
	<div class="adp-screenshots__track" role="list">
		<?php foreach ( $ids as $attachment_id ) :
			$img = wp_get_attachment_image( (int) $attachment_id, 'adp-screenshot', false, [
				'loading' => 'lazy',
				'class'   => 'adp-screenshot',
				'data-lightbox' => wp_get_attachment_url( (int) $attachment_id ),
			] );
			if ( ! $img ) continue;
			?>
			<button type="button" class="adp-screenshot-btn" role="listitem" data-lightbox-src="<?php echo esc_url( wp_get_attachment_url( (int) $attachment_id ) ); ?>">
				<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		<?php endforeach; ?>
	</div>
</section>
<div id="adp-lightbox" class="adp-lightbox" hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Screenshot', 'apk-directory-pro' ); ?>">
	<button type="button" class="adp-lightbox__close" aria-label="<?php esc_attr_e( 'Close', 'apk-directory-pro' ); ?>">×</button>
	<img src="" alt="" class="adp-lightbox__img" />
</div>
