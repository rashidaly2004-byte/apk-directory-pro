<?php
/**
 * Title: App Listing CTA
 * Slug: adp/app-listing-cta
 * Categories: adp-directory
 */
return array(
	'title'      => __( 'App Listing CTA', 'apk-directory-pro' ),
	'categories' => array( 'adp-directory' ),
	'content'    => '<!-- wp:group {"className":"adp-section adp-section--highlight","layout":{"type":"constrained"}} -->
<div class="wp-block-group adp-section adp-section--highlight"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html__( 'Explore our app directory', 'apk-directory-pro' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"adp-btn adp-btn--primary"} -->
<div class="wp-block-button adp-btn adp-btn--primary"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Browse all apps', 'apk-directory-pro' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
);
