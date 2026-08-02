<?php

namespace APD\Core\Migration;

final class AppynMigrator {

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'Appyn Migration', 'apk-directory-pro' ),
			__( 'Appyn Migration', 'apk-directory-pro' ),
			'manage_options',
			'adp-migration',
			[ $this, 'render_page' ]
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$dry_run_result = null;
		if ( isset( $_POST['adp_migration_dry_run'] ) && check_admin_referer( 'adp_migration' ) ) {
			$dry_run_result = $this->dry_run();
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Appyn Migration', 'apk-directory-pro' ); ?></h1>
			<p><?php esc_html_e( 'Migrate data from Appyn theme meta fields. Original data is never deleted automatically.', 'apk-directory-pro' ); ?></p>
			<?php if ( $dry_run_result ) : ?>
				<div class="notice notice-info">
					<p><?php echo esc_html( sprintf( __( 'Dry run: %d posts would be migrated.', 'apk-directory-pro' ), $dry_run_result ) ); ?></p>
				</div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'adp_migration' ); ?>
				<p><label><input type="checkbox" name="confirm_backup" value="1" required /> <?php esc_html_e( 'I have backed up my database', 'apk-directory-pro' ); ?></label></p>
				<input type="hidden" name="adp_migration_dry_run" value="1" />
				<?php submit_button( __( 'Dry Run Preview', 'apk-directory-pro' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function dry_run(): int {
		$posts = get_posts(
			[
				'post_type'      => 'any',
				'posts_per_page' => 100,
				'meta_key'       => 'datos_informacion',
				'fields'         => 'ids',
			]
		);
		return count( $posts );
	}
}
