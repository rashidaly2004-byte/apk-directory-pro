<?php
/**
 * Page template.
 *
 * @package AdpTheme
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="adp-container adp-content-area">
		<?php Adp\Theme\render_breadcrumbs(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'adp-page' ); ?>>
			<header class="adp-page-header">
				<h1 class="adp-page-header__title"><?php the_title(); ?></h1>
			</header>

			<div class="adp-layout-with-sidebar">
				<div class="adp-layout-with-sidebar__main">
					<?php get_template_part( 'template-parts/content/page' ); ?>
				</div>

				<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
					<aside class="adp-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'apk-directory-pro' ); ?>">
						<?php dynamic_sidebar( 'blog-sidebar' ); ?>
					</aside>
				<?php endif; ?>
			</div>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
