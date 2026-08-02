<?php
/**
 * App archive template.
 *
 * @package AdpTheme
 */

get_header();

$view    = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : Adp\Theme\get_theme_mod_string( 'adp_archive_default_view', 'list' );
$view    = in_array( $view, array( 'list', 'grid' ), true ) ? $view : 'list';
$sort    = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';
?>

<div class="adp-container adp-archive">
	<?php Adp\Theme\render_breadcrumbs(); ?>

	<header class="adp-archive-header">
		<h1 class="adp-archive-header__title">
			<?php
			if ( is_post_type_archive( 'adp_app' ) ) {
				esc_html_e( 'All Apps', 'apk-directory-pro' );
			} else {
				the_archive_title();
			}
			?>
		</h1>
		<?php the_archive_description( '<div class="adp-archive-header__desc">', '</div>' ); ?>
	</header>

	<?php
	$ad = Adp\Theme\get_ad_slot( 'archive_top' );
	if ( $ad ) {
		echo '<div class="adp-ad-slot adp-ad-slot--archive-top">' . $ad . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>

	<div class="adp-archive-controls" data-adp-filters>
		<form class="adp-filters-form" method="get" action="">
			<div class="adp-filters-form__row">
				<label for="adp-sort" class="adp-sr-only"><?php esc_html_e( 'Sort by', 'apk-directory-pro' ); ?></label>
				<select id="adp-sort" name="sort" class="adp-select" data-adp-auto-submit>
					<?php foreach ( Adp\Theme\get_archive_sort_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $sort, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<?php if ( taxonomy_exists( 'adp_app_category' ) ) : ?>
					<?php
					$categories = get_terms( array( 'taxonomy' => 'adp_app_category', 'hide_empty' => true ) );
					if ( $categories && ! is_wp_error( $categories ) ) :
						$current_cat = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
						?>
						<label for="adp-filter-category" class="adp-sr-only"><?php esc_html_e( 'Category', 'apk-directory-pro' ); ?></label>
						<select id="adp-filter-category" name="category" class="adp-select" data-adp-auto-submit>
							<option value=""><?php esc_html_e( 'All categories', 'apk-directory-pro' ); ?></option>
							<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $current_cat, $cat->slug ); ?>><?php echo esc_html( $cat->name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( taxonomy_exists( 'adp_platform' ) ) : ?>
					<?php
					$platforms = get_terms( array( 'taxonomy' => 'adp_platform', 'hide_empty' => true ) );
					if ( $platforms && ! is_wp_error( $platforms ) ) :
						$current_platform = isset( $_GET['platform'] ) ? sanitize_title( wp_unslash( $_GET['platform'] ) ) : '';
						?>
						<label for="adp-filter-platform" class="adp-sr-only"><?php esc_html_e( 'Platform', 'apk-directory-pro' ); ?></label>
						<select id="adp-filter-platform" name="platform" class="adp-select" data-adp-auto-submit>
							<option value=""><?php esc_html_e( 'All platforms', 'apk-directory-pro' ); ?></option>
							<?php foreach ( $platforms as $platform ) : ?>
								<option value="<?php echo esc_attr( $platform->slug ); ?>" <?php selected( $current_platform, $platform->slug ); ?>><?php echo esc_html( $platform->name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				<?php endif; ?>

				<label for="adp-filter-price" class="adp-sr-only"><?php esc_html_e( 'Price', 'apk-directory-pro' ); ?></label>
				<select id="adp-filter-price" name="price" class="adp-select" data-adp-auto-submit>
					<?php $current_price = isset( $_GET['price'] ) ? sanitize_key( wp_unslash( $_GET['price'] ) ) : ''; ?>
					<option value=""><?php esc_html_e( 'Any price', 'apk-directory-pro' ); ?></option>
					<option value="free" <?php selected( $current_price, 'free' ); ?>><?php esc_html_e( 'Free', 'apk-directory-pro' ); ?></option>
					<option value="paid" <?php selected( $current_price, 'paid' ); ?>><?php esc_html_e( 'Paid', 'apk-directory-pro' ); ?></option>
				</select>
			</div>
		</form>

		<div class="adp-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View mode', 'apk-directory-pro' ); ?>">
			<a href="<?php echo esc_url( add_query_arg( 'view', 'list' ) ); ?>" class="adp-view-toggle__btn <?php echo $view === 'list' ? 'is-active' : ''; ?>" aria-pressed="<?php echo $view === 'list' ? 'true' : 'false'; ?>">
				<?php esc_html_e( 'List', 'apk-directory-pro' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( 'view', 'grid' ) ); ?>" class="adp-view-toggle__btn <?php echo $view === 'grid' ? 'is-active' : ''; ?>" aria-pressed="<?php echo $view === 'grid' ? 'true' : 'false'; ?>">
				<?php esc_html_e( 'Grid', 'apk-directory-pro' ); ?>
			</a>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="adp-app-list adp-app-list--<?php echo esc_attr( $view ); ?>" data-adp-app-list>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( $view === 'grid' ) {
					get_template_part( 'template-parts/cards/app-grid-item' );
				} else {
					get_template_part( 'template-parts/cards/app-row' );
				}
			endwhile;
			?>
		</div>

		<?php get_template_part( 'template-parts/navigation/pagination' ); ?>

		<?php if ( Adp\Theme\get_theme_mod_bool( 'adp_archive_load_more', true ) && $wp_query->max_num_pages > 1 ) : ?>
			<div class="adp-load-more-wrap">
				<button type="button" class="adp-btn adp-btn--secondary adp-load-more" data-adp-load-more data-page="1" data-max="<?php echo esc_attr( (string) $wp_query->max_num_pages ); ?>">
					<?php esc_html_e( 'Load more', 'apk-directory-pro' ); ?>
				</button>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content/none' ); ?>
	<?php endif; ?>
</div>

<?php
wp_reset_postdata();
get_footer();
