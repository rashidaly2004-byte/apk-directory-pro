<?php
/**
 * What's new / changelog section.
 *
 * @package AdpTheme
 */

$post_id   = get_the_ID();
$changelog = Adp\Theme\get_app_meta( $post_id, 'changelog' );

if ( $changelog === '' ) {
	$changelog = Adp\Theme\get_app_meta( $post_id, 'whats_new' );
}

if ( $changelog === '' ) {
	return;
}
?>
<section class="adp-whats-new adp-container" aria-labelledby="adp-whats-new-title">
	<h2 id="adp-whats-new-title" class="adp-section-title"><?php esc_html_e( "What's new", 'apk-directory-pro' ); ?></h2>
	<div class="adp-whats-new__content adp-entry-content">
		<?php echo wp_kses_post( wpautop( $changelog ) ); ?>
	</div>
</section>
