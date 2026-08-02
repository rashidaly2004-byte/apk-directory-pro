<?php
/**
 * Taxonomy registration.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Registers app taxonomies.
 */
class Taxonomies {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
		// Prefer /category/app/{term}/ over core /category/{name}/ which would
		// otherwise swallow paths like category/app/tools/.
		add_action( 'init', array( self::class, 'register_priority_rewrites' ), 20 );
	}

	/**
	 * Register top-priority rewrite rules for app category archives.
	 *
	 * @return void
	 */
	public static function register_priority_rewrites(): void {
		add_rewrite_rule(
			'^category/app/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$',
			'index.php?adp_app_category=$matches[1]&feed=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^category/app/(.+?)/(feed|rdf|rss|rss2|atom)/?$',
			'index.php?adp_app_category=$matches[1]&feed=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^category/app/(.+?)/page/?([0-9]{1,})/?$',
			'index.php?adp_app_category=$matches[1]&paged=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'^category/app/(.+?)/?$',
			'index.php?adp_app_category=$matches[1]',
			'top'
		);
	}

	/**
	 * Register all taxonomies.
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_category();
		self::register_developer();
		self::register_platform();
		self::register_tag();
	}

	/**
	 * Register app category taxonomy.
	 *
	 * @return void
	 */
	private static function register_category(): void {
		register_taxonomy(
			'adp_app_category',
			AppPostType::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'App Categories', 'apk-directory-core' ),
					'singular_name' => __( 'App Category', 'apk-directory-core' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'         => 'category/app',
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	/**
	 * Register developer taxonomy.
	 *
	 * @return void
	 */
	private static function register_developer(): void {
		register_taxonomy(
			'adp_developer',
			AppPostType::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Developers', 'apk-directory-core' ),
					'singular_name' => __( 'Developer', 'apk-directory-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => 'developer',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Register platform taxonomy.
	 *
	 * @return void
	 */
	private static function register_platform(): void {
		register_taxonomy(
			'adp_platform',
			AppPostType::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Platforms', 'apk-directory-core' ),
					'singular_name' => __( 'Platform', 'apk-directory-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array(
					'slug'       => 'platform',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Register app tag taxonomy.
	 *
	 * @return void
	 */
	private static function register_tag(): void {
		register_taxonomy(
			'adp_tag',
			AppPostType::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'App Tags', 'apk-directory-core' ),
					'singular_name' => __( 'App Tag', 'apk-directory-core' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_in_rest'      => true,
				'show_admin_column' => false,
				'rewrite'           => array(
					'slug'       => 'app-tag',
					'with_front' => false,
				),
			)
		);
	}
}
