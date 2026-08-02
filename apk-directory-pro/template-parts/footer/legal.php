<?php
/**
 * Footer legal bar.
 *
 * @package AdpTheme
 */

$copyright = Adp\Theme\get_theme_mod_string(
	'adp_footer_copyright',
	sprintf(
		/* translators: %s: current year */
		__( '© %s APK Directory Pro. All rights reserved.', 'apk-directory-pro' ),
		gmdate( 'Y' )
	)
);
?>
<div class="adp-footer-legal">
	<div class="adp-footer-legal__inner adp-container">
		<p class="adp-footer-legal__copyright"><?php echo esc_html( $copyright ); ?></p>
		<?php if ( has_nav_menu( 'footer-legal' ) ) : ?>
			<nav class="adp-footer-legal__nav" aria-label="<?php esc_attr_e( 'Legal', 'apk-directory-pro' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer-legal',
					'menu_class'     => 'adp-footer-legal__menu',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</nav>
		<?php endif; ?>
	</div>
</div>
