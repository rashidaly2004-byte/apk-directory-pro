<?php
/**
 * Standard page template.
 */

get_header();
?>
<main id="main-content" class="adp-main adp-container">
	<?php do_action( 'adp_before_content' ); ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'adp-page' ); ?>>
			<h1 class="adp-page-title"><?php the_title(); ?></h1>
			<div class="adp-article-content"><?php the_content(); ?></div>
			<?php
			wp_link_pages(
				array(
					'before' => '<div class="adp-page-links"><span class="adp-page-links__label">' . esc_html__( 'Pages:', 'apk-directory-pro' ) . '</span>',
					'after'  => '</div>',
				)
			);
			?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
