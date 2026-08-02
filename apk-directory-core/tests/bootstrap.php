<?php
/**
 * PHPUnit bootstrap.
 *
 * @package Adp\Core\Tests
 */

define( 'ABSPATH', __DIR__ . '/../vendor/wordpress/wordpress/' );
define( 'WP_DEBUG', true );

// Minimal WordPress stubs for unit tests without full WP install.
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore
		unset( $hook, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { // phpcs:ignore
		unset( $hook, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) { // phpcs:ignore
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) { // phpcs:ignore
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $str ) {
		return sanitize_text_field( $str );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url, $protocols = null ) { // phpcs:ignore
		unset( $protocols );
		$url = trim( (string) $url );
		return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) { // phpcs:ignore
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) { // phpcs:ignore
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return strip_tags( (string) $data, '<p><br><strong><em><ul><ol><li><a>' );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $key ) {
		global $adp_test_transients;
		return $adp_test_transients[ $key ] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $key, $value, $expiration ) { // phpcs:ignore
		global $adp_test_transients;
		$adp_test_transients[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $key ) {
		global $adp_test_transients;
		unset( $adp_test_transients[ $key ] );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $key, $default = false ) {
		global $adp_test_options;
		return $adp_test_options[ $key ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $key, $value, $autoload = null ) { // phpcs:ignore
		global $adp_test_options;
		$adp_test_options[ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) { // phpcs:ignore
		unset( $special_chars, $extra_special_chars );
		return str_repeat( 'a', $length );
	}
}

if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.com/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'user_trailingslashit' ) ) {
	function user_trailingslashit( $string ) {
		return rtrim( $string, '/' ) . '/';
	}
}

if ( ! function_exists( 'size_format' ) ) {
	function size_format( $bytes ) {
		return round( $bytes / 1048576, 2 ) . ' MB';
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, $decimals = 0 ) {
		return number_format( (float) $number, $decimals );
	}
}

if ( ! function_exists( 'get_option_date' ) ) {
	// noop
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private string $code;
		private string $message;

		public function __construct( string $code = '', string $message = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ): bool {
		return $thing instanceof WP_Error;
	}
}

$GLOBALS['adp_test_transients'] = array();
$GLOBALS['adp_test_options']    = array(
	'adp_download_secret' => 'test-secret-key-for-hmac-signing-32chars-minimum!!',
);

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return 0;
	}
}

if ( ! function_exists( 'update_post_meta' ) ) {
	function update_post_meta( $post_id, $meta_key, $meta_value ) { // phpcs:ignore
		global $adp_test_post_meta;
		$adp_test_post_meta[ $post_id ][ $meta_key ] = $meta_value;
		return true;
	}
}

if ( ! function_exists( 'get_post_meta' ) ) {
	function get_post_meta( $post_id, $key, $single = false ) { // phpcs:ignore
		global $adp_test_meta;
		$value = $adp_test_meta[ $post_id ][ $key ] ?? '';
		return $single ? $value : array( $value );
	}
}

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post { // phpcs:ignore
		public int $ID;
		public string $post_title = '';
		public string $post_content = '';
		public string $post_excerpt = '';
		public string $post_status = 'publish';
		public string $post_type = 'post';
		public string $post_name = '';
	}
}
if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query { // phpcs:ignore
		/** @var array<int, object> */
		public array $posts = array();

		/** @param array<string, mixed> $args */
		public function __construct( array $args ) { // phpcs:ignore
			unset( $args );
		}
	}
}

require_once __DIR__ . '/../vendor/autoload.php';
