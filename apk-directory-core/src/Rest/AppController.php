<?php
/**
 * App REST controller.
 *
 * @package Adp\Core\Rest
 */

namespace Adp\Core\Rest;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Reviews\Schema as ReviewSchema;

defined( 'ABSPATH' ) || exit;

/**
 * REST endpoints for apps.
 */
class AppController {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
		add_action( 'rest_prepare_' . AppPostType::POST_TYPE, array( self::class, 'prepare_response' ), 10, 3 );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			'adp/v1',
			'/apps/(?P<id>\d+)/meta',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_meta' ),
					'permission_callback' => array( self::class, 'can_read_app' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
						),
					),
				),
			)
		);
	}

	/**
	 * Permission check for reading app data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public static function can_read_app( \WP_REST_Request $request ): bool {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );
		if ( ! $post || AppPostType::POST_TYPE !== $post->post_type ) {
			return false;
		}
		if ( 'publish' === $post->post_status ) {
			return true;
		}
		return current_user_can( 'read_post', $post_id );
	}

	/**
	 * Get app meta bundle.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function get_meta( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$post_id = (int) $request->get_param( 'id' );
		$fields  = \Adp\Core\Content\MetaSchema::get_fields();
		$meta    = array();

		foreach ( array_keys( $fields ) as $key ) {
			$meta[ $key ] = Meta::get( $post_id, $key );
		}

		$ratings = ReviewSchema::get_aggregate( $post_id );

		return new \WP_REST_Response(
			array(
				'id'      => $post_id,
				'meta'    => $meta,
				'ratings' => $ratings,
			),
			200
		);
	}

	/**
	 * Enrich REST response with meta summary.
	 *
	 * @param \WP_REST_Response $response Response object.
	 * @param \WP_Post          $post     Post object.
	 * @param \WP_REST_Request  $request  Request object.
	 * @return \WP_REST_Response
	 */
	public static function prepare_response( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ): \WP_REST_Response {
		unset( $request );
		$data = $response->get_data();
		$data['adp_meta'] = array(
			'package_name'    => Meta::get( $post->ID, '_adp_package_name' ),
			'current_version' => Meta::get( $post->ID, '_adp_current_version' ),
			'file_size_bytes' => Meta::get( $post->ID, '_adp_file_size_bytes' ),
			'verified'        => Meta::get( $post->ID, '_adp_verified' ),
			'status_badge'    => Meta::get( $post->ID, '_adp_status_badge' ),
		);
		$response->set_data( $data );
		return $response;
	}
}
