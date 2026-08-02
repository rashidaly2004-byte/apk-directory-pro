<?php
/**
 * Gutenberg editor panels enqueue.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\MetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues block editor plugin scripts.
 */
class EditorPanels {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'enqueue_block_editor_assets', array( self::class, 'enqueue' ) );
	}

	/**
	 * Enqueue editor panel assets.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		$screen = get_current_screen();
		if ( ! $screen || AppPostType::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'adp-editor-panels',
			ADP_CORE_URL . 'assets/admin/js/editor-panels.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-api-fetch', 'media-editor' ),
			ADP_CORE_VERSION,
			true
		);

		wp_enqueue_media();

		wp_enqueue_style(
			'adp-editor-panels',
			ADP_CORE_URL . 'assets/admin/css/editor-panels.css',
			array(),
			ADP_CORE_VERSION
		);

		$fields = array();
		foreach ( MetaSchema::get_fields() as $key => $config ) {
			$fields[ $key ] = array(
				'type'    => $config['type'],
				'default' => $config['default'],
			);
		}

		wp_localize_script(
			'adp-editor-panels',
			'adpEditorPanels',
			array(
				'postType'  => AppPostType::POST_TYPE,
				'fields'    => $fields,
				'restUrl'   => rest_url( 'wp/v2/' . AppPostType::POST_TYPE ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'canUpload' => \Adp\Core\Content\Capabilities::can_upload_apk(),
				'i18n'      => array(
					'addScreenshots'    => __( 'Add Screenshots', 'apk-directory-core' ),
					'selectScreenshots' => __( 'Select Screenshots', 'apk-directory-core' ),
					'noScreenshots'     => __( 'No screenshots added yet.', 'apk-directory-core' ),
					'moveUp'            => __( 'Move up', 'apk-directory-core' ),
					'moveDown'          => __( 'Move down', 'apk-directory-core' ),
					'remove'            => __( 'Remove', 'apk-directory-core' ),
				),
			)
		);
	}
}
