<?php
/**
 * Footer columns.
 *
 * @package AdpTheme
 */
?>
<footer class="adp-footer" role="contentinfo">
	<div class="adp-footer__main adp-container">
		<div class="adp-footer__columns">
			<div class="adp-footer__column">
				<h3 class="adp-footer__heading"><?php esc_html_e( 'Company', 'apk-directory-pro' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer-company' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer-company',
						'menu_class'     => 'adp-footer__menu',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					) );
				}
				?>
			</div>

			<div class="adp-footer__column">
				<h3 class="adp-footer__heading"><?php esc_html_e( 'Browse', 'apk-directory-pro' ); ?></h3>
				<?php
				if ( has_nav_menu( 'footer-browse' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer-browse',
						'menu_class'     => 'adp-footer__menu',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					) );
				} elseif ( post_type_exists( 'adp_app' ) ) {
					?>
					<ul class="adp-footer__menu">
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>"><?php esc_html_e( 'All Apps', 'apk-directory-pro' ); ?></a></li>
					</ul>
					<?php
				}
				?>
			</div>

			<?php if ( is_active_sidebar( 'footer-optional' ) ) : ?>
				<div class="adp-footer__column adp-footer__column--widgets">
					<?php dynamic_sidebar( 'footer-optional' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		$ad = Adp\Theme\get_ad_slot( 'footer' );
		if ( $ad ) {
			echo '<div class="adp-ad-slot adp-ad-slot--footer">' . $ad . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>

	<?php get_template_part( 'template-parts/footer/legal' ); ?>
</footer>
