<?php

namespace APD\Core\Schema;

use APD\Core\Content\AppPostType;
use APD\Core\Content\Meta;
use APD\Core\Reviews\Controller as ReviewController;

final class SoftwareApplication {

	public function register(): void {
		add_action( 'wp_head', array( $this, 'output_schema' ), 5 );
	}

	public function output_schema(): void {
		if ( ! is_singular( AppPostType::POST_TYPE ) ) {
			return;
		}

		if ( $this->seo_plugin_active() ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'SoftwareApplication',
			'name'     => $post->post_title,
			'url'      => get_permalink( $post ),
		);

		$short = Meta::get( $post->ID, 'short_description' );
		if ( $short ) {
			$schema['description'] = $short;
		}

		$version = Meta::get( $post->ID, 'current_version' );
		if ( $version ) {
			$schema['softwareVersion'] = $version;
		}

		$size = Meta::get( $post->ID, 'file_size_bytes' );
		if ( $size ) {
			$schema['fileSize'] = size_format( (int) $size );
		}

		$updated = Meta::get( $post->ID, 'updated_date' );
		if ( $updated ) {
			$schema['dateModified'] = $updated;
		}

		$platforms = get_the_terms( $post->ID, 'adp_platform' );
		if ( $platforms && ! is_wp_error( $platforms ) ) {
			$schema['operatingSystem'] = $platforms[0]->name;
		}

		$categories = get_the_terms( $post->ID, 'adp_app_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			$schema['applicationCategory'] = $categories[0]->name;
		}

		$price_type       = Meta::get( $post->ID, 'price_type', 'free' );
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'price'         => $price_type === 'free' ? '0' : Meta::get( $post->ID, 'price_amount', '0' ),
			'priceCurrency' => Meta::get( $post->ID, 'price_currency', 'USD' ),
		);

		$avg = ReviewController::get_average_rating( $post->ID );
		if ( $avg ) {
			$count                     = get_comments(
				array(
					'post_id' => $post->ID,
					'type'    => ReviewController::COMMENT_TYPE,
					'status'  => 'approve',
					'count'   => true,
				)
			);
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $avg,
				'ratingCount' => $count,
				'bestRating'  => 5,
				'worstRating' => 1,
			);
		}

		$thumb = get_the_post_thumbnail_url( $post, 'large' );
		if ( $thumb ) {
			$schema['screenshot'] = $thumb;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}

	private function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
	}
}
