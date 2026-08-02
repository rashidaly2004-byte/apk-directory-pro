<?php
/**
 * Single app template.
 *
 * @package AdpTheme
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'adp-app-single' ); ?>>
		<?php Adp\Theme\render_breadcrumbs(); ?>

		<div class="adp-app-layout">
			<div class="adp-app-layout__main">
				<?php
				get_template_part( 'template-parts/app/hero' );
				get_template_part( 'template-parts/app/download-actions' );
				get_template_part( 'template-parts/app/meta-grid' );
				get_template_part( 'template-parts/app/trust-strip' );
				get_template_part( 'template-parts/app/screenshots' );
				get_template_part( 'template-parts/app/video' );
				get_template_part( 'template-parts/app/content-toc' );
				get_template_part( 'template-parts/app/whats-new' );
				get_template_part( 'template-parts/app/tech-details' );
				get_template_part( 'template-parts/app/version-history' );
				get_template_part( 'template-parts/app/reviews' );
				get_template_part( 'template-parts/app/report-form' );

				if ( Adp\Theme\get_theme_mod_bool( 'adp_show_install_guidance', true ) ) {
					get_template_part( 'template-parts/app/install-guidance' );
				}

				if ( Adp\Theme\get_theme_mod_bool( 'adp_show_safety_notice', true ) ) {
					get_template_part( 'template-parts/app/safety-notice' );
				}

				get_template_part( 'template-parts/app/related' );
				?>
			</div>

			<aside class="adp-app-layout__sidebar" aria-label="<?php esc_attr_e( 'App details sidebar', 'apk-directory-pro' ); ?>">
				<div class="adp-app-sidebar-sticky">
					<?php get_template_part( 'template-parts/app/download-actions', null, array( 'context' => 'sidebar' ) ); ?>
					<?php
					$ad = Adp\Theme\get_ad_slot( 'app_sidebar' );
					if ( $ad ) {
						echo '<div class="adp-ad-slot adp-ad-slot--sidebar">' . $ad . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					<?php if ( is_active_sidebar( 'app-sidebar' ) ) : ?>
						<?php dynamic_sidebar( 'app-sidebar' ); ?>
					<?php endif; ?>
				</div>
			</aside>
		</div>

		<?php if ( Adp\Theme\get_theme_mod_bool( 'adp_sticky_download_bar', true ) ) : ?>
			<?php get_template_part( 'template-parts/app/sticky-download-bar' ); ?>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
