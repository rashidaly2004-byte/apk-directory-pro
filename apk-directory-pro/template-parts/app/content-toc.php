<?php
/**
 * App content with table of contents.
 *
 * @package AdpTheme
 */

$content = get_the_content();
if ( $content === '' ) {
	return;
}
?>
<section class="adp-app-content adp-container" aria-labelledby="adp-content-title">
	<h2 id="adp-content-title" class="adp-section-title"><?php esc_html_e( 'About this app', 'apk-directory-pro' ); ?></h2>
	<div class="adp-entry-content">
		<?php the_content(); ?>
	</div>
</section>
