<?php
/**
 * Title: Hero Search
 * Slug: adp/hero-search
 * Categories: adp-directory
 * Keywords: hero, search, apps
 */
return array(
	'title'      => __( 'Hero Search', 'apk-directory-pro' ),
	'categories' => array( 'adp-directory' ),
	'content'    => '<!-- wp:group {"className":"adp-hero","layout":{"type":"constrained"}} -->
<div class="wp-block-group adp-hero"><!-- wp:heading {"level":1,"className":"adp-hero__title"} -->
<h1 class="wp-block-heading adp-hero__title">' . esc_html__( 'Discover apps you can trust', 'apk-directory-pro' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"adp-hero__subtitle"} -->
<p class="adp-hero__subtitle">' . esc_html__( 'Browse verified apps, read reviews, and download safely.', 'apk-directory-pro' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
);
