<?php
/**
 * Theme helper functions.
 *
 * @package AdpTheme
 */

declare(strict_types=1);

namespace Adp\Theme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if the companion APK Directory plugin is active.
 */
function is_plugin_active_check(): bool {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return post_type_exists( 'adp_app' ) || (
		function_exists( 'is_plugin_active' ) && is_plugin_active( 'apk-directory-core/apk-directory-core.php' )
	);
}

/**
 * Format bytes to human-readable size.
 */
function format_bytes( int|float $bytes, int $precision = 1 ): string {
	if ( $bytes <= 0 ) {
		return '—';
	}

	$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	$pow   = (int) floor( log( $bytes, 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );
	$bytes = $bytes / ( 1024 ** $pow );

	return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}

/**
 * Get app card data for a post.
 *
 * @return array<string, mixed>
 */
function get_app_card_data( int|\WP_Post $post ): array {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$post_id = $post->ID;

	$data = array(
		'id'          => $post_id,
		'title'       => get_the_title( $post ),
		'permalink'   => get_permalink( $post ),
		'icon'        => get_the_post_thumbnail_url( $post, 'adp-app-icon' ) ?: '',
		'developer'   => '',
		'category'    => '',
		'version'     => '',
		'size'        => '',
		'updated'     => '',
		'rating'      => 0.0,
		'rating_count' => 0,
		'has_rating'  => false,
		'downloads'   => 0,
	);

	// Meta from companion plugin or fallbacks.
	$version = get_post_meta( $post_id, '_adp_current_version', true );
	if ( ! $version ) {
		$version = get_post_meta( $post_id, '_adp_version', true );
	}
	if ( ! $version ) {
		$version = get_post_meta( $post_id, 'adp_version', true );
	}
	$data['version'] = is_string( $version ) ? $version : '';

	$size = get_post_meta( $post_id, '_adp_file_size_bytes', true );
	if ( ! $size ) {
		$size = get_post_meta( $post_id, '_adp_file_size', true );
	}
	if ( ! $size ) {
		$size = get_post_meta( $post_id, 'adp_file_size', true );
	}
	if ( is_numeric( $size ) ) {
		$data['size'] = format_bytes( (float) $size );
	} elseif ( is_string( $size ) && $size !== '' ) {
		$data['size'] = $size;
	}

	$modified = get_post_modified_time( 'U', false, $post );
	$data['updated'] = $modified ? human_time_diff( $modified, time() ) . ' ' . __( 'ago', 'apk-directory-pro' ) : '';

	// User ratings only for aggregate stars — never invent totals.
	if ( class_exists( '\Adp\Core\Reviews\Schema' ) ) {
		$aggregate = \Adp\Core\Reviews\Schema::get_aggregate( $post_id );
		if ( $aggregate['count'] > 0 ) {
			$data['rating']       = (float) $aggregate['average'];
			$data['rating_count'] = (int) $aggregate['count'];
			$data['has_rating']   = true;
		}
	}

	// Editorial score is separate and labeled at render time.
	$editor_rating = get_post_meta( $post_id, '_adp_editor_rating', true );
	$data['editor_rating'] = ( is_numeric( $editor_rating ) && (float) $editor_rating > 0 )
		? (float) $editor_rating
		: 0.0;

	$downloads = get_post_meta( $post_id, '_adp_download_count', true );
	if ( ! $downloads ) {
		$downloads = get_post_meta( $post_id, '_adp_downloads', true );
	}
	if ( ! $downloads ) {
		$downloads = get_post_meta( $post_id, 'adp_downloads', true );
	}
	$data['downloads'] = is_numeric( $downloads ) ? (int) $downloads : 0;

	$dev_terms = get_the_terms( $post_id, 'adp_developer' );
	if ( $dev_terms && ! is_wp_error( $dev_terms ) ) {
		$data['developer'] = $dev_terms[0]->name;
	}

	$cat_terms = get_the_terms( $post_id, 'adp_app_category' );
	if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
		$data['category'] = $cat_terms[0]->name;
	}

	return $data;
}

/**
 * Query apps with common args.
 *
 * @param array<string, mixed> $args Query overrides.
 * @return \WP_Query
 */
function query_apps( array $args = array() ): \WP_Query {
	$defaults = array(
		'post_type'      => 'adp_app',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'no_found_rows'  => false,
	);

	if ( ! post_type_exists( 'adp_app' ) ) {
		$defaults['post_type'] = 'post';
	}

	return new \WP_Query( array_merge( $defaults, $args ) );
}

/**
 * Get Customizer setting with default.
 */
function get_theme_mod_string( string $key, string $default = '' ): string {
	$value = get_theme_mod( $key, $default );
	return is_string( $value ) ? $value : $default;
}

/**
 * Get Customizer boolean setting.
 */
