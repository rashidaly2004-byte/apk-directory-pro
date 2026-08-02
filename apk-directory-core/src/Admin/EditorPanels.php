<?php

namespace APD\Core\Admin;

use APD\Core\Content\AppPostType;
use APD\Core\Content\Meta;

final class EditorPanels {

	public function register(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== AppPostType::POST_TYPE ) {
			return;
		}

		wp_enqueue_script(
			'adp-editor',
			ADP_CORE_URL . 'assets/js/editor.js',
			array(
				'wp-plugins',
				'wp-edit-post',
				'wp-element',
				'wp-components',
				'wp-data',
				'wp-i18n',
			),
			ADP_CORE_VERSION,
			true
		);
	}
}
