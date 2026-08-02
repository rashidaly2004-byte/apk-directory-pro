<?php
/**
 * Minimal stubs for PHPStan.
 */

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $a, $b, $c = 10, $d = 1 ) {}
}
if ( ! function_exists( 'get_option' ) ) {
	function get_option( $a, $b = false ) { return $b; }
}
if ( ! function_exists( 'update_option' ) ) {
	function update_option( $a, $b, $c = true ) { return true; }
}
if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $a ) {}
}
if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $a, $b = 0, $c = 512 ) { return json_encode( $a ); }
}
if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( $a = 12, $b = true, $c = false ) { return 'secret'; }
}
if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $a ) { return $a; }
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $a ) { return $a; }
}
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $a ) { return $a; }
}
if ( ! function_exists( 'absint' ) ) {
	function absint( $a ) { return (int) $a; }
}
if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $a ) { return $a; }
}
if ( ! function_exists( 'maybe_unserialize' ) ) {
	function maybe_unserialize( $a ) { return $a; }
}
if ( ! function_exists( 'get_post' ) ) {
	function get_post( $a ) { return null; }
}
if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $a, $b, $c = false ) { return ''; }
}
if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( $a, $b, $c ) { return true; }
}
if ( ! function_exists( 'wp_insert_post' ) ) {
	function wp_insert_post( $a, $b = false ) { return 1; }
}
if ( ! function_exists( 'wp_delete_post' ) ) {
	function wp_delete_post( $a, $b = false ) { return true; }
}
if ( ! function_exists( 'get_post_type' ) ) {
	function get_post_type( $a ) { return 'post'; }
}
if ( ! function_exists( 'get_posts' ) ) {
	function get_posts( $a ) { return []; }
}
if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( $a, $b ) { return []; }
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $a ) { return false; }
}
if ( ! function_exists( 'wp_insert_term' ) ) {
	function wp_insert_term( $a, $b, $c = [] ) { return [ 'term_id' => 1 ]; }
}
if ( ! function_exists( 'term_exists' ) ) {
	function term_exists( $a, $b, $c = null ) { return [ 'term_id' => 1 ]; }
}
if ( ! function_exists( 'wp_set_object_terms' ) ) {
	function wp_set_object_terms( $a, $b, $c ) { return true; }
}
if ( ! function_exists( 'set_post_thumbnail' ) ) {
	function set_post_thumbnail( $a, $b ) { return true; }
}
if ( ! function_exists( 'get_post_thumbnail_id' ) ) {
	function get_post_thumbnail_id( $a ) { return 0; }
}
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $a = '' ) { return 'http://example.com'; }
}
if ( ! function_exists( 'get_permalink' ) ) {
	function get_permalink( $a = 0 ) { return ''; }
}
if ( ! function_exists( 'size_format' ) ) {
	function size_format( $a ) { return (string) $a; }
}
if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $a, $b = null ) { return true; }
}
if ( ! function_exists( '__' ) ) {
	function __( $a, $b = 'default' ) { return $a; }
}

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {
		public $ID = 0;
		public $post_title = '';
		public $post_content = '';
		public $post_excerpt = '';
		public $post_status = 'publish';
		public $post_type = 'post';
		public $post_author = 1;
		public $post_parent = 0;
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		public function get_param( $a ) { return null; }
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		public function __construct( $a, $b = 200 ) {}
	}
}
