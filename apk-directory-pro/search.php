<?php
/**
 * Search results template.
 *
 * @package AdpTheme
 */

get_header();

$search_query = get_search_query();
$post_type    = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
?>

<div class="adp-container adp-search-results">
	<?php Adp\Theme\render_breadcrumbs(); ?>

	<header class="adp-page-header">
		<h1 class="adp-page-header__title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Search results for "%s"', 'apk-directory-pro' ),
				esc_html( $search_query )
			);
			?>
		</h1>
	</header>

	<form role="search" method="get" class="adp-search-form adp-search-form--page" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label for="adp-search-page" class="adp-sr-only"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></label>
		<input type="search" id="adp-search-page" class="adp-search-form__input" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search apps…', 'apk-directory-pro' ); ?>">
		<?php if ( post_type_exists( 'adp_app' ) ) : ?>
			<input type="hidden" name="post_type" value="adp_app">
		<?php endif; ?>
		<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></button>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="adp-app-list adp-app-list--list">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( get_post_type() === 'adp_app' || post_type_exists( 'adp_app' ) ) {
					get_template_part( 'template-parts/cards/app-row' );
				} else {
					get_template_part( 'template-parts/cards/post-card' );
				}
			endwhile;
			?>
		</div>
		<?php get_template_part( 'template-parts/navigation/pagination' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>

<?php
get_footer();
