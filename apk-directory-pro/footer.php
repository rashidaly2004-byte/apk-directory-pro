<footer class="adp-footer" role="contentinfo">
	<div class="adp-container adp-footer__grid">
		<div class="adp-footer__brand">
			<strong><?php bloginfo( 'name' ); ?></strong>
			<?php if ( $text = get_theme_mod( 'adp_footer_text' ) ) : ?>
				<p><?php echo esc_html( $text ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'A directory of apps and games. Files are provided by third parties; verify before installing.', 'apk-directory-pro' ); ?></p>
			<?php endif; ?>
		</div>
		<div class="adp-footer__col">
			<h3 class="adp-footer__title"><?php esc_html_e( 'Browse', 'apk-directory-pro' ); ?></h3>
			<?php
			wp_nav_menu( [
				'theme_location' => 'footer-browse',
				'container'      => false,
				'menu_class'     => 'adp-footer-menu',
				'fallback_cb'    => false,
			] );
			?>
		</div>
		<div class="adp-footer__col">
			<h3 class="adp-footer__title"><?php esc_html_e( 'Company', 'apk-directory-pro' ); ?></h3>
			<?php
			wp_nav_menu( [
				'theme_location' => 'footer-company',
				'container'      => false,
				'menu_class'     => 'adp-footer-menu',
				'fallback_cb'    => false,
			] );
			?>
		</div>
		<div class="adp-footer__col">
			<h3 class="adp-footer__title"><?php esc_html_e( 'Legal', 'apk-directory-pro' ); ?></h3>
			<?php
			wp_nav_menu( [
				'theme_location' => 'footer-legal',
				'container'      => false,
				'menu_class'     => 'adp-footer-menu',
				'fallback_cb'    => false,
			] );
			?>
		</div>
	</div>
	<div class="adp-footer__bottom adp-container">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'apk-directory-pro' ); ?></p>
		<?php do_action( 'adp_footer_language_switcher' ); ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
