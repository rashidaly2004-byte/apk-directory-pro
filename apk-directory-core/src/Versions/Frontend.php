<?php
/**
 * Public versions archive page.
 *
 * @package Adp\Core\Versions
 */

namespace Adp\Core\Versions;

use Adp\Core\Content\AppPostType;
use Adp\Core\Downloads\Signer;

defined( 'ABSPATH' ) || exit;

/**
 * Registers /app/{slug}/versions/ rewrite and renders version history.
 */
class Frontend {

	public const PER_PAGE = 20;

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register_rewrite' ) );
		add_filter( 'query_vars', array( self::class, 'add_query_vars' ) );
		add_filter( 'template_include', array( self::class, 'load_template' ) );
		add_action( 'wp', array( self::class, 'setup_query' ) );
		add_filter( 'document_title_parts', array( self::class, 'filter_title' ) );
		add_filter( 'adp_theme_breadcrumbs', array( self::class, 'filter_breadcrumbs' ) );
		add_action( 'wp_head', array( self::class, 'output_canonical' ), 1 );
	}

	/**
	 * Register versions rewrite rule.
	 *
	 * @return void
	 */
	public static function register_rewrite(): void {
		add_rewrite_rule(
			'^app/([^/]+)/versions/?$',
			'index.php?adp_versions_page=1&adp_app_slug=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array<int, string> $vars Query vars.
	 * @return array<int, string>
	 */
	public static function add_query_vars( array $vars ): array {
		$vars[] = 'adp_versions_page';
		$vars[] = 'adp_app_slug';
		return $vars;
	}

	/**
	 * Resolve app post from slug query var.
	 *
	 * @return \WP_Post|null
	 */
	public static function get_app_post(): ?\WP_Post {
		$slug = get_query_var( 'adp_app_slug' );
		if ( empty( $slug ) ) {
			return null;
		}

		$post = get_page_by_path( sanitize_title( (string) $slug ), OBJECT, AppPostType::POST_TYPE );
		return $post instanceof \WP_Post ? $post : null;
	}

	/**
	 * Setup main query context for versions page.
	 *
	 * @return void
	 */
	public static function setup_query(): void {
		if ( ! get_query_var( 'adp_versions_page' ) ) {
			return;
		}

		$app = self::get_app_post();
		if ( ! $app || 'publish' !== $app->post_status ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		global $wp_query;
		$wp_query->is_404      = false;
		$wp_query->is_singular = true;
		$wp_query->is_single   = true;
		$wp_query->queried_object    = $app;
		$wp_query->queried_object_id = $app->ID;
	}

	/**
	 * Load plugin or theme template.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public static function load_template( string $template ): string {
		if ( ! get_query_var( 'adp_versions_page' ) ) {
			return $template;
		}

		$app = self::get_app_post();
		if ( ! $app ) {
			return $template;
		}

		/**
		 * Filter versions page template path.
		 *
		 * @param string   $template Template path.
		 * @param \WP_Post $app      App post.
		 */
		$filtered = apply_filters( 'adp_versions_template', '', $app );
		if ( is_string( $filtered ) && '' !== $filtered && file_exists( $filtered ) ) {
			return $filtered;
		}

		$theme_template = locate_template( 'adp-versions.php' );
		if ( $theme_template ) {
			return $theme_template;
		}

		$plugin_template = ADP_CORE_PATH . 'templates/versions.php';
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return $template;
	}

	/**
	 * Filter document title.
	 *
	 * @param array<string, string> $parts Title parts.
	 * @return array<string, string>
	 */
	public static function filter_title( array $parts ): array {
		if ( ! get_query_var( 'adp_versions_page' ) ) {
			return $parts;
		}

		$app = self::get_app_post();
		if ( ! $app ) {
			return $parts;
		}

		$parts['title'] = sprintf(
			/* translators: %s: app title */
			__( '%s — All Versions', 'apk-directory-core' ),
			get_the_title( $app )
		);

		return $parts;
	}

	/**
	 * Output canonical link when no SEO plugin handles it.
	 *
	 * @return void
	 */
	public static function output_canonical(): void {
		if ( ! get_query_var( 'adp_versions_page' ) ) {
			return;
		}

		$app = self::get_app_post();
		if ( ! $app ) {
			return;
		}

		$paged = max( 1, (int) get_query_var( 'paged', 1 ) );
		$url   = self::get_versions_url( $app );
		if ( $paged > 1 ) {
			$url = add_query_arg( 'paged', $paged, $url );
		}

		echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
	}

	/**
	 * Append breadcrumb data for theme consumption.
	 *
	 * @param array<int, array<string, string>> $crumbs Breadcrumb items.
	 * @return array<int, array<string, string>>
	 */
	public static function filter_breadcrumbs( array $crumbs ): array {
		if ( ! get_query_var( 'adp_versions_page' ) ) {
			return $crumbs;
		}

		$app = self::get_app_post();
		if ( ! $app ) {
			return $crumbs;
		}

		return array(
			array(
				'label' => __( 'Home', 'apk-directory-core' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Apps', 'apk-directory-core' ),
				'url'   => get_post_type_archive_link( AppPostType::POST_TYPE ) ?: '',
			),
			array(
				'label' => get_the_title( $app ),
				'url'   => get_permalink( $app ),
			),
			array(
				'label' => __( 'All Versions', 'apk-directory-core' ),
				'url'   => '',
			),
		);
	}

	/**
	 * Get public versions page URL for an app.
	 *
	 * @param int|\WP_Post $app App post or ID.
	 * @return string
	 */
	public static function get_versions_url( int|\WP_Post $app ): string {
		$post = get_post( $app );
		if ( ! $post ) {
			return '';
		}
		return user_trailingslashit( home_url( 'app/' . $post->post_name . '/versions' ) );
	}

	/**
	 * Get paginated versions for the current request.
	 *
	 * @return array{versions: array<int, array<string, mixed>>, total: int, pages: int, page: int, app: \WP_Post|null}
	 */
	public static function get_page_data(): array {
		$app = self::get_app_post();
		if ( ! $app ) {
			return array(
				'versions' => array(),
				'total'    => 0,
				'pages'    => 0,
				'page'     => 1,
				'app'      => null,
			);
		}

		$page   = max( 1, (int) get_query_var( 'paged', 1 ) );
		$repo   = new Repository();
		$total  = $repo->count_by_app( $app->ID );
		$pages  = (int) ceil( $total / self::PER_PAGE );
		$offset = ( $page - 1 ) * self::PER_PAGE;

		return array(
			'versions' => $repo->list_by_app( $app->ID, self::PER_PAGE, $offset ),
			'total'    => $total,
			'pages'    => $pages,
			'page'     => $page,
			'app'      => $app,
		);
	}

	/**
	 * Get download URL for a version row.
	 *
	 * @param array<string, mixed> $version Version data.
	 * @return string
	 */
	public static function get_version_download_url( array $version ): string {
		$signer = new Signer();
		return $signer->get_interstitial_url( (int) $version['id'] );
	}
}
