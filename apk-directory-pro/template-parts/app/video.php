<?php
/**
 * App video embed section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$video   = Adp\Theme\get_app_meta( $post_id, 'video_url' );

if ( $video === '' ) {
	return;
}
?>
<section class="adp-app-video adp-container" aria-labelledby="adp-video-title">
	<h2 id="adp-video-title" class="adp-section-title"><?php esc_html_e( 'Preview video', 'apk-directory-pro' ); ?></h2>
	<div class="adp-app-video__embed">
		<?php echo wp_oembed_get( $video ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</section>
