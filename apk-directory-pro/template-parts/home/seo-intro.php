<?php
/**
 * SEO intro section.
 *
 * @package AdpTheme
 */

$content = Adp\Theme\get_theme_mod_string( 'adp_seo_intro_content' );
if ( $content === '' ) {
	return;
}
?>
<section class="adp-section adp-seo-intro adp-container" aria-labelledby="adp-seo-intro-title">
	<h2 id="adp-seo-intro-title" class="adp-sr-only"><?php esc_html_e( 'About', 'apk-directory-pro' ); ?></h2>
	<div class="adp-seo-intro__content adp-entry-content">
		<?php echo wp_kses_post( wpautop( $content ) ); ?>
	</div>
</section>
