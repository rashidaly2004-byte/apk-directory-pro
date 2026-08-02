<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="adp-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'apk-directory-pro' ); ?></a>

<header class="adp-header" role="banner">
	<div class="adp-header__inner adp-container">
		<div class="adp-header__row">
			<div class="adp-header__logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="adp-site-title"><?php bloginfo( 'name' ); ?></a>
				<?php endif; ?>
			</div>

			<div class="adp-header__search" role="search">
				<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="adp-search-form">
					<label class="adp-search-label" for="adp-search-input"><?php esc_html_e( 'Search apps and articles', 'apk-directory-pro' ); ?></label>
					<div class="adp-search-field">
						<svg class="adp-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
						<input
							type="search"
							id="adp-search-input"
							name="s"
							class="adp-search-input"
							placeholder="<?php esc_attr_e( 'Search apps, games…', 'apk-directory-pro' ); ?>"
							autocomplete="off"
							role="combobox"
							aria-expanded="false"
							aria-controls="adp-search-suggestions"
							aria-autocomplete="list"
						/>
						<ul id="adp-search-suggestions" class="adp-search-suggestions" role="listbox" hidden></ul>
					</div>
				</form>
			</div>

			<div class="adp-header__actions">
				<button type="button" class="adp-btn-icon adp-theme-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'apk-directory-pro' ); ?>">
					<svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.2A5.009 5.009 0 0 1 12 3z"/></svg>
				</button>
				<button type="button" class="adp-btn-icon adp-menu-toggle" aria-expanded="false" aria-controls="adp-mobile-drawer" aria-label="<?php esc_attr_e( 'Open menu', 'apk-directory-pro' ); ?>">
					<svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
				</button>
				<nav class="adp-nav-desktop" aria-label="<?php esc_attr_e( 'Primary', 'apk-directory-pro' ); ?>">
					<?php
					wp_nav_menu( [
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'adp-menu adp-menu--primary',
						'fallback_cb'    => false,
					] );
					?>
				</nav>
			</div>
		</div>
	</div>

	<?php if ( get_theme_mod( 'adp_show_category_bar', true ) ) : ?>
		<div class="adp-category-bar">
			<div class="adp-container adp-category-bar__inner">
				<nav aria-label="<?php esc_attr_e( 'Categories', 'apk-directory-pro' ); ?>">
					<?php
					wp_nav_menu( [
						'theme_location' => 'category-bar',
						'container'      => false,
						'menu_class'     => 'adp-menu adp-menu--categories',
						'fallback_cb'    => false,
					] );
					?>
				</nav>
			</div>
		</div>
	<?php endif; ?>
</header>

<div id="adp-mobile-drawer" class="adp-drawer" hidden>
	<div class="adp-drawer__overlay" data-close-drawer></div>
	<div class="adp-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'apk-directory-pro' ); ?>">
		<button type="button" class="adp-drawer__close" data-close-drawer aria-label="<?php esc_attr_e( 'Close menu', 'apk-directory-pro' ); ?>">×</button>
		<?php
		wp_nav_menu( [
			'theme_location' => 'mobile',
			'container'      => false,
			'menu_class'     => 'adp-menu adp-menu--mobile',
			'fallback_cb'    => function () {
				wp_nav_menu( [
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'adp-menu adp-menu--mobile',
					'fallback_cb'    => false,
				] );
			},
		] );
		?>
	</div>
</div>
