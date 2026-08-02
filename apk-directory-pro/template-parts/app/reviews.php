<?php
/**
 * App reviews section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$aggregate = array( 'count' => 0, 'average' => 0.0 );

if ( class_exists( '\Adp\Core\Reviews\Schema' ) ) {
	$aggregate = \Adp\Core\Reviews\Schema::get_aggregate( $post_id );
}

$reviews_enabled = true;
$settings          = get_option( 'adp_core_settings', array() );
if ( is_array( $settings ) && array_key_exists( 'reviews_enabled', $settings ) ) {
	$reviews_enabled = (bool) $settings['reviews_enabled'];
}

if ( ! $reviews_enabled ) {
	return;
}
?>
<section
	class="adp-reviews adp-container"
	id="adp-reviews"
	data-adp-reviews
	data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
	aria-labelledby="adp-reviews-title"
>
	<header class="adp-reviews__header">
		<h2 id="adp-reviews-title" class="adp-section-title"><?php esc_html_e( 'Reviews', 'apk-directory-pro' ); ?></h2>
		<?php if ( $aggregate['count'] > 0 ) : ?>
			<?php Adp\Theme\render_star_rating( (float) $aggregate['average'], (int) $aggregate['count'] ); ?>
		<?php endif; ?>
	</header>

	<?php if ( comments_open( $post_id ) ) : ?>
		<form class="adp-reviews__form" data-adp-review-form>
			<div class="adp-reviews__field">
				<label for="adp-review-rating"><?php esc_html_e( 'Your rating', 'apk-directory-pro' ); ?></label>
				<select id="adp-review-rating" name="rating" required>
					<option value=""><?php esc_html_e( 'Select rating', 'apk-directory-pro' ); ?></option>
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<option value="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( sprintf(
							/* translators: %d: star count */
							_n( '%d star', '%d stars', $i, 'apk-directory-pro' ),
							$i
						) ); ?></option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="adp-reviews__field">
				<label for="adp-review-author"><?php esc_html_e( 'Name', 'apk-directory-pro' ); ?></label>
				<input type="text" id="adp-review-author" name="author" required autocomplete="name" />
			</div>
			<div class="adp-reviews__field">
				<label for="adp-review-email"><?php esc_html_e( 'Email', 'apk-directory-pro' ); ?></label>
				<input type="email" id="adp-review-email" name="email" required autocomplete="email" />
			</div>
			<div class="adp-reviews__field">
				<label for="adp-review-content"><?php esc_html_e( 'Review', 'apk-directory-pro' ); ?></label>
				<textarea id="adp-review-content" name="content" rows="4" required></textarea>
			</div>
			<input type="text" name="website" class="adp-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />
			<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Submit review', 'apk-directory-pro' ); ?></button>
			<p class="adp-reviews__status" data-adp-review-status hidden></p>
		</form>
	<?php endif; ?>

	<div class="adp-reviews__list-wrap" data-adp-reviews-list>
		<?php
		if ( have_comments() ) {
			wp_list_comments(
				array(
					'style'       => 'ol',
					'callback'    => static function ( $comment, $args, $depth ) {
						if ( $comment instanceof WP_Comment ) {
							Adp\Theme\render_review_comment( $comment, $args, $depth );
						}
					},
					'avatar_size' => 40,
					'type'        => class_exists( '\Adp\Core\Reviews\Schema' ) ? \Adp\Core\Reviews\Schema::COMMENT_TYPE : 'comment',
				)
			);
		}
		?>
	</div>
</section>
