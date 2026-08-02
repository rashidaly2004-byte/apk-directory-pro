<?php
/**
 * 404 template.
 *
 * @package AdpTheme
 */

get_header();
?>

<div class="adp-container adp-404">
	<div class="adp-404__content">
		<h1 class="adp-404__title"><?php esc_html_e( 'Page not found', 'apk-directory-pro' ); ?></h1>
		<p class="adp-404__message"><?php esc_html_e( 'The page you are looking for might have been removed or is temporarily unavailable.', 'apk-directory-pro' ); ?></p>

		<form role="search" method="get" class="adp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label for="adp-search-404" class="adp-sr-only"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></label>
			<input type="search" id="adp-search-404" class="adp-search-form__input" name="s" placeholder="<?php esc_attr_e( 'Search apps…', 'apk-directory-pro' ); ?>">
			<?php if ( post_type_exists( 'adp_app' ) ) : ?>
				<input type="hidden" name="post_type" value="adp_app">
			<?php endif; ?>
			<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></button>
		</form>
	</div>

	<?php if ( taxonomy_exists( 'adp_app_category' ) ) : ?>
		<section class="adp-404__section">
			<h2 class="adp-section-title"><?php esc_html_e( 'Popular categories', 'apk-directory-pro' ); ?></h2>
			<div class="adp-category-pills">
				<?php
				$categories = get_terms( array(
					'taxonomy'   => 'adp_app_category',
					'hide_empty' => true,
					'number'     => 8,
					'orderby'    => 'count',
					'order'      => 'DESC',
				) );
				if ( $categories && ! is_wp_error( $categories ) ) :
					foreach ( $categories as $cat ) :
						?>
						<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="adp-pill"><?php echo esc_html( $cat->name ); ?></a>
						<?php
					endforeach;
				endif;
				?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( post_type_exists( 'adp_app' ) ) : ?>
		<section class="adp-404__section">
			<h2 class="adp-section-title"><?php esc_html_e( 'Recent apps', 'apk-directory-pro' ); ?></h2>
			<div class="adp-app-list adp-app-list--list">
				<?php
				$recent = Adp\Theme\query_apps( array( 'posts_per_page' => 6 ) );
				if ( $recent->have_posts() ) :
					while ( $recent->have_posts() ) :
						$recent->the_post();
						get_template_part( 'template-parts/cards/app-row' );
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php
get_footer();
