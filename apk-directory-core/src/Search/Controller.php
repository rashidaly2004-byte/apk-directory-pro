<?php
/**
 * Live search REST controller.
 *
 * @package Adp\Core\Search
 */

namespace Adp\Core\Search;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Support\Formatter;
use Adp\Core\Support\RateLimiter;

defined( 'ABSPATH' ) || exit;

/**
 * Search endpoint with caching and rate limiting.
 */
class Controller {

	public const MAX_RESULTS = 10;

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/search',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'search' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'q' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => static fn( mixed $v ): bool => is_string( $v ) && strlen( trim( $v ) ) >= 2,
						),
					),
				),
			)
		);
	}

	/**
	 * Perform search.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function search( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$query = trim( sanitize_text_field( (string) $request->get_param( 'q' ) ) );
		if ( strlen( $query ) < 2 ) {
			return new \WP_Error( 'adp_query_too_short', __( 'Query must be at least 2 characters.', 'apk-directory-core' ), array( 'status' => 400 ) );
		}

		$rate_key = RateLimiter::key( 'search' );
		if ( ! RateLimiter::attempt( $rate_key, 30, MINUTE_IN_SECONDS ) ) {
			return new \WP_Error( 'adp_rate_limited', __( 'Too many search requests.', 'apk-directory-core' ), array( 'status' => 429 ) );
		}

		$cache_key = 'adp_search_' . md5( strtolower( $query ) );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$results = self::execute_search( $query );
		$response = array(
			'query'   => $query,
			'results' => $results,
		);

		set_transient( $cache_key, $response, 5 * MINUTE_IN_SECONDS );

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Execute search query.
	 *
	 * @param string $query Search query.
	 * @return array<int, array<string, mixed>>
	 */
	private static function execute_search( string $query ): array {
		$results = array();
		$seen    = array();

		// Title search first.
		$title_query = new \WP_Query(
			array(
				'post_type'              => array( AppPostType::POST_TYPE, 'post' ),
				'post_status'            => 'publish',
				's'                      => $query,
				'posts_per_page'         => self::MAX_RESULTS,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => true,
			)
		);

		foreach ( $title_query->posts as $post ) {
			$seen[ $post->ID ] = true;
			$results[]         = self::format_result( $post, $query, 'title' );
		}

		// Package name / developer search if room remains.
		if ( count( $results ) < self::MAX_RESULTS ) {
			$remaining = self::MAX_RESULTS - count( $results );

			$meta_query = new \WP_Query(
				array(
					'post_type'      => AppPostType::POST_TYPE,
					'post_status'    => 'publish',
					'posts_per_page' => $remaining + count( $seen ),
					'no_found_rows'  => true,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						array(
							'key'     => '_adp_package_name',
							'value'   => $query,
							'compare' => 'LIKE',
						),
					),
				)
			);

			foreach ( $meta_query->posts as $post ) {
				if ( isset( $seen[ $post->ID ] ) ) {
					continue;
				}
				if ( count( $results ) >= self::MAX_RESULTS ) {
					break;
				}
				$seen[ $post->ID ] = true;
				$results[]         = self::format_result( $post, $query, 'package' );
			}
		}

		// Developer taxonomy search.
		if ( count( $results ) < self::MAX_RESULTS ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'adp_developer',
					'name__like' => $query,
					'hide_empty' => true,
					'number'     => 5,
				)
			);

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
				$dev_query = new \WP_Query(
					array(
						'post_type'      => AppPostType::POST_TYPE,
						'post_status'    => 'publish',
						'posts_per_page' => self::MAX_RESULTS - count( $results ),
						'no_found_rows'  => true,
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => 'adp_developer',
								'field'    => 'term_id',
								'terms'    => $term_ids,
							),
						),
						'post__not_in'   => array_keys( $seen ),
					)
				);

				foreach ( $dev_query->posts as $post ) {
					if ( count( $results ) >= self::MAX_RESULTS ) {
						break;
					}
					$results[] = self::format_result( $post, $query, 'developer' );
				}
			}
		}

		return $results;
	}

	/**
	 * Format a search result item.
	 *
	 * @param \WP_Post $post    Post object.
	 * @param string   $query   Search query.
	 * @param string   $matched Match type.
	 * @return array<string, mixed>
	 */
	private static function format_result( \WP_Post $post, string $query, string $matched ): array {
		$is_app = AppPostType::POST_TYPE === $post->post_type;
		$developers = $is_app ? wp_get_post_terms( $post->ID, 'adp_developer', array( 'fields' => 'names' ) ) : array();
		$categories = $is_app ? wp_get_post_terms( $post->ID, 'adp_app_category', array( 'fields' => 'names' ) ) : array();

		return array(
			'id'         => $post->ID,
			'title'      => Formatter::highlight( get_the_title( $post ), $query ),
			'url'        => get_permalink( $post ),
			'type'       => $is_app ? 'app' : 'post',
			'type_label' => $is_app ? __( 'App', 'apk-directory-core' ) : __( 'Blog', 'apk-directory-core' ),
			'icon'       => $is_app ? get_the_post_thumbnail_url( $post->ID, 'thumbnail' ) : get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
			'developer'  => ! is_wp_error( $developers ) && ! empty( $developers ) ? $developers[0] : '',
			'category'   => ! is_wp_error( $categories ) && ! empty( $categories ) ? $categories[0] : '',
			'version'    => $is_app ? (string) Meta::get( $post->ID, '_adp_current_version' ) : '',
			'matched'    => $matched,
		);
	}
}
