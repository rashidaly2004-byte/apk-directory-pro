<?php
$post_id = get_the_ID();
$url     = adp_get_meta( $post_id, 'video_url' );
if ( ! $url ) {
	return;
}
$embed = wp_oembed_get( $url );
if ( ! $embed ) {
	return;
}
?>
<section class="adp-section adp-video">
	<h2><?php esc_html_e( 'Video', 'apk-directory-pro' ); ?></h2>
	<div class="adp-video__embed" data-click-load>
		<button type="button" class="adp-btn adp-video__load"><?php esc_html_e( 'Load video', 'apk-directory-pro' ); ?></button>
		<div class="adp-video__container" hidden><?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
</section>
