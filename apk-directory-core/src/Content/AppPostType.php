<?php

namespace APD\Core\Content;

final class AppPostType {

	public const POST_TYPE = 'adp_app';

	public function register(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'enter_title_here', [ $this, 'title_placeholder' ], 10, 2 );
	}

	public function register_post_type(): void {
		$labels = [
			'name'               => __( 'Apps', 'apk-directory-pro' ),
			'singular_name'      => __( 'App', 'apk-directory-pro' ),
			'menu_name'          => __( 'Apps', 'apk-directory-pro' ),
			'add_new'            => __( 'Add New', 'apk-directory-pro' ),
			'add_new_item'       => __( 'Add New App', 'apk-directory-pro' ),
			'edit_item'          => __( 'Edit App', 'apk-directory-pro' ),
			'new_item'           => __( 'New App', 'apk-directory-pro' ),
			'view_item'          => __( 'View App', 'apk-directory-pro' ),
			'search_items'       => __( 'Search Apps', 'apk-directory-pro' ),
			'not_found'          => __( 'No apps found.', 'apk-directory-pro' ),
			'not_found_in_trash' => __( 'No apps found in Trash.', 'apk-directory-pro' ),
			'all_items'          => __( 'All Apps', 'apk-directory-pro' ),
		];

		register_post_type(
			self::POST_TYPE,
			[
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-smartphone',
				'menu_position'       => 5,
				'query_var'           => true,
				'rewrite'             => [
					'slug'       => 'app',
					'with_front' => false,
				],
				'has_archive'         => 'apps',
				'hierarchical'        => false,
				'supports'            => [
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'author',
					'revisions',
					'comments',
					'custom-fields',
				],
				'show_in_rest'        => true,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'taxonomies'          => [
					'adp_app_category',
					'adp_developer',
					'adp_platform',
					'adp_tag',
				],
			]
		);
	}

	public function title_placeholder( string $title, \WP_Post $post ): string {
		if ( self::POST_TYPE === $post->post_type ) {
			return __( 'App name', 'apk-directory-pro' );
		}
		return $title;
	}
}
