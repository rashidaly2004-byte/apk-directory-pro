<?php
/**
 * App post meta schema definitions.
 *
 * @package Adp\Core\Content
 */

namespace Adp\Core\Content;

use Adp\Core\Support\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Defines all _adp_* post meta fields.
 */
class MetaSchema {

	/**
	 * Get all meta field definitions.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_fields(): array {
		return array(
			'_adp_short_description' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_package_name'      => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'package_name' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_current_version'   => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_version_code'      => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_file_size_bytes'   => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_android_requirement' => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_architectures'     => array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( Sanitizer::class, 'string_array' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
				'single'            => true,
			),
			'_adp_dpi'               => array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( Sanitizer::class, 'string_array' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
				),
				'single'            => true,
			),
			'_adp_release_date'      => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'date' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_updated_date'      => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'date' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_price_type'        => array(
				'type'              => 'string',
				'default'           => 'free',
				'sanitize_callback' => static fn( mixed $v ): string => Sanitizer::enum( $v, array( 'free', 'paid', 'freemium' ), 'free' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_price_amount'      => array(
				'type'              => 'number',
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'float' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_price_currency'    => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'currency' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_official_url'      => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'url' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_store_url'         => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'url' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_support_url'       => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'url' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_privacy_url'       => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'url' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_license'           => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_content_rating'    => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_status_badge'      => array(
				'type'              => 'string',
				'default'           => 'none',
				'sanitize_callback' => static fn( mixed $v ): string => Sanitizer::enum( $v, array( 'none', 'new', 'updated', 'mod' ), 'none' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_verified'          => array(
				'type'              => 'boolean',
				'default'           => false,
				'sanitize_callback' => array( Sanitizer::class, 'bool' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_editor_rating'     => array(
				'type'              => 'number',
				'default'           => 0,
				'sanitize_callback' => static function ( mixed $value ): float {
					$rating = (float) $value;
					return max( 0.0, min( 5.0, $rating ) );
				},
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_download_count'    => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
				'show_in_rest'      => true,
				'single'            => true,
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_adp_apps' ),
			),
			'_adp_view_count'        => array(
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => array( Sanitizer::class, 'positive_int' ),
				'show_in_rest'      => true,
				'single'            => true,
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_adp_apps' ),
			),
			'_adp_screenshot_ids'    => array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => array( Sanitizer::class, 'id_array' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'            => true,
			),
			'_adp_video_url'         => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'url' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_whats_new'         => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'html' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_mod_features'      => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => array( Sanitizer::class, 'html' ),
				'show_in_rest'      => true,
				'single'            => true,
			),
			'_adp_disclaimer_note'   => array(
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_textarea_field',
				'show_in_rest'      => true,
				'single'            => true,
			),
		);
	}

	/**
	 * Get default value for a meta key.
	 *
	 * @param string $key Meta key.
	 * @return mixed
	 */
	public static function get_default( string $key ): mixed {
		$fields = self::get_fields();
		return $fields[ $key ]['default'] ?? '';
	}
}
