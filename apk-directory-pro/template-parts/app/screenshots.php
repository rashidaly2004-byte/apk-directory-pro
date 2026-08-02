<?php
/**
 * App screenshots gallery.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$screenshots = get_post_meta( $post_id, '_adp_screenshot_ids', true );

if ( ! is_array( $screenshots ) || empty( $screenshots ) ) {
	$legacy = get_post_meta( $post_id, '_adp_screenshots', true );
	if ( is_array( $legacy ) && ! empty( $legacy ) ) {
		$screenshots = $legacy;
	}
}

if ( empty( $screenshots ) ) {
	$gallery = get_post_gallery( $post_id, false );
	if ( $gallery && ! empty( $gallery['ids'] ) ) {
		$screenshots = array_map( 'intval', explode( ',', $gallery['ids'] ) );
	}
}

if ( empty( $screenshots ) ) {
	return;
}
?>
<section class="adp-screenshots adp-container" aria-labelledby="adp-screenshots-title">
	<h2 id="adp-screenshots-title" class="adp-section-title"><?php esc_html_e( 'Screenshots', 'apk-directory-pro' ); ?></h2>
	<div class="adp-screenshots__gallery" data-adp-lightbox-gallery>
		<?php foreach ( $screenshots as $index => $screenshot ) : ?>
			<?php
			$attachment_id = is_numeric( $screenshot ) ? (int) $screenshot : attachment_url_to_postid( (string) $screenshot );
			if ( ! $attachment_id ) {
				continue;
			}
			$full  = wp_get_attachment_image_url( $attachment_id, 'large' );
			$thumb = wp_get_attachment_image_url( $attachment_id, 'adp-screenshot' );
			if ( ! $full ) {
				continue;
			}
			?>
			<button type="button" class="adp-screenshots__item" data-adp-lightbox-trigger data-src="<?php echo esc_url( $full ); ?>" aria-label="<?php echo esc_attr( sprintf(
				/* translators: %d: screenshot number */
				__( 'Screenshot %d', 'apk-directory-pro' ),
				$index + 1
			) ); ?>">
				<img src="<?php echo esc_url( $thumb ?: $full ); ?>" alt="" loading="lazy" width="200" height="400">
			</button>
		<?php endforeach; ?>
	</div>
</section>
