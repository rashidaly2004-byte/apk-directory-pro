<?php
/**
 * Comments template.
 *
 * @package AdpTheme
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="adp-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="adp-comments__title">
			<?php
			$comment_count = get_comments_number();
			printf(
				/* translators: 1: comment count */
				esc_html( _n( '%1$s comment', '%1$s comments', $comment_count, 'apk-directory-pro' ) ),
				esc_html( number_format_i18n( $comment_count ) )
			);
			?>
		</h2>

		<ol class="adp-comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 48,
				'callback'    => null,
			) );
			?>
		</ol>

		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="adp-comments__closed"><?php esc_html_e( 'Comments are closed.', 'apk-directory-pro' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply'          => __( 'Leave a comment', 'apk-directory-pro' ),
		'label_submit'         => __( 'Post comment', 'apk-directory-pro' ),
		'class_submit'         => 'adp-btn adp-btn--primary',
		'comment_notes_before' => '',
	) );
	?>
</section>
