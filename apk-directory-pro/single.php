<?php
/**
 * Single post template.
 *
 * @package AdpTheme
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="adp-container adp-content-area">
		<?php Adp\Theme\render_breadcrumbs(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'adp-single-post' ); ?>>
			<header class="adp-page-header">
				<h1 class="adp-page-header__title"><?php the_title(); ?></h1>
				<div class="adp-post-meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php if ( get_the_author() ) : ?>
						<span class="adp-post-meta__author">
							<?php
							printf(
								/* translators: %s: author name */
								esc_html__( 'By %s', 'apk-directory-pro' ),
								esc_html( get_the_author() )
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="adp-post-featured-image">
					<?php the_post_thumbnail( 'large' ); ?>
				</figure>
			<?php endif; ?>

			<div class="adp-layout-with-sidebar">
				<div class="adp-layout-with-sidebar__main">
					<div class="adp-entry-content">
						<?php the_content(); ?>
					</div>
					<?php wp_link_pages(); ?>
				</div>

				<?php if ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
					<aside class="adp-sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'apk-directory-pro' ); ?>">
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
