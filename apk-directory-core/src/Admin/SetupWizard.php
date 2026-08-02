<?php
/**
 * First-run setup wizard.
 *
 * @package Adp\Core\Admin
 */

namespace Adp\Core\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Opt-in setup wizard for page creation.
 */
class SetupWizard {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_menu' ) );
		add_action( 'admin_post_adp_setup_wizard', array( self::class, 'handle_submit' ) );
		add_action( 'admin_notices', array( self::class, 'maybe_show_notice' ) );
	}

	/**
	 * Add setup wizard page.
	 *
	 * @return void
	 */
	public static function add_menu(): void {
		add_submenu_page(
			'edit.php?post_type=adp_app',
			__( 'Setup Wizard', 'apk-directory-core' ),
			__( 'Setup', 'apk-directory-core' ),
			'manage_options',
			'adp-setup-wizard',
			array( self::class, 'render_page' )
		);
	}

	/**
	 * Show notice if wizard not completed.
	 *
	 * @return void
	 */
	public static function maybe_show_notice(): void {
		if ( get_option( 'adp_core_setup_wizard_done' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'adp_app' !== $screen->post_type ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Complete the APK Directory setup wizard to create recommended pages.', 'apk-directory-core' ),
			esc_url( admin_url( 'edit.php?post_type=adp_app&page=adp-setup-wizard' ) ),
			esc_html__( 'Run Setup Wizard', 'apk-directory-core' )
		);
	}

	/**
	 * Render wizard page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$done = (bool) get_option( 'adp_core_setup_wizard_done' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'APK Directory Setup Wizard', 'apk-directory-core' ); ?></h1>
			<?php if ( $done ) : ?>
				<p><?php esc_html_e( 'Setup has already been completed. You can run it again to create any missing pages.', 'apk-directory-core' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'This wizard will optionally create recommended pages for your APK directory site.', 'apk-directory-core' ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'adp_setup_wizard', 'adp_setup_nonce' ); ?>
				<input type="hidden" name="action" value="adp_setup_wizard" />
				<p>
					<label>
						<input type="checkbox" name="create_pages" value="1" checked />
						<?php esc_html_e( 'Create recommended pages (Home, Apps, Games, Blog, About, Contact, Privacy, Disclaimer, DMCA, Submit App)', 'apk-directory-core' ); ?>
					</label>
				</p>
				<p>
					<label>
						<input type="checkbox" name="import_demo" value="1" />
						<?php esc_html_e( 'Import neutral demo content', 'apk-directory-core' ); ?>
					</label>
				</p>
				<?php submit_button( __( 'Run Setup', 'apk-directory-core' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle wizard form submission.
	 *
	 * @return void
	 */
	public static function handle_submit(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'apk-directory-core' ) );
		}

		check_admin_referer( 'adp_setup_wizard', 'adp_setup_nonce' );

		if ( ! empty( $_POST['create_pages'] ) ) {
			self::create_pages();
		}

		if ( ! empty( $_POST['import_demo'] ) ) {
			DemoImporter::import();
		}

		update_option( 'adp_core_setup_wizard_done', true, false );

		wp_safe_redirect( admin_url( 'edit.php?post_type=adp_app&page=adp-setup-wizard&setup=done' ) );
		exit;
	}

	/**
	 * Create recommended pages once.
	 *
	 * @return array<int, int> Created page IDs.
	 */
	public static function create_pages(): array {
		$pages = array(
			'home'        => array( 'title' => __( 'Home', 'apk-directory-core' ), 'content' => '' ),
			'apps'        => array( 'title' => __( 'Apps', 'apk-directory-core' ), 'content' => '' ),
			'games'       => array( 'title' => __( 'Games', 'apk-directory-core' ), 'content' => '' ),
			'blog'        => array( 'title' => __( 'Blog', 'apk-directory-core' ), 'content' => '' ),
			'about'       => array( 'title' => __( 'About', 'apk-directory-core' ), 'content' => __( 'About this APK directory.', 'apk-directory-core' ) ),
			'contact'     => array( 'title' => __( 'Contact', 'apk-directory-core' ), 'content' => __( 'Contact us with questions or feedback.', 'apk-directory-core' ) ),
			'privacy'     => array( 'title' => __( 'Privacy Policy', 'apk-directory-core' ), 'content' => __( 'Our privacy policy.', 'apk-directory-core' ) ),
			'disclaimer'  => array( 'title' => __( 'Disclaimer', 'apk-directory-core' ), 'content' => __( 'Files are provided for informational purposes. We do not guarantee safety or official status unless explicitly verified.', 'apk-directory-core' ) ),
			'dmca'        => array( 'title' => __( 'DMCA / Takedown', 'apk-directory-core' ), 'content' => __( 'Submit takedown requests using our report form.', 'apk-directory-core' ) ),
			'submit-app'  => array( 'title' => __( 'Submit App', 'apk-directory-core' ), 'content' => __( 'Submit your app for listing consideration.', 'apk-directory-core' ) ),
		);

		$created = array();
		$existing = get_option( 'adp_core_created_pages', array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		foreach ( $pages as $slug => $page ) {
			if ( isset( $existing[ $slug ] ) && get_post( (int) $existing[ $slug ] ) ) {
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $page['title'],
					'post_content' => $page['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_name'    => $slug,
				),
				true
			);

			if ( ! is_wp_error( $page_id ) ) {
				$created[]         = $page_id;
				$existing[ $slug ] = $page_id;
			}
		}

		update_option( 'adp_core_created_pages', $existing, false );
		self::assign_menus( $existing );
		return $created;
	}

	/**
	 * Assign theme menu locations from created pages.
	 *
	 * @param array<string, int> $pages Page slug => ID map.
	 * @return void
	 */
	private static function assign_menus( array $pages ): void {
		$primary_items = array( 'home', 'apps', 'games', 'blog' );
		$footer_company = array( 'about', 'contact', 'submit-app' );
		$footer_browse  = array( 'apps', 'games', 'blog' );
		$footer_legal   = array( 'privacy', 'disclaimer', 'dmca' );

		$locations = array(
			'primary'        => self::create_menu( __( 'Primary Menu', 'apk-directory-core' ), $pages, $primary_items ),
			'footer-company' => self::create_menu( __( 'Footer Company', 'apk-directory-core' ), $pages, $footer_company ),
			'footer-browse'  => self::create_menu( __( 'Footer Browse', 'apk-directory-core' ), $pages, $footer_browse ),
			'footer-legal'   => self::create_menu( __( 'Footer Legal', 'apk-directory-core' ), $pages, $footer_legal ),
		);

		$assigned = array();
		foreach ( $locations as $location => $menu_id ) {
			if ( $menu_id > 0 ) {
				$assigned[ $location ] = $menu_id;
			}
		}

		if ( ! empty( $assigned ) ) {
			set_theme_mod( 'nav_menu_locations', array_merge( (array) get_theme_mod( 'nav_menu_locations', array() ), $assigned ) );
		}

		if ( isset( $pages['home'] ) ) {
			update_option( 'show_on_front', 'page', false );
			update_option( 'page_on_front', (int) $pages['home'], false );
		}
		if ( isset( $pages['blog'] ) ) {
			update_option( 'page_for_posts', (int) $pages['blog'], false );
		}
	}

	/**
	 * Create or update a nav menu from page slugs.
	 *
	 * @param string             $name  Menu name.
	 * @param array<string, int> $pages Page map.
	 * @param array<int, string> $slugs Ordered slugs.
	 * @return int Menu ID.
	 */
	private static function create_menu( string $name, array $pages, array $slugs ): int {
		$existing = wp_get_nav_menu_object( $name );
		$menu_id  = $existing ? (int) $existing->term_id : 0;

		if ( $menu_id <= 0 ) {
			$result = wp_create_nav_menu( $name );
			if ( is_wp_error( $result ) ) {
				return 0;
			}
			$menu_id = (int) $result;
		}

		$position = 1;
		foreach ( $slugs as $slug ) {
			if ( empty( $pages[ $slug ] ) ) {
				continue;
			}
			$page_id = (int) $pages[ $slug ];
			$exists  = false;
			$items   = wp_get_nav_menu_items( $menu_id );
			if ( is_array( $items ) ) {
				foreach ( $items as $item ) {
					if ( (int) $item->object_id === $page_id ) {
						$exists = true;
						break;
					}
				}
			}
			if ( $exists ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $page_id ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $position,
				)
			);
			++$position;
		}

		return $menu_id;
	}
}
