<?php
/**
 * 404 template.
 */

get_header();
status_header( 404 );
?>
<main id="main-content" class="adp-main adp-container adp-404">
	<h1 class="adp-page-title"><?php esc_html_e( 'Page not found', 'apk-directory-pro' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for does not exist.', 'apk-directory-pro' ); ?></p>

	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="adp-search-form adp-404-search">
		<label for="adp-404-search"><?php esc_html_e( 'Search apps', 'apk-directory-pro' ); ?></label>
		<input type="search" id="adp-404-search" name="s" placeholder="<?php esc_attr_e( 'Search…', 'apk-directory-pro' ); ?>" />
		<button type="submit" class="adp-btn"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></button>
	</form>

	<section class="adp-section">
		<h2><?php esc_html_e( 'Recent apps', 'apk-directory-pro' ); ?></h2>
		<div class="adp-app-list">
			<?php
			$recent = adp_query_apps( [ 'posts_per_page' => 6 ] );
			while ( $recent->have_posts() ) :
				$recent->the_post();
				get_template_part( 'template-parts/cards/app-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</section>
</main>
<?php
get_footer();
