<?php
/**
 * Comments template for app reviews.
 */

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="adp-comments">
	<?php if ( have_comments() ) : ?>
		<ol class="adp-comment-list">
			<?php wp_list_comments( [ 'style' => 'ol', 'short_ping' => true ] ); ?>
		</ol>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<form action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" method="post" class="adp-review-form">
			<h3><?php esc_html_e( 'Leave a review', 'apk-directory-pro' ); ?></h3>
			<p>
				<label for="adp_rating"><?php esc_html_e( 'Rating', 'apk-directory-pro' ); ?></label>
				<select name="adp_rating" id="adp_rating" required>
					<option value=""><?php esc_html_e( 'Select…', 'apk-directory-pro' ); ?></option>
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<option value="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( (string) $i ); ?> <?php esc_html_e( 'stars', 'apk-directory-pro' ); ?></option>
					<?php endfor; ?>
				</select>
			</p>
			<p class="adp-honeypot" aria-hidden="true" style="position:absolute;left:-9999px;">
				<label for="adp_hp"><?php esc_html_e( 'Leave empty', 'apk-directory-pro' ); ?></label>
				<input type="text" name="adp_hp" id="adp_hp" tabindex="-1" autocomplete="off" />
			</p>
			<p>
				<label for="comment"><?php esc_html_e( 'Review', 'apk-directory-pro' ); ?></label>
				<textarea id="comment" name="comment" rows="4" required></textarea>
			</p>
			<?php wp_nonce_field( 'adp_review' ); ?>
			<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( (string) get_the_ID() ); ?>" />
			<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Submit review', 'apk-directory-pro' ); ?></button>
		</form>
	<?php endif; ?>
</div>
