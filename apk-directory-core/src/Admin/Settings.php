<?php
/**
 * Plugin settings page.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Settings API registration.
 */
class Settings {

	public const OPTION = 'adp_core_settings';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_init', array( self::class, 'register' ) );
	}

	/**
	 * Add settings submenu.
	 *
	 * @return void
	 */
	public static function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'APK Directory Settings', 'apk-directory-core' ),
			__( 'Settings', 'apk-directory-core' ),
			'manage_options',
			'adp-core-settings',
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public static function register(): void {
		register_setting(
			'adp_core_settings_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'adp_downloads',
			__( 'Downloads', 'apk-directory-core' ),
			'__return_false',
			'adp-core-settings'
		);

		add_settings_field(
			'local_uploads_enabled',
			__( 'Local APK Uploads', 'apk-directory-core' ),
			array( self::class, 'render_checkbox' ),
			'adp-core-settings',
			'adp_downloads',
			array( 'key' => 'local_uploads_enabled', 'label' => __( 'Enable local APK uploads via Media Library', 'apk-directory-core' ) )
		);

		add_settings_field(
			'default_download_type',
			__( 'Default Download Type', 'apk-directory-core' ),
			array( self::class, 'render_select' ),
			'adp-core-settings',
			'adp_downloads',
			array(
				'key'     => 'default_download_type',
				'options' => array(
					'media'    => __( 'Media (local upload)', 'apk-directory-core' ),
					'external' => __( 'External URL', 'apk-directory-core' ),
					'redirect' => __( 'Redirect', 'apk-directory-core' ),
				),
			)
		);

		add_settings_field(
			'download_token_ttl',
			__( 'Download Token TTL (seconds)', 'apk-directory-core' ),
			array( self::class, 'render_number' ),
			'adp-core-settings',
			'adp_downloads',
			array( 'key' => 'download_token_ttl', 'min' => 60, 'max' => 86400 )
		);

		add_settings_field(
			'reviews_enabled',
			__( 'Reviews', 'apk-directory-core' ),
			array( self::class, 'render_checkbox' ),
			'adp-core-settings',
			'adp_downloads',
			array( 'key' => 'reviews_enabled', 'label' => __( 'Enable user reviews', 'apk-directory-core' ) )
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'local_uploads_enabled'    => true,
			'default_download_type'    => 'media',
			'download_token_ttl'       => 3600,
			'external_url_allowlist'   => array(),
			'download_countdown'       => false,
			'reviews_enabled'          => true,
			'view_counter_enabled'     => false,
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( mixed $input ): array {
		$defaults = self::defaults();
		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		return array(
			'local_uploads_enabled'  => ! empty( $input['local_uploads_enabled'] ),
			'default_download_type'  => in_array( $input['default_download_type'] ?? 'media', array( 'media', 'external', 'redirect' ), true )
				? $input['default_download_type']
				: 'media',
			'download_token_ttl'     => max( 60, min( 86400, (int) ( $input['download_token_ttl'] ?? 3600 ) ) ),
			'external_url_allowlist' => array_filter( array_map( 'sanitize_text_field', (array) ( $input['external_url_allowlist'] ?? array() ) ) ),
			'download_countdown'     => ! empty( $input['download_countdown'] ),
			'reviews_enabled'        => ! empty( $input['reviews_enabled'] ),
			'view_counter_enabled'   => ! empty( $input['view_counter_enabled'] ),
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'APK Directory Settings', 'apk-directory-core' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'adp_core_settings_group' );
				do_settings_sections( 'adp-core-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array<string, mixed> $args Field args.
	 * @return void
	 */
	public static function render_checkbox( array $args ): void {
		$settings = get_option( self::OPTION, self::defaults() );
		$key      = $args['key'];
		$checked  = ! empty( $settings[ $key ] );
		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s /> %s</label>',
			esc_attr( self::OPTION ),
			esc_attr( $key ),
			checked( $checked, true, false ),
			esc_html( $args['label'] ?? '' )
		);
	}

	/**
	 * Render select field.
	 *
	 * @param array<string, mixed> $args Field args.
	 * @return void
	 */
	public static function render_select( array $args ): void {
		$settings = get_option( self::OPTION, self::defaults() );
		$key      = $args['key'];
		$value    = $settings[ $key ] ?? '';
		echo '<select name="' . esc_attr( self::OPTION ) . '[' . esc_attr( $key ) . ']">';
		foreach ( $args['options'] as $opt_value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Render number field.
	 *
	 * @param array<string, mixed> $args Field args.
	 * @return void
	 */
	public static function render_number( array $args ): void {
		$settings = get_option( self::OPTION, self::defaults() );
		$key      = $args['key'];
		$value    = (int) ( $settings[ $key ] ?? 0 );
		printf(
			'<input type="number" name="%s[%s]" value="%d" min="%d" max="%d" />',
			esc_attr( self::OPTION ),
			esc_attr( $key ),
			$value,
			(int) ( $args['min'] ?? 0 ),
			(int) ( $args['max'] ?? 99999 )
		);
	}

	/**
	 * Get a setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		$settings = get_option( self::OPTION, self::defaults() );
		return $settings[ $key ] ?? $default;
	}
}
