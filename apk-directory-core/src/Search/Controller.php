<?php

namespace APD\Core\Search;

use APD\Core\Content\AppPostType;
use APD\Core\Content\Meta;
use APD\Core\Support\RateLimiter;

final class Controller {

	private const MAX_RESULTS = 10;

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/search',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $value ) {
							return strlen( $value ) >= 2;
						},
					),
				),
			)
		);
	}

	public function search( \WP_REST_Request $request ): \WP_REST_Response {
		if ( ! $this->check_rate_limit() ) {
			return new \WP_REST_Response( array( 'error' => 'rate_limited' ), 429 );
		}

		$query     = sanitize_text_field( $request->get_param( 'q' ) );
		$cache_key = 'adp_search_' . md5( $query );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return new \WP_REST_Response( $cached, 200 );
		}

		$apps = new \WP_Query(
			array(
				'post_type'      => AppPostType::POST_TYPE,
				'post_status'    => 'publish',
				's'              => $query,
				'posts_per_page' => self::MAX_RESULTS,
				'no_found_rows'  => true,
			)
		);

		$results = array();
		foreach ( $apps->posts as $post ) {
			$developer = '';
			$dev_terms = get_the_terms( $post->ID, 'adp_developer' );
			if ( $dev_terms && ! is_wp_error( $dev_terms ) ) {
				$developer = $dev_terms[0]->name;
			}

			$results[] = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'url'       => get_permalink( $post ),
				'type'      => 'app',
				'developer' => $developer,
				'version'   => Meta::get( $post->ID, 'current_version', '' ),
				'icon'      => get_the_post_thumbnail_url( $post, 'thumbnail' ),
			);
		}

		$posts = new \WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				's'              => $query,
				'posts_per_page' => 3,
				'no_found_rows'  => true,
			)
		);

		foreach ( $posts->posts as $post ) {
			$results[] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'url'   => get_permalink( $post ),
				'type'  => 'post',
			);
		}

		$response = array( 'results' => array_slice( $results, 0, self::MAX_RESULTS ) );
		set_transient( $cache_key, $response, 60 );

		return new \WP_REST_Response( $response, 200 );
	}

	private function check_rate_limit(): bool {
		/**
		 * Filters the suggestion requests allowed per window. Set to 0 to
		 * disable the limit.
		 */
		$limit = (int) apply_filters( 'adp_search_rate_limit', 30 );

		/** Filters the search rate limit window, in seconds. */
		$window = (int) apply_filters( 'adp_search_rate_window', 60 );

		return RateLimiter::hit( 'search', $limit, $window );
	}
}
