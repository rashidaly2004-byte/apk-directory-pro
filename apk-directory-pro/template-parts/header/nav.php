<?php
/**
 * Primary navigation.
 *
 * @package AdpTheme
 */
?>
<nav class="adp-nav adp-container" aria-label="<?php esc_attr_e( 'Primary navigation', 'apk-directory-pro' ); ?>">
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'menu_class'     => 'adp-nav__list',
			'container'      => false,
			'fallback_cb'    => false,
			'depth'          => 2,
		) );
	} else {
		?>
		<ul class="adp-nav__list">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'apk-directory-pro' ); ?></a></li>
			<?php if ( post_type_exists( 'adp_app' ) ) : ?>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>"><?php esc_html_e( 'Apps', 'apk-directory-pro' ); ?></a></li>
			<?php endif; ?>
		</ul>
		<?php
	}
	?>
</nav>
