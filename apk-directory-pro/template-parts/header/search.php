<?php
/**
 * Header search.
 *
 * @package AdpTheme
 */

if ( ! Adp\Theme\get_theme_mod_bool( 'adp_show_search', true ) ) {
	return;
}
?>
<div class="adp-header-search adp-container">
	<form role="search" method="get" class="adp-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-adp-search-form>
		<div class="adp-search-form__wrapper">
			<label for="adp-header-search" class="adp-sr-only"><?php esc_html_e( 'Search apps', 'apk-directory-pro' ); ?></label>
			<span class="adp-search-form__icon" aria-hidden="true"><?php Adp\Theme\icon( 'search' ); ?></span>
			<input
				type="search"
				id="adp-header-search"
				class="adp-search-form__input"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'Search apps…', 'apk-directory-pro' ); ?>"
				autocomplete="off"
				role="combobox"
				aria-expanded="false"
				aria-controls="adp-search-results"
				aria-autocomplete="list"
				data-adp-search-input
			>
			<?php if ( post_type_exists( 'adp_app' ) ) : ?>
				<input type="hidden" name="post_type" value="adp_app">
			<?php endif; ?>
		</div>
		<ul id="adp-search-results" class="adp-search-results" role="listbox" hidden data-adp-search-results></ul>
	</form>
</div>
