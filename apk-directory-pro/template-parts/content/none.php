<?php
/**
 * No results content.
 *
 * @package AdpTheme
 */
?>
<div class="adp-empty-state">
	<div class="adp-empty-state__icon" aria-hidden="true"><?php Adp\Theme\icon( 'search' ); ?></div>
	<h2 class="adp-empty-state__title"><?php esc_html_e( 'Nothing found', 'apk-directory-pro' ); ?></h2>
	<p class="adp-empty-state__message"><?php esc_html_e( 'Try adjusting your search or filters to find what you are looking for.', 'apk-directory-pro' ); ?></p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Back to home', 'apk-directory-pro' ); ?></a>
</div>
