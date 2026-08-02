<?php

namespace APD\Core\Migration;

final class AppynMigrator {

	private MigrationService $service;

	public function __construct() {
		$this->service = new MigrationService();
	}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_adp_migration_batch', array( $this, 'ajax_batch' ) );
		add_action( 'wp_ajax_adp_migration_rollback', array( $this, 'ajax_rollback' ) );
	}

	public function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'Appyn Migration', 'apk-directory-pro' ),
			__( 'Appyn Migration', 'apk-directory-pro' ),
			'manage_options',
			'adp-migration',
			array( $this, 'render_page' )
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'adp_app_page_adp-migration' ) {
			return;
		}
		wp_enqueue_script(
			'adp-migration',
			ADP_CORE_URL . 'assets/js/migration.js',
			array( 'jquery' ),
			ADP_CORE_VERSION,
			true
		);
		wp_localize_script(
			'adp-migration',
			'adpMigration',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'adp_migration' ),
			)
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message     = null;
		$state       = $this->service->get_state();
		$dry_preview = null;

		if ( isset( $_POST['adp_migration_dry_run'] ) && check_admin_referer( 'adp_migration' ) ) {
			$dry_preview = $this->service->dry_run();
		}

		if ( isset( $_POST['adp_migration_start'] ) && check_admin_referer( 'adp_migration' ) ) {
			$state   = $this->service->start_migration( false );
			$message = __( 'Migration started. Processing in batches…', 'apk-directory-pro' );
		}

		if ( isset( $_POST['adp_migration_rollback'] ) && check_admin_referer( 'adp_migration' ) ) {
			$result  = $this->service->rollback();
			$message = sprintf(
				/* translators: %d: number of posts rolled back */
				__( 'Rollback complete. %d migrated posts removed. Original Appyn data was not modified.', 'apk-directory-pro' ),
				$result['rolled']
			);
			$state = $this->service->get_state();
		}

		$log = $this->service->get_log();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Appyn Migration', 'apk-directory-pro' ); ?></h1>
			<p><?php esc_html_e( 'Migrate Appyn theme data into APK Directory. Original data is never deleted automatically. Use rollback to remove only migrated copies.', 'apk-directory-pro' ); ?></p>

			<?php if ( $message ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:800px;padding:1em;margin-bottom:1em;">
				<h2><?php esc_html_e( 'Status', 'apk-directory-pro' ); ?></h2>
				<p>
					<strong><?php esc_html_e( 'Source posts:', 'apk-directory-pro' ); ?></strong>
					<?php echo esc_html( (string) $this->service->count_source_posts() ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Progress:', 'apk-directory-pro' ); ?></strong>
					<?php echo esc_html( (string) $state['processed'] ); ?> / <?php echo esc_html( (string) $state['total'] ); ?>
					(<?php echo esc_html( $state['status'] ); ?>)
				</p>
				<div id="adp-migration-progress" aria-live="polite"></div>
			</div>

			<?php if ( $dry_preview ) : ?>
				<div class="notice notice-info">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of posts that would be migrated */
								__( 'Dry run: %d posts would be migrated.', 'apk-directory-pro' ),
								$dry_preview['count']
							)
						);
						?>
					</p>
				</div>
				<table class="widefat striped" style="max-width:800px;">
					<thead><tr><th>ID</th><th><?php esc_html_e( 'Title', 'apk-directory-pro' ); ?></th><th><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $dry_preview['preview'] as $row ) : ?>
							<tr>
								<td><?php echo esc_html( (string) $row['id'] ); ?></td>
								<td><?php echo esc_html( $row['title'] ); ?></td>
								<td><?php echo esc_html( $row['version'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<form method="post" style="margin-top:1em;">
				<?php wp_nonce_field( 'adp_migration' ); ?>
				<p><label><input type="checkbox" name="confirm_backup" value="1" required /> <?php esc_html_e( 'I have backed up my database', 'apk-directory-pro' ); ?></label></p>
				<input type="hidden" name="adp_migration_dry_run" value="1" />
				<?php submit_button( __( 'Dry Run Preview', 'apk-directory-pro' ), 'secondary' ); ?>
			</form>

			<form method="post" style="margin-top:1em;">
				<?php wp_nonce_field( 'adp_migration' ); ?>
				<p><label><input type="checkbox" name="confirm_backup" value="1" required /> <?php esc_html_e( 'I have backed up my database', 'apk-directory-pro' ); ?></label></p>
				<input type="hidden" name="adp_migration_start" value="1" />
				<?php submit_button( __( 'Run Full Migration', 'apk-directory-pro' ), 'primary' ); ?>
			</form>

			<p>
				<button type="button" class="button" id="adp-migration-continue"><?php esc_html_e( 'Continue batch', 'apk-directory-pro' ); ?></button>
			</p>

			<form method="post" style="margin-top:1em;border-top:1px solid #ccc;padding-top:1em;">
				<?php wp_nonce_field( 'adp_migration' ); ?>
				<p class="description"><?php esc_html_e( 'Rollback removes migrated adp_app posts and version rows only. Original Appyn posts and meta are preserved.', 'apk-directory-pro' ); ?></p>
				<input type="hidden" name="adp_migration_rollback" value="1" />
				<?php submit_button( __( 'Rollback Migration', 'apk-directory-pro' ), 'delete' ); ?>
			</form>

			<?php if ( $log ) : ?>
				<h2><?php esc_html_e( 'Migration log', 'apk-directory-pro' ); ?></h2>
				<pre style="max-width:800px;background:#f6f7f7;padding:1em;overflow:auto;max-height:300px;"><?php echo esc_html( wp_json_encode( array_slice( $log, -20 ), JSON_PRETTY_PRINT ) ); ?></pre>
			<?php endif; ?>
		</div>
		<?php
	}

	public function dry_run(): int {
		return $this->service->count_source_posts();
	}

	public function ajax_batch(): void {
		check_ajax_referer( 'adp_migration', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		$state = $this->service->process_batch();
		wp_send_json_success( $state );
	}

	public function ajax_rollback(): void {
		check_ajax_referer( 'adp_migration', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
		$result = $this->service->rollback();
		wp_send_json_success( $result );
	}
}
