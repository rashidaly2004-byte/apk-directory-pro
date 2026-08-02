<?php

namespace APD\Core\Versions;

use APD\Core\Downloads\Controller as DownloadController;
use APD\Core\Versions\Service;

final class Admin {

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_adp_version_action', array( $this, 'handle_ajax' ) );
	}

	public function add_meta_box(): void {
		add_meta_box(
			'adp_versions',
			__( 'Version Manager', 'apk-directory-pro' ),
			array( $this, 'render' ),
			'adp_app',
			'normal',
			'default'
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'adp_app' ) {
			return;
		}
		wp_enqueue_script( 'adp-version-manager', ADP_CORE_URL . 'assets/js/version-manager.js', array( 'jquery' ), ADP_CORE_VERSION, true );
		wp_localize_script(
			'adp-version-manager',
			'adpVersions',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'adp_version_action' ),
			)
		);
	}

	public function render( \WP_Post $post ): void {
		$repo     = new Repository();
		$versions = $repo->find_by_app( $post->ID, 50 );
		?>
		<table class="widefat striped" id="adp-version-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th>
					<th><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></th>
					<th><?php esc_html_e( 'Type', 'apk-directory-pro' ); ?></th>
					<th><?php esc_html_e( 'Current', 'apk-directory-pro' ); ?></th>
					<th><?php esc_html_e( 'Downloads', 'apk-directory-pro' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'apk-directory-pro' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $versions as $v ) : ?>
					<tr>
						<td><?php echo esc_html( $v['version_name'] ); ?></td>
						<td><?php echo esc_html( size_format( (int) $v['file_size_bytes'] ) ); ?></td>
						<td><?php echo esc_html( $v['file_type'] ); ?></td>
						<td><?php echo $v['is_current'] ? '✓' : ''; ?></td>
						<td><?php echo esc_html( (string) $v['download_count'] ); ?></td>
						<td>
							<?php if ( ! $v['is_current'] ) : ?>
								<button type="button" class="button adp-set-current" data-id="<?php echo esc_attr( $v['id'] ); ?>"><?php esc_html_e( 'Set current', 'apk-directory-pro' ); ?></button>
							<?php endif; ?>
							<button type="button" class="button adp-delete-version" data-id="<?php echo esc_attr( $v['id'] ); ?>"><?php esc_html_e( 'Delete', 'apk-directory-pro' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<button type="button" class="button" id="adp-add-version"><?php esc_html_e( 'Add Version', 'apk-directory-pro' ); ?></button>
		</p>
		<div id="adp-version-form" style="display:none; margin-top:1em; padding:1em; border:1px solid #ccc;">
			<p><label><?php esc_html_e( 'Version name', 'apk-directory-pro' ); ?> <input type="text" id="adp-v-name" /></label></p>
			<p><label><?php esc_html_e( 'File size (bytes)', 'apk-directory-pro' ); ?> <input type="number" id="adp-v-size" /></label></p>
			<p><label><?php esc_html_e( 'File type', 'apk-directory-pro' ); ?>
				<select id="adp-v-type"><option value="apk">APK</option><option value="xapk">XAPK</option></select>
			</label></p>
			<p><label><?php esc_html_e( 'External URL', 'apk-directory-pro' ); ?> <input type="url" id="adp-v-url" class="regular-text" /></label></p>
			<p><label><input type="checkbox" id="adp-v-current" /> <?php esc_html_e( 'Set as current', 'apk-directory-pro' ); ?></label></p>
			<button type="button" class="button button-primary" id="adp-save-version"><?php esc_html_e( 'Save Version', 'apk-directory-pro' ); ?></button>
		</div>
		<input type="hidden" id="adp-post-id" value="<?php echo esc_attr( (string) $post->ID ); ?>" />
		<?php
	}

	public function handle_ajax(): void {
		check_ajax_referer( 'adp_version_action', 'nonce' );

		$post_id = absint( $_POST['post_id'] ?? 0 );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}

		$action  = sanitize_text_field( wp_unslash( $_POST['version_action'] ?? '' ) );
		$repo    = new Repository();
		$service = new Service( $repo );

		switch ( $action ) {
			case 'create':
				$id = $service->create_version(
					$post_id,
					array(
						'version_name'    => sanitize_text_field( wp_unslash( $_POST['version_name'] ?? '' ) ),
						'file_size_bytes' => absint( $_POST['file_size_bytes'] ?? 0 ),
						'file_type'       => sanitize_text_field( wp_unslash( $_POST['file_type'] ?? 'apk' ) ),
						'external_url'    => esc_url_raw( wp_unslash( $_POST['external_url'] ?? '' ) ),
						'download_type'   => ! empty( $_POST['external_url'] ) ? 'external' : 'media',
						'is_current'      => ! empty( $_POST['is_current'] ),
					)
				);
				wp_send_json_success( array( 'id' => $id ) );
				break;

			case 'set_current':
				$version_id = absint( $_POST['version_id'] ?? 0 );
				$service->set_current( $post_id, $version_id );
				wp_send_json_success();
				break;

			case 'delete':
				$version_id = absint( $_POST['version_id'] ?? 0 );
				$version    = $repo->find( $version_id );
				if ( $version && (int) $version['app_id'] === $post_id ) {
					$repo->delete( $version_id );
				}
				wp_send_json_success();
				break;

			default:
				wp_send_json_error( 'invalid_action', 400 );
		}
	}
}
