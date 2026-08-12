<?php

namespace APD\Core\Admin;

final class Settings {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'APK Directory Settings', 'apk-directory-pro' ),
			__( 'Settings', 'apk-directory-pro' ),
			'manage_options',
			'adp-settings',
			array( $this, 'render_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'adp_settings',
			'adp_category_base',
			array(
				'type'              => 'string',
				'default'           => 'category/app',
				'sanitize_callback' => 'sanitize_title',
			)
		);
		register_setting(
			'adp_settings',
			'adp_allow_mod_content',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		register_setting(
			'adp_settings',
			'adp_download_countdown',
			array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);
		register_setting(
			'adp_settings',
			'adp_trusted_proxies',
			array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_trusted_proxies' ),
			)
		);
	}

	/**
	 * Keeps only addresses, CIDR ranges, and the wildcard, one per line.
	 *
	 * @param mixed $value Submitted value.
	 */
	public function sanitize_trusted_proxies( $value ): string {
		$entries = preg_split( '/[\s,]+/', sanitize_textarea_field( (string) $value ) );
		if ( ! is_array( $entries ) ) {
			return '';
		}

		$valid = array();
		foreach ( $entries as $entry ) {
			$entry = trim( $entry );
			if ( '' === $entry ) {
				continue;
			}
			if ( '*' === $entry ) {
				$valid[] = $entry;
				continue;
			}

			$address = $entry;
			$bits    = '';
			if ( str_contains( $entry, '/' ) ) {
				[ $address, $bits ] = explode( '/', $entry, 2 );
				if ( '' === $bits || ! ctype_digit( $bits ) ) {
					continue;
				}
			}

			$binary = false !== filter_var( $address, FILTER_VALIDATE_IP ) ? inet_pton( $address ) : false;
			if ( false === $binary ) {
				continue;
			}
			if ( '' !== $bits && (int) $bits > strlen( $binary ) * 8 ) {
				continue;
			}

			$valid[] = $entry;
		}

		return implode( "\n", array_unique( $valid ) );
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
					<tr>
						<th><label for="adp_trusted_proxies"><?php esc_html_e( 'Trusted proxies', 'apk-directory-pro' ); ?></label></th>
						<td>
							<textarea id="adp_trusted_proxies" name="adp_trusted_proxies" rows="4" class="large-text code"><?php echo esc_textarea( get_option( 'adp_trusted_proxies', '' ) ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Addresses or CIDR ranges of the CDN, load balancer, or reverse proxy in front of this site, one per line. Without them every visitor arriving through the same proxy shares one rate-limit bucket, so search and reporting can fail for whole regions at once. Use * to trust any proxy, which is only safe when the origin refuses direct traffic.', 'apk-directory-pro' ); ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
