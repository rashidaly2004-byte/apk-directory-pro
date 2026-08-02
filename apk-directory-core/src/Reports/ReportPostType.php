<?php
/**
 * Report private post type.
 *
 * @package Adp\Core\Reports
 */

namespace Adp\Core\Reports;

defined( 'ABSPATH' ) || exit;

/**
 * Registers private adp_report CPT.
 */
class ReportPostType {

	public const POST_TYPE = 'adp_report';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'App Reports', 'apk-directory-core' ),
					'singular_name' => __( 'App Report', 'apk-directory-core' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=adp_app',
				'show_in_rest'        => false,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'editor' ),
				'has_archive'         => false,
				'exclude_from_search' => true,
			)
		);
	}
}
