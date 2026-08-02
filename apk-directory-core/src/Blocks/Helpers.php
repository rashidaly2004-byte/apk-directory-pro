<?php
/**
 * Block render helpers.
 *
 * @package Adp\Core
 */

namespace Adp\Core\Blocks;

use Adp\Core\Content\AppPostType;
use Adp\Core\Content\Meta;
use Adp\Core\Downloads\Signer;
use Adp\Core\Versions\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Shared block rendering utilities.
 */
class Helpers {

	/**
	 * Query apps for blocks.
	 *
	 * @param array<string, mixed> $args Query overrides.
	 * @return array<int, \WP_Post>
	 */
	public static function query_apps( array $args = array() ): array {
		$defaults = array(
			'post_type'      => AppPostType::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'no_found_rows'  => true,
		);
		$query = new \WP_Query( array_merge( $defaults, $args ) );
		return $query->posts;
	}

	/**
	 * Render app card HTML.
	 *
	 * @param \WP_Post $post App post.
	 * @return string
	 */
	public static function render_app_card( \WP_Post $post ): string {
		$developers = wp_get_post_terms( $post->ID, 'adp_developer', array( 'fields' => 'names' ) );
		$developer  = ( ! is_wp_error( $developers ) && ! empty( $developers ) ) ? $developers[0] : '';
		$version    = (string) Meta::get( $post->ID, '_adp_current_version' );
		$size       = (int) Meta::get( $post->ID, '_adp_file_size_bytes' );
		$icon       = get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'adp-app-card__icon' ) );

		ob_start();
		?>
		<article class="adp-app-card">
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="adp-app-card__link">
				<?php echo $icon ? wp_kses_post( $icon ) : '<span class="adp-app-card__icon adp-app-card__icon--placeholder" aria-hidden="true"></span>'; ?>
				<div class="adp-app-card__body">
					<h3 class="adp-app-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
					<?php if ( $developer ) : ?>
						<p class="adp-app-card__developer"><?php echo esc_html( $developer ); ?></p>
					<?php endif; ?>
					<p class="adp-app-card__meta">
						<?php if ( $version ) : ?>
							<span><?php echo esc_html( $version ); ?></span>
						<?php endif; ?>
						<?php if ( $size > 0 ) : ?>
							<span><?php echo esc_html( size_format( $size ) ); ?></span>
						<?php endif; ?>
					</p>
				</div>
			</a>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Get download URL for current app version.
	 *
	 * @param int $app_id App post ID.
	 * @return string
	 */
	public static function get_download_url( int $app_id ): string {
		$repo    = new Repository();
		$version = $repo->find_current( $app_id );
		if ( null === $version ) {
			return '';
		}
		$signer = new Signer();
		return $signer->get_interstitial_url( (int) $version['id'] );
	}
}
