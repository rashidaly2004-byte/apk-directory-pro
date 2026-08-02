<?php
/**
 * Blog posts index.
 */

get_header();
?>
<main id="main-content" class="adp-main adp-container">
	<?php do_action( 'adp_before_content' ); ?>
	<h1 class="adp-page-title"><?php esc_html_e( 'Blog', 'apk-directory-pro' ); ?></h1>
	<div class="adp-blog-grid">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/cards/blog-card' ); ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
	<?php the_posts_pagination(); ?>
</main>
<?php
get_footer();
