<?php

namespace APD\Core\Admin;

final class Settings {

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'APK Directory Settings', 'apk-directory-pro' ),
			__( 'Settings', 'apk-directory-pro' ),
			'manage_options',
			'adp-settings',
			[ $this, 'render_page' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'adp_settings', 'adp_category_base', [
			'type'              => 'string',
			'default'           => 'category/app',
			'sanitize_callback' => 'sanitize_title',
		] );
		register_setting( 'adp_settings', 'adp_allow_mod_content', [
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		] );
		register_setting( 'adp_settings', 'adp_download_countdown', [
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		] );
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'APK Directory Settings', 'apk-directory-pro' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'adp_settings' );
				do_settings_sections( 'adp_settings' );
				?>
				<table class="form-table">
					<tr>
						<th><label for="adp_category_base"><?php esc_html_e( 'Category URL base', 'apk-directory-pro' ); ?></label></th>
						<td><input type="text" id="adp_category_base" name="adp_category_base" value="<?php echo esc_attr( get_option( 'adp_category_base', 'category/app' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Allow MOD content fields', 'apk-directory-pro' ); ?></th>
						<td><input type="checkbox" name="adp_allow_mod_content" value="1" <?php checked( get_option( 'adp_allow_mod_content' ) ); ?> /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Download countdown', 'apk-directory-pro' ); ?></th>
						<td><input type="checkbox" name="adp_download_countdown" value="1" <?php checked( get_option( 'adp_download_countdown' ) ); ?> /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
