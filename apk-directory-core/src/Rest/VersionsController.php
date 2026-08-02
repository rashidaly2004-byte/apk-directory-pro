<?php
/**
 * Versions REST controller.
 *
 * @package Adp\Core\Rest
 */

namespace Adp\Core\Rest;

use Adp\Core\Content\AppPostType;
use Adp\Core\Downloads\HashService;
use Adp\Core\Versions\Service;

defined( 'ABSPATH' ) || exit;

/**
 * REST endpoints for version management.
 */
class VersionsController {

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
		$app_id_arg = array(
			'app_id' => array(
				'required'          => true,
				'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
			),
		);

		register_rest_route(
			'adp/v1',
			'/apps/(?P<app_id>\d+)/versions',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'list_versions' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => $app_id_arg,
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'create_version' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => $app_id_arg,
				),
			)
		);

		register_rest_route(
			'adp/v1',
			'/apps/(?P<app_id>\d+)/versions/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( self::class, 'update_version' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => array_merge(
						$app_id_arg,
						array(
							'id' => array(
								'required'          => true,
								'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
							),
						)
					),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( self::class, 'delete_version' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => array_merge(
						$app_id_arg,
						array(
							'id' => array(
								'required'          => true,
								'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
							),
							'delete_attachment' => array(
								'type'    => 'boolean',
								'default' => false,
							),
						)
					),
				),
			)
		);

		register_rest_route(
			'adp/v1',
			'/apps/(?P<app_id>\d+)/versions/(?P<id>\d+)/duplicate',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'duplicate_version' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => array_merge(
						$app_id_arg,
						array(
							'id' => array(
								'required'          => true,
								'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
							),
						)
					),
				),
			)
		);

		register_rest_route(
			'adp/v1',
			'/apps/(?P<app_id>\d+)/versions/(?P<id>\d+)/set-current',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'set_current' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => array_merge(
						$app_id_arg,
						array(
							'id' => array(
								'required'          => true,
								'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
							),
						)
					),
				),
			)
		);

		register_rest_route(
			'adp/v1',
			'/apps/(?P<app_id>\d+)/versions/attachment/(?P<attachment_id>\d+)/hash',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_attachment_hash' ),
					'permission_callback' => array( self::class, 'can_manage_versions' ),
					'args'                => array_merge(
						$app_id_arg,
						array(
							'attachment_id' => array(
								'required'          => true,
								'validate_callback' => static fn( mixed $v ): bool => is_numeric( $v ) && (int) $v > 0,
							),
						)
					),
				),
			)
		);
	}

	/**
	 * Check permission to manage versions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool
	 */
	public static function can_manage_versions( \WP_REST_Request $request ): bool {
		$app_id = (int) $request->get_param( 'app_id' );
		if ( AppPostType::POST_TYPE !== get_post_type( $app_id ) ) {
			return false;
		}
		return current_user_can( 'edit_post', $app_id );
	}

	/**
	 * List versions for an app.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function list_versions( \WP_REST_Request $request ): \WP_REST_Response {
		$service  = new Service();
		$app_id   = (int) $request->get_param( 'app_id' );
		$versions = $service->get_repository()->list_by_app( $app_id );
		return new \WP_REST_Response( array( 'versions' => $versions ), 200 );
	}

	/**
	 * Create a version.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function create_version( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service = new Service();
		$data    = $request->get_json_params();
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$data['app_id'] = (int) $request->get_param( 'app_id' );

		$result = $service->create( $data );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		self::maybe_hash_version( (int) $result, $data );

		$version = $service->get_repository()->find( $result );
		return new \WP_REST_Response( array( 'version' => $version ), 201 );
	}

	/**
	 * Update a version.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function update_version( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service = new Service();
		$id      = (int) $request->get_param( 'id' );
		$data    = $request->get_json_params();
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$result = $service->update( $id, $data );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		self::maybe_hash_version( $id, $data );

		$version = $service->get_repository()->find( $id );
		return new \WP_REST_Response( array( 'version' => $version ), 200 );
	}

	/**
	 * Delete a version.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function delete_version( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service           = new Service();
		$id                = (int) $request->get_param( 'id' );
		$delete_attachment = (bool) $request->get_param( 'delete_attachment' );

		$result = $service->delete( $id, $delete_attachment );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response( array( 'deleted' => true ), 200 );
	}

	/**
	 * Duplicate a version.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function duplicate_version( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service = new Service();
		$id      = (int) $request->get_param( 'id' );
		$result  = $service->duplicate( $id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$version = $service->get_repository()->find( $result );
		return new \WP_REST_Response( array( 'version' => $version ), 201 );
	}

	/**
	 * Set version as current.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function set_current( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service = new Service();
		$id      = (int) $request->get_param( 'id' );
		$result  = $service->set_current( $id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$version = $service->get_repository()->find( $id );
		return new \WP_REST_Response( array( 'version' => $version ), 200 );
	}

	/**
	 * Compute SHA-256 for a media attachment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function get_attachment_hash( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$attachment_id = (int) $request->get_param( 'attachment_id' );
		if ( 'attachment' !== get_post_type( $attachment_id ) ) {
			return new \WP_Error( 'adp_invalid_attachment', __( 'Invalid attachment.', 'apk-directory-core' ), array( 'status' => 404 ) );
		}

		$hash_service = new HashService();
		$hash         = $hash_service->compute_for_attachment( $attachment_id );

		return new \WP_REST_Response(
			array(
				'attachment_id' => $attachment_id,
				'hash'          => $hash,
				'status'        => null !== $hash ? 'complete' : 'pending',
			),
			200
		);
	}

	/**
	 * Hash local attachment after version save when applicable.
	 *
	 * @param int                  $version_id Version ID.
	 * @param array<string, mixed> $data       Submitted data.
	 * @return void
	 */
	private static function maybe_hash_version( int $version_id, array $data ): void {
		$download_type = $data['download_type'] ?? '';
		$attachment_id = ! empty( $data['attachment_id'] ) ? (int) $data['attachment_id'] : 0;

		if ( 'media' !== $download_type || $attachment_id <= 0 ) {
			return;
		}

		$hash_service = new HashService();
		$hash_service->update_version_hash( $version_id, $attachment_id );
	}
}
