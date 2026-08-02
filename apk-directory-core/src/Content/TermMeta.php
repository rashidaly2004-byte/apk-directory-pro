<?php
/**
 * Taxonomy term meta registration.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

use Adp\Core\Support\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Registers term meta for app taxonomies.
 */
class TermMeta {

	public const TAXONOMIES = array(
		'adp_app_category',
		'adp_developer',
		'adp_platform',
		'adp_tag',
	);

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( self::class, 'register' ) );
		add_action( 'adp_app_category_add_form_fields', array( self::class, 'render_add_fields' ) );
		add_action( 'adp_app_category_edit_form_fields', array( self::class, 'render_edit_fields' ), 10, 2 );
		add_action( 'created_adp_app_category', array( self::class, 'save_term_meta' ) );
		add_action( 'edited_adp_app_category', array( self::class, 'save_term_meta' ) );

		foreach ( array( 'adp_developer', 'adp_platform', 'adp_tag' ) as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( self::class, 'render_add_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( self::class, 'render_edit_fields' ), 10, 2 );
			add_action( "created_{$taxonomy}", array( self::class, 'save_term_meta' ) );
			add_action( "edited_{$taxonomy}", array( self::class, 'save_term_meta' ) );
		}
	}

	/**
	 * Register term meta keys.
	 *
	 * @return void
	 */
	public static function register(): void {
		$fields = self::get_schema();

		foreach ( self::TAXONOMIES as $taxonomy ) {
			foreach ( $fields as $key => $config ) {
				register_term_meta(
					$taxonomy,
					$key,
					array(
						'type'              => $config['type'],
						'description'       => $config['description'],
						'single'            => true,
						'show_in_rest'      => true,
						'default'           => $config['default'],
						'sanitize_callback' => $config['sanitize_callback'],
						'auth_callback'     => static fn(): bool => current_user_can( 'manage_categories' ),
					)
				);
			}
		}
	}

	/**
	 * Term meta schema.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_schema(): array {
		return array(
			'adp_icon_id'          => array(
				'type'              => 'integer',
				'description'       => __( 'Icon attachment ID.', 'apk-directory-core' ),
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
			),
			'adp_color'            => array(
				'type'              => 'string',
				'description'       => __( 'Term color hex.', 'apk-directory-core' ),
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'hex_color' ),
			),
			'adp_short_description' => array(
				'type'              => 'string',
				'description'       => __( 'Short description.', 'apk-directory-core' ),
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'adp_seo_intro'        => array(
				'type'              => 'string',
				'description'       => __( 'SEO introduction text.', 'apk-directory-core' ),
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
			),
			'adp_featured'         => array(
				'type'              => 'boolean',
				'description'       => __( 'Featured term flag.', 'apk-directory-core' ),
				'default'           => false,
				'sanitize_callback' => array( Sanitizer::class, 'bool' ),
			),
			'adp_display_order'    => array(
				'type'              => 'integer',
				'description'       => __( 'Display order.', 'apk-directory-core' ),
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
			),
		);
	}

	/**
	 * Render add term fields.
	 *
	 * @return void
	 */
	public static function render_add_fields(): void {
		wp_nonce_field( 'adp_term_meta', 'adp_term_meta_nonce' );
		?>
		<div class="form-field">
			<label for="adp_color"><?php esc_html_e( 'Color', 'apk-directory-core' ); ?></label>
			<input type="text" name="adp_color" id="adp_color" value="" placeholder="#18a957" />
		</div>
		<div class="form-field">
			<label for="adp_short_description"><?php esc_html_e( 'Short Description', 'apk-directory-core' ); ?></label>
			<textarea name="adp_short_description" id="adp_short_description" rows="3"></textarea>
		</div>
		<div class="form-field">
			<label for="adp_display_order"><?php esc_html_e( 'Display Order', 'apk-directory-core' ); ?></label>
			<input type="number" name="adp_display_order" id="adp_display_order" value="0" min="0" />
		</div>
		<div class="form-field">
			<label>
				<input type="checkbox" name="adp_featured" value="1" />
				<?php esc_html_e( 'Featured', 'apk-directory-core' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Render edit term fields.
	 *
	 * @param \WP_Term $term     Term object.
	 * @param string   $taxonomy Taxonomy slug.
	 * @return void
	 */
	public static function render_edit_fields( \WP_Term $term, string $taxonomy ): void {
		unset( $taxonomy );
		wp_nonce_field( 'adp_term_meta', 'adp_term_meta_nonce' );
		$color       = get_term_meta( $term->term_id, 'adp_color', true );
		$short_desc  = get_term_meta( $term->term_id, 'adp_short_description', true );
		$order       = (int) get_term_meta( $term->term_id, 'adp_display_order', true );
		$featured    = (bool) get_term_meta( $term->term_id, 'adp_featured', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="adp_color"><?php esc_html_e( 'Color', 'apk-directory-core' ); ?></label></th>
			<td><input type="text" name="adp_color" id="adp_color" value="<?php echo esc_attr( (string) $color ); ?>" /></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="adp_short_description"><?php esc_html_e( 'Short Description', 'apk-directory-core' ); ?></label></th>
			<td><textarea name="adp_short_description" id="adp_short_description" rows="3"><?php echo esc_textarea( (string) $short_desc ); ?></textarea></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="adp_display_order"><?php esc_html_e( 'Display Order', 'apk-directory-core' ); ?></label></th>
			<td><input type="number" name="adp_display_order" id="adp_display_order" value="<?php echo esc_attr( (string) $order ); ?>" min="0" /></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'Featured', 'apk-directory-core' ); ?></th>
			<td><label><input type="checkbox" name="adp_featured" value="1" <?php checked( $featured ); ?> /> <?php esc_html_e( 'Featured', 'apk-directory-core' ); ?></label></td>
		</tr>
		<?php
	}

	/**
	 * Save term meta from admin form.
	 *
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public static function save_term_meta( int $term_id ): void {
		if ( ! isset( $_POST['adp_term_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['adp_term_meta_nonce'] ) ), 'adp_term_meta' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		$schema = self::get_schema();
		foreach ( $schema as $key => $config ) {
			$post_key = str_replace( 'adp_', 'adp_', $key );
			if ( 'adp_featured' === $key ) {
				$value = isset( $_POST['adp_featured'] );
			} elseif ( isset( $_POST[ $post_key ] ) ) {
				$value = wp_unslash( $_POST[ $post_key ] );
				if ( is_callable( $config['sanitize_callback'] ) ) {
					$value = call_user_func( $config['sanitize_callback'], $value );
				}
			} else {
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}
	}
}
