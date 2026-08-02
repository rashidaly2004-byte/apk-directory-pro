<?php
/**
 * Safety notice section.
 *
 * @package AdpTheme
 */
?>
<aside class="adp-safety-notice adp-container" role="note">
	<div class="adp-safety-notice__inner">
		<?php Adp\Theme\icon( 'shield' ); ?>
		<div class="adp-safety-notice__content">
			<h2 class="adp-safety-notice__title"><?php esc_html_e( 'Safety notice', 'apk-directory-pro' ); ?></h2>
			<p><?php esc_html_e( 'Always verify the app source and permissions before installing. Download files only from trusted sources. We scan uploads but cannot guarantee third-party modifications.', 'apk-directory-pro' ); ?></p>
		</div>
	</div>
</aside>
