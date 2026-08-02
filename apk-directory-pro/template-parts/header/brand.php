<?php
/**
 * Site header brand area.
 *
 * @package AdpTheme
 */
?>
<header class="adp-header" role="banner">
	<div class="adp-header__inner adp-container">
		<div class="adp-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<div class="adp-header__logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="adp-header__site-title" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>

			<?php
			$tagline = Adp\Theme\get_theme_mod_string( 'adp_site_tagline' );
			if ( $tagline ) :
				?>
				<p class="adp-header__tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
		</div>

		<div class="adp-header__actions">
			<button type="button" class="adp-header__theme-toggle" data-adp-dark-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'apk-directory-pro' ); ?>">
				<?php Adp\Theme\icon( 'moon', array( 'class' => 'adp-icon adp-icon--moon' ) ); ?>
				<?php Adp\Theme\icon( 'sun', array( 'class' => 'adp-icon adp-icon--sun' ) ); ?>
			</button>

			<button type="button" class="adp-header__menu-toggle" data-adp-menu-open aria-expanded="false" aria-controls="adp-mobile-drawer" aria-label="<?php esc_attr_e( 'Open menu', 'apk-directory-pro' ); ?>">
				<?php Adp\Theme\icon( 'menu' ); ?>
			</button>
		</div>
	</div>
</header>
