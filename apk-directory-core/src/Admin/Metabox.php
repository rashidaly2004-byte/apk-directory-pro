<?php

namespace APD\Core\Admin;

use APD\Core\Content\Meta;

final class Metabox {

	public function register(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_adp_app', [ $this, 'save_meta' ], 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'adp_app_details',
			__( 'App Details', 'apk-directory-pro' ),
			[ $this, 'render_details' ],
			'adp_app',
			'normal',
			'high'
		);
	}

	public function render_details( \WP_Post $post ): void {
		wp_nonce_field( 'adp_save_meta', 'adp_meta_nonce' );

		$fields = [
			'short_description'   => __( 'Short description', 'apk-directory-pro' ),
			'package_name'        => __( 'Package name', 'apk-directory-pro' ),
			'current_version'     => __( 'Current version', 'apk-directory-pro' ),
			'android_requirement' => __( 'Android requirement', 'apk-directory-pro' ),
			'store_url'           => __( 'Store URL', 'apk-directory-pro' ),
			'official_url'        => __( 'Official URL', 'apk-directory-pro' ),
		];

		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$value = Meta::get( $post->ID, $key, '' );
			echo '<tr><th><label for="adp_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
			echo '<td><input type="text" id="adp_' . esc_attr( $key ) . '" name="adp_' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text" /></td></tr>';
		}
		echo '</table>';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['adp_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adp_meta_nonce'] ) ), 'adp_save_meta' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$meta = new Meta();
		$defs = $meta->get_field_definitions();

		foreach ( $defs as $key => $config ) {
			$field = 'adp_' . $key;
			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}
			$raw   = wp_unslash( $_POST[ $field ] );
			$clean = isset( $config['sanitize_callback'] ) && is_callable( $config['sanitize_callback'] )
				? call_user_func( $config['sanitize_callback'], $raw )
				: sanitize_text_field( $raw );
			update_post_meta( $post_id, '_adp_' . $key, $clean );
		}
	}
}
