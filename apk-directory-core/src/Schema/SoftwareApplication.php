<?php
/**
 * SoftwareApplication structured data.
 *
 * @package Adp\Core\Schema
 */

namespace Adp\Core\Schema;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Reviews\Schema as ReviewSchema;
use Adp\Core\Support\Formatter;

defined( 'ABSPATH' ) || exit;

/**
 * Outputs factual SoftwareApplication JSON-LD.
 */
class SoftwareApplication {

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_head', array( self::class, 'output' ), 5 );
	}

	/**
	 * Output schema on single app pages.
	 *
	 * @return void
	 */
	public static function output(): void {
		if ( ! is_singular( AppPostType::POST_TYPE ) ) {
			return;
		}

		if ( self::seo_plugin_active() ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$schema = self::build( $post_id );
		if ( empty( $schema ) ) {
			return;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Build schema array for an app.
	 *
	 * @param int $post_id App post ID.
	 * @return array<string, mixed>
	 */
	public static function build( int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$developers = wp_get_post_terms( $post_id, 'adp_developer', array( 'fields' => 'names' ) );
		$categories = wp_get_post_terms( $post_id, 'adp_app_category', array( 'fields' => 'names' ) );
		$platforms  = wp_get_post_terms( $post_id, 'adp_platform', array( 'fields' => 'names' ) );

		$schema = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => get_the_title( $post_id ),
			'description'         => (string) Meta::get( $post_id, '_adp_short_description' ),
			'softwareVersion'     => (string) Meta::get( $post_id, '_adp_current_version' ),
			'operatingSystem'     => ! is_wp_error( $platforms ) && ! empty( $platforms ) ? $platforms[0] : 'Android',
			'applicationCategory' => ! is_wp_error( $categories ) && ! empty( $categories ) ? $categories[0] : 'Application',
			'dateModified'        => get_the_modified_date( 'c', $post_id ),
			'url'                 => get_permalink( $post_id ),
		);

		$file_size = (int) Meta::get( $post_id, '_adp_file_size_bytes' );
		if ( $file_size > 0 ) {
			$schema['fileSize'] = Formatter::bytes( $file_size );
		}

		$store_url = (string) Meta::get( $post_id, '_adp_store_url' );
		if ( '' !== $store_url ) {
			$schema['downloadUrl'] = $store_url;
		}

		if ( ! is_wp_error( $developers ) && ! empty( $developers ) ) {
			$schema['author'] = array(
				'@type' => 'Organization',
				'name'  => $developers[0],
			);
		}

		$screenshot_ids = Meta::get( $post_id, '_adp_screenshot_ids' );
		if ( is_array( $screenshot_ids ) && ! empty( $screenshot_ids ) ) {
			$screenshots = array();
			foreach ( $screenshot_ids as $id ) {
				$url = wp_get_attachment_image_url( (int) $id, 'large' );
				if ( $url ) {
					$screenshots[] = $url;
				}
			}
			if ( ! empty( $screenshots ) ) {
				$schema['screenshot'] = $screenshots;
			}
		}

		$price_type = (string) Meta::get( $post_id, '_adp_price_type' );
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => 'free' === $price_type ? '0' : (string) Meta::get( $post_id, '_adp_price_amount' ),
			'priceCurrency' => (string) Meta::get( $post_id, '_adp_price_currency' ) ?: 'USD',
		);

		$ratings = ReviewSchema::get_aggregate( $post_id );
		if ( $ratings['count'] > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $ratings['average'],
				'ratingCount' => $ratings['count'],
				'bestRating'  => 5,
				'worstRating' => 1,
			);
		}

		return $schema;
	}

	/**
	 * Check if a major SEO plugin is active.
	 *
	 * @return bool
	 */
	private static function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
	}
}
