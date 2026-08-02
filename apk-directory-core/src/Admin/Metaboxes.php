<?php
/**
 * Classic editor metabox fallback.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Content\MetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Metaboxes for classic editor fallback.
 */
class Metaboxes {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'add_meta_boxes', array( self::class, 'register' ) );
		add_action( 'save_post_' . AppPostType::POST_TYPE, array( self::class, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	/**
	 * Enqueue metabox assets for classic editor.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || AppPostType::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( use_block_editor_for_post_type( AppPostType::POST_TYPE ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'adp-metabox-screenshots',
			ADP_CORE_URL . 'assets/admin/js/metabox-screenshots.js',
			array( 'jquery', 'media-editor' ),
			ADP_CORE_VERSION,
			true
		);
		wp_enqueue_style(
			'adp-editor-panels',
			ADP_CORE_URL . 'assets/admin/css/editor-panels.css',
			array(),
			ADP_CORE_VERSION
		);
	}

	/**
	 * Register metaboxes.
	 *
	 * @return void
	 */
	public static function register(): void {
		if ( use_block_editor_for_post_type( AppPostType::POST_TYPE ) ) {
			return;
		}

		add_meta_box(
			'adp-app-details',
			__( 'App Details', 'apk-directory-core' ),
			array( self::class, 'render' ),
			AppPostType::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'adp-screenshots',
			__( 'Screenshots', 'apk-directory-core' ),
			array( self::class, 'render_screenshots' ),
			AppPostType::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Render metabox fields.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public static function render( \WP_Post $post ): void {
		wp_nonce_field( 'adp_metabox_save', 'adp_metabox_nonce' );

		$fields = array(
			'_adp_short_description'  => array( 'label' => __( 'Short Description', 'apk-directory-core' ), 'type' => 'textarea' ),
			'_adp_package_name'       => array( 'label' => __( 'Package Name', 'apk-directory-core' ), 'type' => 'text' ),
			'_adp_current_version'    => array( 'label' => __( 'Current Version', 'apk-directory-core' ), 'type' => 'text' ),
			'_adp_android_requirement' => array( 'label' => __( 'Android Requirement', 'apk-directory-core' ), 'type' => 'text' ),
			'_adp_official_url'       => array( 'label' => __( 'Official URL', 'apk-directory-core' ), 'type' => 'url' ),
			'_adp_store_url'          => array( 'label' => __( 'Store URL', 'apk-directory-core' ), 'type' => 'url' ),
			'_adp_disclaimer_note'    => array( 'label' => __( 'Disclaimer Note', 'apk-directory-core' ), 'type' => 'textarea' ),
		);

		echo '<table class="form-table"><tbody>';
		foreach ( $fields as $key => $field ) {
			$value = Meta::get( $post->ID, $key );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
			if ( 'textarea' === $field['type'] ) {
				printf(
					'<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>',
					esc_attr( $key ),
					esc_attr( $key ),
					esc_textarea( (string) $value )
				);
			} else {
				printf(
					'<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />',
					esc_attr( 'url' === $field['type'] ? 'url' : 'text' ),
					esc_attr( $key ),
					esc_attr( $key ),
					esc_attr( (string) $value )
				);
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Render screenshot gallery metabox.
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public static function render_screenshots( \WP_Post $post ): void {
		$ids = Meta::get( $post->ID, '_adp_screenshot_ids' );
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}
		$ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
		?>
		<div id="adp-metabox-screenshots" class="adp-screenshot-gallery" data-ids="<?php echo esc_attr( wp_json_encode( $ids ) ); ?>">
			<p>
				<button type="button" class="button" id="adp-metabox-screenshots-add">
					<?php esc_html_e( 'Add Screenshots', 'apk-directory-core' ); ?>
				</button>
			</p>
			<ul class="adp-screenshot-gallery__list" id="adp-metabox-screenshots-list">
				<?php foreach ( $ids as $index => $attachment_id ) : ?>
					<?php
					$thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
					$title = get_the_title( $attachment_id );
					?>
					<li class="adp-screenshot-gallery__item" data-id="<?php echo esc_attr( (string) $attachment_id ); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="adp-screenshot-gallery__thumb" />
						<?php else : ?>
							<span class="adp-screenshot-gallery__placeholder">#<?php echo esc_html( (string) $attachment_id ); ?></span>
						<?php endif; ?>
						<span class="screen-reader-text"><?php echo esc_html( $title ); ?></span>
						<div class="adp-screenshot-gallery__actions">
							<button type="button" class="button button-small adp-screenshot-up" <?php disabled( 0 === $index ); ?>>&uarr;</button>
							<button type="button" class="button button-small adp-screenshot-down" <?php disabled( $index === count( $ids ) - 1 ); ?>>&darr;</button>
							<button type="button" class="button button-small adp-screenshot-remove">&times;</button>
						</div>
						<input type="hidden" name="_adp_screenshot_ids[]" value="<?php echo esc_attr( (string) $attachment_id ); ?>" />
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="description"><?php esc_html_e( 'Drag to reorder or use the arrow buttons.', 'apk-directory-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Save metabox data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! isset( $_POST['adp_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['adp_metabox_nonce'] ) ), 'adp_metabox_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$schema = MetaSchema::get_fields();
		foreach ( array_keys( $schema ) as $key ) {
			if ( '_adp_screenshot_ids' === $key ) {
				continue;
			}
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$value = wp_unslash( $_POST[ $key ] );
			if ( is_callable( $schema[ $key ]['sanitize_callback'] ) ) {
				$value = call_user_func( $schema[ $key ]['sanitize_callback'], $value );
			}
			Meta::update( $post_id, $key, $value );
		}

		if ( isset( $_POST['_adp_screenshot_ids'] ) && is_array( $_POST['_adp_screenshot_ids'] ) ) {
			$ids = array_map( 'intval', wp_unslash( $_POST['_adp_screenshot_ids'] ) );
			$ids = array_values( array_filter( $ids ) );
			if ( is_callable( $schema['_adp_screenshot_ids']['sanitize_callback'] ) ) {
				$ids = call_user_func( $schema['_adp_screenshot_ids']['sanitize_callback'], $ids );
			}
			Meta::update( $post_id, '_adp_screenshot_ids', $ids );
		} else {
			Meta::update( $post_id, '_adp_screenshot_ids', array() );
		}
	}
}
