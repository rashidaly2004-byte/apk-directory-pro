<?php
/**
 * App archive.
 */

get_header();

$sort    = sanitize_text_field( $_GET['sort'] ?? 'newest' );
$orderby = 'date';
$meta_key = '';
$order   = 'DESC';

switch ( $sort ) {
	case 'updated':
		$orderby  = 'meta_value';
		$meta_key = '_adp_updated_date';
		break;
	case 'popular':
		$orderby  = 'meta_value_num';
		$meta_key = '_adp_download_count';
		break;
	case 'name':
		$orderby = 'title';
		$order   = 'ASC';
		break;
}

$query_args = [
	'post_type'      => 'adp_app',
	'post_status'    => 'publish',
	'posts_per_page' => 24,
	'paged'          => max( 1, get_query_var( 'paged' ) ),
	'orderby'        => $orderby,
	'order'          => $order,
];
if ( $meta_key ) {
	$query_args['meta_key'] = $meta_key;
}

if ( ! empty( $_GET['category'] ) ) {
	$query_args['tax_query'] = [
		[
			'taxonomy' => 'adp_app_category',
			'field'    => 'slug',
			'terms'    => sanitize_text_field( wp_unslash( $_GET['category'] ) ),
		],
	];
}

$apps = new WP_Query( $query_args );
?>
<main id="main-content" class="adp-main adp-container">
	<?php do_action( 'adp_before_content' ); ?>
	<h1 class="adp-page-title"><?php post_type_archive_title(); ?></h1>

	<div class="adp-archive-controls">
		<form class="adp-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>">
			<label for="adp-sort"><?php esc_html_e( 'Sort', 'apk-directory-pro' ); ?></label>
			<select name="sort" id="adp-sort">
				<option value="newest" <?php selected( $sort, 'newest' ); ?>><?php esc_html_e( 'Newest', 'apk-directory-pro' ); ?></option>
				<option value="updated" <?php selected( $sort, 'updated' ); ?>><?php esc_html_e( 'Recently updated', 'apk-directory-pro' ); ?></option>
				<option value="popular" <?php selected( $sort, 'popular' ); ?>><?php esc_html_e( 'Popular', 'apk-directory-pro' ); ?></option>
				<option value="name" <?php selected( $sort, 'name' ); ?>><?php esc_html_e( 'A–Z', 'apk-directory-pro' ); ?></option>
			</select>
			<button type="submit" class="adp-btn adp-btn--secondary"><?php esc_html_e( 'Apply', 'apk-directory-pro' ); ?></button>
		</form>
		<div class="adp-layout-toggle" role="group" aria-label="<?php esc_attr_e( 'Layout', 'apk-directory-pro' ); ?>">
			<button type="button" class="adp-btn-icon" data-layout="list" aria-pressed="true" aria-label="<?php esc_attr_e( 'List view', 'apk-directory-pro' ); ?>">☰</button>
			<button type="button" class="adp-btn-icon" data-layout="grid" aria-pressed="false" aria-label="<?php esc_attr_e( 'Grid view', 'apk-directory-pro' ); ?>">▦</button>
		</div>
	</div>

	<?php if ( $apps->have_posts() ) : ?>
		<div class="adp-app-list" data-layout="list">
			<?php while ( $apps->have_posts() ) : $apps->the_post(); ?>
				<?php get_template_part( 'template-parts/cards/app-card' ); ?>
			<?php endwhile; ?>
		</div>
		<?php
		echo paginate_links( [
			'total'   => $apps->max_num_pages,
			'current' => max( 1, get_query_var( 'paged' ) ),
		] );
		?>
	<?php else : ?>
		<div class="adp-empty-state">
			<p><?php esc_html_e( 'No apps found.', 'apk-directory-pro' ); ?></p>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>" class="adp-btn"><?php esc_html_e( 'Clear filters', 'apk-directory-pro' ); ?></a>
		</div>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</main>
<?php
get_footer();
