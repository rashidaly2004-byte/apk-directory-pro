<?php

namespace APD\Core\Admin;

use APD\Core\Content\Meta;

final class EditorPanels {

	public function register(): void {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets(): void {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'adp_app' ) {
			return;
		}

		$asset = ADP_CORE_PATH . 'assets/js/editor.js';
		if ( file_exists( $asset ) ) {
			wp_enqueue_script(
				'adp-editor',
				ADP_CORE_URL . 'assets/js/editor.js',
				[ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ],
				ADP_CORE_VERSION,
				true
			);
		}
	}
}
