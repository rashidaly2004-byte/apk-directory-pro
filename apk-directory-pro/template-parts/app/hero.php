<?php
/**
 * App hero section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$data    = Adp\Theme\get_app_card_data( $post_id );
$verified = Adp\Theme\get_app_meta( $post_id, 'verified' ) === '1';
?>
<section class="adp-app-hero adp-container">
	<div class="adp-app-hero__main">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="adp-app-hero__icon">
				<?php the_post_thumbnail( 'adp-app-icon', array( 'class' => 'adp-app-hero__icon-img' ) ); ?>
			</div>
		<?php else : ?>
			<div class="adp-app-hero__icon adp-app-hero__icon--placeholder" aria-hidden="true"></div>
		<?php endif; ?>

		<div class="adp-app-hero__info">
			<h1 class="adp-app-hero__title">
				<?php the_title(); ?>
				<?php if ( $verified ) : ?>
					<span class="adp-app-hero__verified" title="<?php esc_attr_e( 'Verified', 'apk-directory-pro' ); ?>">
						<?php Adp\Theme\icon( 'verified' ); ?>
						<span class="adp-sr-only"><?php esc_html_e( 'Verified app', 'apk-directory-pro' ); ?></span>
					</span>
				<?php endif; ?>
			</h1>

			<?php if ( $data['developer'] ) : ?>
				<p class="adp-app-hero__developer"><?php echo esc_html( $data['developer'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['has_rating'] ) ) : ?>
				<div class="adp-app-hero__rating" data-rating-type="user">
					<span class="adp-app-hero__rating-label"><?php esc_html_e( 'User rating', 'apk-directory-pro' ); ?></span>
					<?php Adp\Theme\render_star_rating( $data['rating'], $data['rating_count'] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $data['editor_rating'] ) ) : ?>
				<div class="adp-app-hero__rating" data-rating-type="editor">
					<span class="adp-app-hero__rating-label"><?php esc_html_e( 'Editor score', 'apk-directory-pro' ); ?></span>
					<?php Adp\Theme\render_star_rating( (float) $data['editor_rating'], 0 ); ?>
				</div>
			<?php endif; ?>

			<?php
			$short_desc = Adp\Theme\get_app_meta( $post_id, 'short_description' );
			if ( $short_desc ) :
				?>
				<p class="adp-app-hero__desc"><?php echo esc_html( $short_desc ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
