<?php

namespace APD\Core\Admin;

final class SetupWizard {

	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function maybe_redirect(): void {
		if ( ! get_transient( 'adp_activation_redirect' ) ) {
			return;
		}
		delete_transient( 'adp_activation_redirect' );
		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}
		wp_safe_redirect( admin_url( 'edit.php?post_type=adp_app&page=adp-setup' ) );
		exit;
	}

	public function add_menu(): void {
		add_submenu_page(
			null,
			__( 'Setup', 'apk-directory-pro' ),
			__( 'Setup', 'apk-directory-pro' ),
			'manage_options',
			'adp-setup',
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['adp_setup_run'] ) && check_admin_referer( 'adp_setup' ) ) {
			$this->create_pages();
			update_option( 'adp_setup_complete', true );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Setup complete.', 'apk-directory-pro' ) . '</p></div>';
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'APK Directory Setup', 'apk-directory-pro' ); ?></h1>
			<p><?php esc_html_e( 'Optionally create default pages and configure your site.', 'apk-directory-pro' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'adp_setup' ); ?>
				<p><label><input type="checkbox" name="create_pages" value="1" checked /> <?php esc_html_e( 'Create default pages (Home, Apps, Blog, About, Contact, Privacy, Disclaimer, DMCA)', 'apk-directory-pro' ); ?></label></p>
				<input type="hidden" name="adp_setup_run" value="1" />
				<?php submit_button( __( 'Run Setup', 'apk-directory-pro' ) ); ?>
			</form>
		</div>
		<?php
	}

	private function create_pages(): void {
		$pages = array(
			'Home'       => '',
			'Apps'       => '',
			'Games'      => '',
			'Blog'       => '',
			'About'      => '',
			'Contact'    => '',
			'Privacy'    => '',
			'Disclaimer' => '',
			'DMCA'       => '',
			'Submit App' => '',
		);

		foreach ( $pages as $title => $content ) {
			$existing = get_posts(
				array(
					'post_type'      => 'page',
					'title'          => $title,
					'post_status'    => 'any',
					'posts_per_page' => 1,
				)
			);
			if ( empty( $existing ) ) {
				wp_insert_post(
					array(
						'post_title'   => $title,
						'post_content' => $content,
						'post_status'  => 'publish',
						'post_type'    => 'page',
					)
				);
			}
		}

		flush_rewrite_rules();
	}
}
