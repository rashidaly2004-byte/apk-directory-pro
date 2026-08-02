<?php
/**
 * Single blog post.
 */

get_header();
?>
<main id="main-content" class="adp-main adp-container adp-layout-with-sidebar">
	<div class="adp-layout-main">
		<?php do_action( 'adp_before_content' ); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'adp-post' ); ?>>
				<h1 class="adp-page-title"><?php the_title(); ?></h1>
				<p class="adp-post-meta">
					<?php echo esc_html( get_the_date() ); ?>
					· <?php the_author(); ?>
				</p>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="adp-post-featured"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
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
			<?php comments_template(); ?>
		<?php endwhile; ?>
	</div>
	<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
		<aside class="adp-layout-sidebar"><?php dynamic_sidebar( 'blog-sidebar' ); ?></aside>
	<?php endif; ?>
</main>
<?php
get_footer();
