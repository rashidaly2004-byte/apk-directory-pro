<?php
/**
 * Title: Featured Categories
 * Slug: adp/featured-categories
 * Categories: adp-directory
 */
return array(
	'title'      => __( 'Featured Categories', 'apk-directory-pro' ),
	'categories' => array( 'adp-directory' ),
	'content'    => '<!-- wp:group {"className":"adp-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group adp-section"><!-- wp:heading -->
<h2 class="wp-block-heading">' . esc_html__( 'Browse by category', 'apk-directory-pro' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Explore apps organized by category.', 'apk-directory-pro' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->',
);