function get_theme_mod_bool( string $key, bool $default = false ): bool {
	return (bool) get_theme_mod( $key, $default );
}

/**
 * Get Customizer integer setting.
 */
function get_theme_mod_int( string $key, int $default = 0 ): int {
	return (int) get_theme_mod( $key, $default );
}

/**
 * Render breadcrumbs navigation.
 */
function render_breadcrumbs(): void {
	get_template_part( 'template-parts/navigation/breadcrumbs' );
}

/**
 * Get homepage section order from Customizer.
 *
 * @return array<int, string>
 */
function get_homepage_sections(): array {
	$default = array(
		'hero-search',
		'featured-categories',
		'trending',
		'latest-updates',
		'latest-games',
		'editors-choice',
		'popular',
		'latest-blog',
		'seo-intro',
	);

	$order = get_theme_mod( 'adp_homepage_sections_order', implode( ',', $default ) );
	if ( ! is_string( $order ) || $order === '' ) {
		return $default;
	}

	$sections = array_map( 'trim', explode( ',', $order ) );
	$sections = array_filter( $sections );

	return ! empty( $sections ) ? array_values( $sections ) : $default;
}

/**
 * Check if a homepage section is enabled.
 */
function is_homepage_section_enabled( string $section ): bool {
	return get_theme_mod_bool( 'adp_homepage_section_' . str_replace( '-', '_', $section ), true );
}

/**
 * Get homepage section count.
 */
function get_homepage_section_count( string $section, int $default = 8 ): int {
	return get_theme_mod_int( 'adp_homepage_count_' . str_replace( '-', '_', $section ), $default );
}

/**
 * Get SVG icon markup.
 */
function get_icon( string $name, array $attrs = array() ): string {
	$path = ADP_THEME_DIR . '/assets/icons/' . sanitize_file_name( $name ) . '.svg';
	if ( ! file_exists( $path ) ) {
		return '';
	}

	$svg = file_get_contents( $path );
	if ( $svg === false ) {
		return '';
	}

	$defaults = array(
		'class'       => 'adp-icon adp-icon--' . esc_attr( $name ),
		'aria-hidden' => 'true',
		'focusable'   => 'false',
	);

	$attrs = array_merge( $defaults, $attrs );

	$attr_string = '';
	foreach ( $attrs as $key => $value ) {
		$attr_string .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
	}

	return preg_replace( '/<svg/', '<svg' . $attr_string, $svg, 1 ) ?? $svg;
}

/**
 * Echo SVG icon.
 */
function icon( string $name, array $attrs = array() ): void {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from theme files.
	echo get_icon( $name, $attrs );
}

/**
 * Get archive sort options.
 *
 * @return array<string, string>
 */
function get_archive_sort_options(): array {
	return array(
		'newest'   => __( 'Newest', 'apk-directory-pro' ),
		'updated'  => __( 'Recently Updated', 'apk-directory-pro' ),
		'popular'  => __( 'Most Popular', 'apk-directory-pro' ),
		'rating'   => __( 'Top Rated', 'apk-directory-pro' ),
		'title'    => __( 'A–Z', 'apk-directory-pro' ),
	);
}

/**
 * Build archive query args from GET params.
 *
 * @return array<string, mixed>
 */
