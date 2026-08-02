<?php
/**
 * Main template fallback.
 */

get_header();
?>
<main id="main-content" class="adp-main adp-container">
	<?php do_action( 'adp_before_content' ); ?>
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/content/content', get_post_type() ); ?>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'apk-directory-pro' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
