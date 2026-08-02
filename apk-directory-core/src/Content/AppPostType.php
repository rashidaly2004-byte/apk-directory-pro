<?php
/**
 * App custom post type registration.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the adp_app post type.
 */
class AppPostType {

	public const POST_TYPE = 'adp_app';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
		add_filter( 'post_type_link', array( self::class, 'filter_permalink' ), 10, 2 );
	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public static function register(): void {
		$labels = array(
			'name'                  => __( 'Apps', 'apk-directory-core' ),
			'singular_name'         => __( 'App', 'apk-directory-core' ),
			'menu_name'             => __( 'Apps', 'apk-directory-core' ),
			'add_new'               => __( 'Add New', 'apk-directory-core' ),
			'add_new_item'          => __( 'Add New App', 'apk-directory-core' ),
			'edit_item'             => __( 'Edit App', 'apk-directory-core' ),
			'new_item'              => __( 'New App', 'apk-directory-core' ),
			'view_item'             => __( 'View App', 'apk-directory-core' ),
			'view_items'            => __( 'View Apps', 'apk-directory-core' ),
			'search_items'          => __( 'Search Apps', 'apk-directory-core' ),
			'not_found'             => __( 'No apps found.', 'apk-directory-core' ),
			'not_found_in_trash'    => __( 'No apps found in Trash.', 'apk-directory-core' ),
			'all_items'             => __( 'All Apps', 'apk-directory-core' ),
			'archives'              => __( 'App Archives', 'apk-directory-core' ),
			'attributes'            => __( 'App Attributes', 'apk-directory-core' ),
			'insert_into_item'      => __( 'Insert into app', 'apk-directory-core' ),
			'uploaded_to_this_item' => __( 'Uploaded to this app', 'apk-directory-core' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-smartphone',
				'menu_position'       => 5,
				'has_archive'         => 'apps',
				'rewrite'             => array(
					'slug'       => 'app',
					'with_front' => false,
				),
				'capability_type'     => array( 'adp_app', 'adp_apps' ),
				'map_meta_cap'        => true,
				'supports'            => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'author',
					'revisions',
					'comments',
					'custom-fields',
				),
				'taxonomies'          => array(
					'adp_app_category',
					'adp_developer',
					'adp_platform',
					'adp_tag',
				),
				'show_in_nav_menus'   => true,
				'exclude_from_search' => false,
			)
		);
	}

	/**
	 * Filter permalink structure.
	 *
	 * @param string   $post_link Permalink.
	 * @param \WP_Post $post      Post object.
	 * @return string
	 */
	public static function filter_permalink( string $post_link, \WP_Post $post ): string {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $post_link;
		}
		return home_url( user_trailingslashit( 'app/' . $post->post_name ) );
	}
}
