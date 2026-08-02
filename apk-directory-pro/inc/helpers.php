<?php
/**
 * Theme helper functions.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get app meta via plugin if available.
 */
function adp_get_meta( int $post_id, string $key, mixed $default = null ): mixed {
	if ( class_exists( 'APD\\Core\\Content\\Meta' ) ) {
		return \APD\Core\Content\Meta::get( $post_id, $key, $default );
	}
	$value = get_post_meta( $post_id, '_adp_' . $key, true );
	return ( '' === $value && null !== $default ) ? $default : $value;
}

/**
 * Format file size from bytes.
 */
function adp_format_bytes( int $bytes ): string {
	return size_format( $bytes );
}

/**
 * Get download URL for current app version.
 */
function adp_get_download_url( int $app_id ): string {
	if ( ! class_exists( 'APD\\Core\\Versions\\Repository' ) ) {
		return '#';
	}
	$repo    = new \APD\Core\Versions\Repository();
	$version = $repo->get_current( $app_id );
	if ( ! $version ) {
		return '#';
	}
	$controller = new \APD\Core\Downloads\Controller();
	return $controller->get_download_url( (int) $version['id'], $app_id );
}

/**
 * Get developer name for an app.
 */
function adp_get_developer( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'adp_developer' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}

/**
 * Get primary category for an app.
 */
function adp_get_category( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'adp_app_category' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}
	return '';
}

/**
 * Query apps with common args.
 */
function adp_query_apps( array $args = [] ): \WP_Query {
	$defaults = [
		'post_type'      => 'adp_app',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'no_found_rows'  => false,
	];
	return new \WP_Query( array_merge( $defaults, $args ) );
}

/**
 * Output star rating display.
 */
function adp_rating_stars( ?float $rating, string $label = '' ): void {
	if ( null === $rating || $rating <= 0 ) {
		return;
	}
	echo '<span class="adp-rating" aria-label="' . esc_attr( sprintf( __( 'Rating: %s out of 5', 'apk-directory-pro' ), $rating ) ) . '">';
	if ( $label ) {
		echo '<span class="adp-rating-label">' . esc_html( $label ) . '</span> ';
	}
	echo '<span class="adp-rating-value">' . esc_html( number_format( $rating, 1 ) ) . '</span>';
	echo '</span>';
}
