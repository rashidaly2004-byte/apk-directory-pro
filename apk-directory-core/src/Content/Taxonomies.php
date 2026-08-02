<?php

namespace APD\Core\Content;

final class Taxonomies {

	public function register(): void {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'adp_app_category_add_form_fields', array( $this, 'term_add_fields' ) );
		add_action( 'adp_app_category_edit_form_fields', array( $this, 'term_edit_fields' ) );
		add_action( 'created_adp_app_category', array( $this, 'save_term_meta' ) );
		add_action( 'edited_adp_app_category', array( $this, 'save_term_meta' ) );
	}

	public function register_taxonomies(): void {
		$category_base = get_option( 'adp_category_base', 'category/app' );

		register_taxonomy(
			'adp_app_category',
			AppPostType::POST_TYPE,
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'          => __( 'App Categories', 'apk-directory-pro' ),
					'singular_name' => __( 'App Category', 'apk-directory-pro' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => $category_base,
					'with_front' => false,
				),
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'adp_developer',
			AppPostType::POST_TYPE,
			array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'          => __( 'Developers', 'apk-directory-pro' ),
					'singular_name' => __( 'Developer', 'apk-directory-pro' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'developer',
					'with_front' => false,
				),
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'adp_platform',
			AppPostType::POST_TYPE,
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'          => __( 'Platforms', 'apk-directory-pro' ),
					'singular_name' => __( 'Platform', 'apk-directory-pro' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'platform',
					'with_front' => false,
				),
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'adp_tag',
			AppPostType::POST_TYPE,
			array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'          => __( 'App Tags', 'apk-directory-pro' ),
					'singular_name' => __( 'App Tag', 'apk-directory-pro' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array(
					'slug'       => 'app-tag',
					'with_front' => false,
				),
				'show_in_rest'      => true,
			)
		);
	}

	public function term_add_fields(): void {
		?>
		<div class="form-field">
			<label for="adp_term_color"><?php esc_html_e( 'Color', 'apk-directory-pro' ); ?></label>
			<input type="text" name="adp_term_color" id="adp_term_color" value="" />
		</div>
		<div class="form-field">
			<label for="adp_term_short_desc"><?php esc_html_e( 'Short description', 'apk-directory-pro' ); ?></label>
			<textarea name="adp_term_short_desc" id="adp_term_short_desc" rows="3"></textarea>
		</div>
		<?php
	}

	public function term_edit_fields( \WP_Term $term ): void {
		$color      = get_term_meta( $term->term_id, '_adp_term_color', true );
		$short_desc = get_term_meta( $term->term_id, '_adp_term_short_desc', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="adp_term_color"><?php esc_html_e( 'Color', 'apk-directory-pro' ); ?></label></th>
			<td><input type="text" name="adp_term_color" id="adp_term_color" value="<?php echo esc_attr( $color ); ?>" /></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="adp_term_short_desc"><?php esc_html_e( 'Short description', 'apk-directory-pro' ); ?></label></th>
			<td><textarea name="adp_term_short_desc" id="adp_term_short_desc" rows="3"><?php echo esc_textarea( $short_desc ); ?></textarea></td>
		</tr>
		<?php
	}

	public function save_term_meta( int $term_id ): void {
		if ( isset( $_POST['adp_term_color'] ) ) {
			update_term_meta( $term_id, '_adp_term_color', sanitize_hex_color( wp_unslash( $_POST['adp_term_color'] ) ) );
		}
		if ( isset( $_POST['adp_term_short_desc'] ) ) {
			update_term_meta( $term_id, '_adp_term_short_desc', sanitize_text_field( wp_unslash( $_POST['adp_term_short_desc'] ) ) );
		}
	}
}
