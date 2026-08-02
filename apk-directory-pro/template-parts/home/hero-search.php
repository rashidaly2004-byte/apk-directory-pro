<?php
/**
 * Homepage hero search section.
 *
 * @package AdpTheme
 */
?>
<section class="adp-hero adp-container" aria-labelledby="adp-hero-title">
	<div class="adp-hero__content">
		<h1 id="adp-hero-title" class="adp-hero__title">
			<?php echo esc_html( Adp\Theme\get_theme_mod_string( 'adp_site_tagline', __( 'Discover apps you can trust', 'apk-directory-pro' ) ) ); ?>
		</h1>
		<p class="adp-hero__subtitle"><?php esc_html_e( 'Browse verified apps, read reviews, and download safely.', 'apk-directory-pro' ); ?></p>

		<form role="search" method="get" class="adp-search-form adp-search-form--hero" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-adp-search-form>
			<label for="adp-hero-search" class="adp-sr-only"><?php esc_html_e( 'Search apps', 'apk-directory-pro' ); ?></label>
			<div class="adp-search-form__wrapper">
				<span class="adp-search-form__icon" aria-hidden="true"><?php Adp\Theme\icon( 'search' ); ?></span>
				<input type="search" id="adp-hero-search" class="adp-search-form__input" name="s" placeholder="<?php esc_attr_e( 'Search apps, games, tools…', 'apk-directory-pro' ); ?>" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="adp-hero-search-results" data-adp-search-input>
				<?php if ( post_type_exists( 'adp_app' ) ) : ?>
					<input type="hidden" name="post_type" value="adp_app">
				<?php endif; ?>
				<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Search', 'apk-directory-pro' ); ?></button>
			</div>
			<ul id="adp-hero-search-results" class="adp-search-results" role="listbox" hidden data-adp-search-results></ul>
		</form>
	</div>
</section>