function get_archive_query_args(): array {
	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';
	$paged = max( 1, (int) ( $_GET['paged'] ?? get_query_var( 'paged', 1 ) ) );

	$args = array(
		'paged'          => $paged,
		'posts_per_page' => get_theme_mod_int( 'adp_archive_per_page', 24 ),
	);

	switch ( $sort ) {
		case 'updated':
			$args['orderby'] = 'modified';
			$args['order']   = 'DESC';
			break;
		case 'popular':
			$args['meta_key'] = '_adp_download_count';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'rating':
			$args['meta_key'] = '_adp_editor_rating';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'title':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
	}

	$tax_query = array();

	if ( ! empty( $_GET['category'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'adp_app_category',
			'field'    => 'slug',
			'terms'    => sanitize_title( wp_unslash( $_GET['category'] ) ),
		);
	}

	if ( ! empty( $_GET['platform'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'adp_platform',
			'field'    => 'slug',
			'terms'    => sanitize_title( wp_unslash( $_GET['platform'] ) ),
		);
	}

	if ( ! empty( $_GET['price'] ) ) {
		$price = sanitize_key( wp_unslash( $_GET['price'] ) );
		if ( in_array( $price, array( 'free', 'paid' ), true ) ) {
			$args['meta_query'][] = array(
				'key'   => '_adp_price_type',
				'value' => $price,
			);
		}
	}

	if ( ! empty( $_GET['file_type'] ) ) {
		$args['meta_query'][] = array(
			'key'   => '_adp_file_type',
			'value' => sanitize_text_field( wp_unslash( $_GET['file_type'] ) ),
		);
	}

	if ( ! empty( $_GET['android'] ) ) {
		$args['meta_query'][] = array(
			'key'     => '_adp_min_android',
			'value'   => sanitize_text_field( wp_unslash( $_GET['android'] ) ),
			'compare' => '<=',
			'type'    => 'NUMERIC',
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query;
	}

	return $args;
}

/**
 * Get ad slot content sanitized by capability.
 */
function get_ad_slot( string $slot ): string {
	$setting = 'adp_ad_' . $slot;
	$content = get_theme_mod( $setting, '' );

	if ( ! is_string( $content ) || $content === '' ) {
		return '';
	}

	if ( current_user_can( 'unfiltered_html' ) ) {
		return $content;
	}

	return wp_kses_post( $content );
}

/**
 * Render star rating display.
 */
function render_star_rating( float $rating, int $count = 0 ): void {
	if ( $rating <= 0 ) {
		return;
	}
	?>
	<div class="adp-rating" aria-label="<?php echo esc_attr( sprintf(
		/* translators: 1: rating value, 2: number of reviews */
		__( 'Rated %1$s out of 5 from %2$d reviews', 'apk-directory-pro' ),
		number_format( $rating, 1 ),
		$count
	) ); ?>">
		<span class="adp-rating__stars" aria-hidden="true">
			<?php
			for ( $i = 1; $i <= 5; $i++ ) {
				$filled = $rating >= $i - 0.25;
				icon( $filled ? 'star-filled' : 'star', array( 'class' => 'adp-icon adp-icon--star' ) );
			}
			?>
		</span>
		<span class="adp-rating__value"><?php echo esc_html( number_format( $rating, 1 ) ); ?></span>
		<?php if ( $count > 0 ) : ?>
			<span class="adp-rating__count">(<?php echo esc_html( number_format_i18n( $count ) ); ?>)</span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Get app meta value with fallbacks.
 */
function get_app_meta( int $post_id, string $key, string $default = '' ): string {
	$map = array(
		'version'      => '_adp_current_version',
		'file_size'    => '_adp_file_size_bytes',
		'download_url' => '',
	);

	if ( 'download_url' === $key ) {
		return get_app_download_url( $post_id );
	}

	$meta_key = $map[ $key ] ?? ( '_adp_' . $key );
	$value    = get_post_meta( $post_id, $meta_key, true );
	if ( $value === '' || $value === false ) {
		$value = get_post_meta( $post_id, 'adp_' . $key, true );
	}
	if ( is_numeric( $value ) ) {
		return (string) $value;
	}
	return is_string( $value ) && $value !== '' ? $value : $default;
}

/**
 * Get signed download URL for the current app version.
 */
function get_app_download_url( int $post_id ): string {
	if ( class_exists( '\Adp\Core\Blocks\Helpers' ) ) {
		return \Adp\Core\Blocks\Helpers::get_download_url( $post_id );
	}
	return '';
}

/**
 * Get public versions archive URL for an app.
 */
function get_app_versions_url( int $post_id ): string {
	if ( class_exists( '\Adp\Core\Versions\Frontend' ) ) {
		return \Adp\Core\Versions\Frontend::get_versions_url( $post_id );
	}
	$post = get_post( $post_id );
	return $post ? user_trailingslashit( home_url( 'app/' . $post->post_name . '/versions' ) ) : '';
}

/**
 * Render a single review comment.
 *
 * @param \WP_Comment $comment Comment object.
 * @param array<string, mixed> $args Comment args.
 * @param int $depth Thread depth.
 */
function render_review_comment( \WP_Comment $comment, array $args, int $depth ): void {
	unset( $args, $depth );
	$rating = (int) get_comment_meta( $comment->comment_ID, 'adp_rating', true );
	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'adp-reviews__item' ); ?>>
		<article class="adp-review">
			<header class="adp-review__header">
				<strong class="adp-review__author"><?php comment_author(); ?></strong>
				<?php if ( $rating > 0 ) : ?>
					<?php render_star_rating( (float) $rating ); ?>
				<?php endif; ?>
				<time class="adp-review__date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
					<?php echo esc_html( get_comment_date() ); ?>
				</time>
			</header>
			<div class="adp-review__content"><?php comment_text(); ?></div>
		</article>
	</li>
	<?php
}

/**
 * Format download count.
 */
function format_downloads( int $count ): string {
	if ( $count <= 0 ) {
		return '—';
	}
	if ( $count >= 1000000 ) {
		return round( $count / 1000000, 1 ) . 'M';
	}
	if ( $count >= 1000 ) {
		return round( $count / 1000, 1 ) . 'K';
	}
	return number_format_i18n( $count );
}
