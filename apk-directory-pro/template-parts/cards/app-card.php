<?php
$post_id = get_the_ID();
?>
<article class="adp-app-card">
	<a href="<?php the_permalink(); ?>" class="adp-app-card__link">
		<div class="adp-app-card__icon">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'adp-app-card', [ 'loading' => 'lazy' ] ); ?>
			<?php else : ?>
				<span class="adp-app-card__placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="adp-app-card__body">
			<h2 class="adp-app-card__title"><?php the_title(); ?></h2>
			<p class="adp-app-card__meta">
				<?php
				$dev = adp_get_developer( $post_id );
				$cat = adp_get_category( $post_id );
				if ( $dev ) {
					echo esc_html( $dev );
				}
				if ( $dev && $cat ) {
					echo ' · ';
				}
				if ( $cat ) {
					echo esc_html( $cat );
				}
				?>
			</p>
			<p class="adp-app-card__details">
				<?php
				$version = adp_get_meta( $post_id, 'current_version' );
				$size    = (int) adp_get_meta( $post_id, 'file_size_bytes', 0 );
				$updated = adp_get_meta( $post_id, 'updated_date' );
				$parts   = [];
				if ( $version ) {
					$parts[] = 'v' . $version;
				}
				if ( $size ) {
					$parts[] = adp_format_bytes( $size );
				}
				if ( $updated ) {
					$parts[] = $updated;
				}
				echo esc_html( implode( ' · ', $parts ) );
				?>
			</p>
			<?php
			if ( class_exists( 'APD\\Core\\Reviews\\Controller' ) ) {
				$rating = \APD\Core\Reviews\Controller::get_average_rating( $post_id );
				if ( $rating ) {
					adp_rating_stars( $rating );
				}
			}
			?>
		</div>
		<span class="adp-app-card__action adp-btn adp-btn--small"><?php esc_html_e( 'View', 'apk-directory-pro' ); ?></span>
	</a>
</article>
