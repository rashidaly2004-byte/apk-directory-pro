<?php
/**
 * App post meta registration.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Registers _adp_* post meta for adp_app.
 */
class Meta {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ), 20 );
	}

	/**
	 * Register all post meta fields.
	 *
	 * @return void
	 */
	public static function register(): void {
		$fields = MetaSchema::get_fields();

		foreach ( $fields as $key => $config ) {
			$args = array(
				'type'              => $config['type'],
				'description'       => sprintf(
					/* translators: %s: meta key */
					__( 'App meta field: %s', 'apk-directory-core' ),
					$key
				),
				'single'            => $config['single'],
				'default'           => $config['default'],
				'sanitize_callback' => $config['sanitize_callback'],
				'auth_callback'     => $config['auth_callback'] ?? array( self::class, 'auth_callback' ),
				'show_in_rest'      => $config['show_in_rest'],
			);

			register_post_meta( AppPostType::POST_TYPE, $key, $args );
		}
	}

	/**
	 * Default auth callback for meta editing.
	 *
	 * @param bool   $allowed   Whether allowed.
	 * @param string $meta_key  Meta key.
	 * @param int    $post_id   Post ID.
	 * @return bool
	 */
	public static function auth_callback( bool $allowed, string $meta_key, int $post_id ): bool {
		unset( $allowed, $meta_key );
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get meta value with default fallback.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return mixed
	 */
	public static function get( int $post_id, string $key ): mixed {
		$value = get_post_meta( $post_id, $key, true );
		if ( '' === $value || ( is_array( $value ) && empty( $value ) ) ) {
			return MetaSchema::get_default( $key );
		}
		return $value;
	}

	/**
	 * Update meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Value.
	 * @return bool|int
	 */
	public static function update( int $post_id, string $key, mixed $value ): bool|int {
		return update_post_meta( $post_id, $key, $value );
	}
}
