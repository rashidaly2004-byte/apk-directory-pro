<?php
/**
 * Single app template.
 */

get_header();

$post_id = get_the_ID();
$download_url = adp_get_download_url( $post_id );
$store_url    = adp_get_meta( $post_id, 'store_url' );
$user_rating  = class_exists( 'APD\\Core\\Reviews\\Controller' )
	? \APD\Core\Reviews\Controller::get_average_rating( $post_id )
	: null;
$editor_rating = (float) adp_get_meta( $post_id, 'editor_rating', 0 );
$verified      = (bool) adp_get_meta( $post_id, 'verified' );
?>
<main id="main-content" class="adp-main adp-single-app">
	<div class="adp-container adp-single-app__layout">
		<div class="adp-single-app__content">
			<?php do_action( 'adp_before_content' ); ?>

			<article class="adp-app-hero">
				<div class="adp-app-hero__icon">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'adp-app-icon', [ 'loading' => 'eager' ] ); ?>
					<?php endif; ?>
				</div>
				<div class="adp-app-hero__body">
					<h1 class="adp-app-hero__title"><?php the_title(); ?></h1>
					<?php if ( $short = adp_get_meta( $post_id, 'short_description' ) ) : ?>
						<p class="adp-app-hero__desc"><?php echo esc_html( $short ); ?></p>
					<?php endif; ?>
					<p class="adp-app-hero__meta">
						<?php if ( $dev = adp_get_developer( $post_id ) ) : ?>
							<span><?php echo esc_html( $dev ); ?></span>
						<?php endif; ?>
						<?php if ( $verified ) : ?>
							<span class="adp-badge adp-badge--verified"><?php esc_html_e( 'Verified', 'apk-directory-pro' ); ?></span>
						<?php endif; ?>
					</p>
					<div class="adp-app-hero__ratings">
						<?php if ( $user_rating ) : ?>
							<?php adp_rating_stars( $user_rating, __( 'User rating', 'apk-directory-pro' ) ); ?>
						<?php endif; ?>
						<?php if ( $editor_rating > 0 ) : ?>
							<?php adp_rating_stars( $editor_rating, __( 'Editor rating', 'apk-directory-pro' ) ); ?>
						<?php endif; ?>
					</div>
					<p class="adp-app-hero__version">
						<?php
						$version = adp_get_meta( $post_id, 'current_version' );
						$updated = adp_get_meta( $post_id, 'updated_date' );
						if ( $version ) {
							echo esc_html( sprintf( __( 'Version %s', 'apk-directory-pro' ), $version ) );
						}
						if ( $updated ) {
							echo ' · ' . esc_html( sprintf( __( 'Updated %s', 'apk-directory-pro' ), $updated ) );
						}
						?>
					</p>
					<div class="adp-app-hero__actions">
						<a href="<?php echo esc_url( $download_url ); ?>" class="adp-btn adp-btn--primary adp-btn--download">
							<?php
							$size = (int) adp_get_meta( $post_id, 'file_size_bytes', 0 );
							echo esc_html( __( 'Download APK', 'apk-directory-pro' ) );
							if ( $size ) {
								echo ' (' . esc_html( adp_format_bytes( $size ) ) . ')';
							}
							?>
						</a>
						<?php if ( $store_url ) : ?>
							<a href="<?php echo esc_url( $store_url ); ?>" class="adp-btn adp-btn--secondary" rel="noopener noreferrer"><?php esc_html_e( 'Official store', 'apk-directory-pro' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</article>

			<div class="adp-meta-grid">
				<?php
				$meta_items = [
					__( 'Version', 'apk-directory-pro' )     => adp_get_meta( $post_id, 'current_version' ),
					__( 'Size', 'apk-directory-pro' )        => adp_format_bytes( (int) adp_get_meta( $post_id, 'file_size_bytes', 0 ) ),
					__( 'Requirement', 'apk-directory-pro' ) => adp_get_meta( $post_id, 'android_requirement' ),
					__( 'Package', 'apk-directory-pro' )     => adp_get_meta( $post_id, 'package_name' ),
					__( 'Developer', 'apk-directory-pro' )   => adp_get_developer( $post_id ),
					__( 'Price', 'apk-directory-pro' )       => adp_get_meta( $post_id, 'price_type', 'free' ),
				];
				foreach ( $meta_items as $label => $value ) :
					if ( ! $value ) continue;
					?>
					<div class="adp-meta-grid__item">
						<span class="adp-meta-grid__label"><?php echo esc_html( $label ); ?></span>
						<span class="adp-meta-grid__value"><?php echo esc_html( (string) $value ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<?php get_template_part( 'template-parts/app/trust-strip' ); ?>
			<?php get_template_part( 'template-parts/app/screenshots' ); ?>
			<?php get_template_part( 'template-parts/app/video' ); ?>

			<div class="adp-article-content">
				<?php if ( ! adp_get_meta( $post_id, 'disable_toc' ) ) : ?>
					<?php get_template_part( 'template-parts/app/toc' ); ?>
				<?php endif; ?>
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<div class="adp-page-links"><span class="adp-page-links__label">' . esc_html__( 'Pages:', 'apk-directory-pro' ) . '</span>',
						'after'  => '</div>',
					)
				);
				?>
			</div>

			<?php if ( $whats_new = adp_get_meta( $post_id, 'whats_new' ) ) : ?>
				<section class="adp-section">
					<h2><?php esc_html_e( "What's New", 'apk-directory-pro' ); ?></h2>
					<div class="adp-whats-new"><?php echo wp_kses_post( $whats_new ); ?></div>
				</section>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/app/technical-details' ); ?>
			<?php get_template_part( 'template-parts/app/version-history' ); ?>
			<?php get_template_part( 'template-parts/app/install-guide' ); ?>
			<?php get_template_part( 'template-parts/app/safety-notice' ); ?>
			<?php get_template_part( 'template-parts/app/related' ); ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<section class="adp-section adp-reviews">
					<h2><?php esc_html_e( 'Reviews', 'apk-directory-pro' ); ?></h2>
					<?php comments_template(); ?>
				</section>
			<?php endif; ?>
		</div>

		<aside class="adp-single-app__sidebar" aria-label="<?php esc_attr_e( 'App sidebar', 'apk-directory-pro' ); ?>">
			<div class="adp-sidebar-card adp-sidebar-download">
				<a href="<?php echo esc_url( $download_url ); ?>" class="adp-btn adp-btn--primary adp-btn--block"><?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></a>
			</div>
			<?php if ( is_active_sidebar( 'app-sidebar' ) ) : ?>
				<?php dynamic_sidebar( 'app-sidebar' ); ?>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/app/related', 'sidebar' ); ?>
		</aside>
	</div>

	<div class="adp-mobile-download-bar" role="region" aria-label="<?php esc_attr_e( 'Download', 'apk-directory-pro' ); ?>">
		<a href="<?php echo esc_url( $download_url ); ?>" class="adp-btn adp-btn--primary adp-btn--block"><?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></a>
	</div>
</main>
<?php
get_footer();
