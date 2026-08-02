<?php
/**
 * Mobile navigation drawer.
 *
 * @package AdpTheme
 */
?>
<div id="adp-mobile-drawer" class="adp-drawer" hidden data-adp-drawer>
	<div class="adp-drawer__overlay" data-adp-menu-close tabindex="-1" aria-hidden="true"></div>
	<div class="adp-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Mobile menu', 'apk-directory-pro' ); ?>">
		<div class="adp-drawer__header">
			<span class="adp-drawer__title"><?php bloginfo( 'name' ); ?></span>
			<button type="button" class="adp-drawer__close" data-adp-menu-close aria-label="<?php esc_attr_e( 'Close menu', 'apk-directory-pro' ); ?>">
				<?php Adp\Theme\icon( 'close' ); ?>
			</button>
		</div>
		<nav class="adp-drawer__nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'apk-directory-pro' ); ?>">
			<?php
			if ( has_nav_menu( 'mobile' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'mobile',
					'menu_class'     => 'adp-drawer__list',
					'container'      => false,
					'fallback_cb'    => false,
				) );
			} elseif ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_class'     => 'adp-drawer__list',
					'container'      => false,
					'fallback_cb'    => false,
				) );
			}
			?>
		</nav>
	</div>
</div>
