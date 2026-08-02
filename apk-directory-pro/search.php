<?php
/**
 * Search results.
 */

get_header();
?>
<main id="main-content" class="adp-main adp-container">
	<?php do_action( 'adp_before_content' ); ?>
	<h1 class="adp-page-title">
		<?php
		printf(
			/* translators: %s: search query */
			esc_html__( 'Search results for "%s"', 'apk-directory-pro' ),
			esc_html( get_search_query() )
		);
		?>
	</h1>

	<?php if ( have_posts() ) : ?>
		<div class="adp-search-results">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				if ( get_post_type() === 'adp_app' ) {
					get_template_part( 'template-parts/cards/app-card' );
				} else {
					get_template_part( 'template-parts/cards/blog-card' );
				}
				?>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No results found. Try a different search.', 'apk-directory-pro' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
