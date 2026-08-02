<?php
/**
 * Blog posts index.
 *
 * @package AdpTheme
 */

get_header();
?>

<div class="adp-container adp-content-area adp-blog-index">
	<header class="adp-page-header">
		<h1 class="adp-page-header__title"><?php single_post_title(); ?></h1>
	</header>

	<div class="adp-layout-with-sidebar">
		<div class="adp-layout-with-sidebar__main">
			<?php if ( have_posts() ) : ?>
				<div class="adp-post-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/cards/post-card' );
					endwhile;
					?>
				</div>
				<?php get_template_part( 'template-parts/navigation/pagination' ); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/none' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
			<aside class="adp-sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'apk-directory-pro' ); ?>">
				<?php dynamic_sidebar( 'blog-sidebar' ); ?>
			</aside>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
