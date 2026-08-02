<?php
$post_id = get_the_ID();
$note    = adp_get_meta( $post_id, 'disclaimer_note' );
?>
<section class="adp-section adp-safety-notice" role="region" aria-label="<?php esc_attr_e( 'Safety notice', 'apk-directory-pro' ); ?>">
	<h2><?php esc_html_e( 'Safety notice', 'apk-directory-pro' ); ?></h2>
	<?php if ( $note ) : ?>
		<p><?php echo esc_html( $note ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Files are provided by third parties. We do not guarantee safety or authenticity. Verify hashes when available and install only from sources you trust.', 'apk-directory-pro' ); ?></p>
	<?php endif; ?>
	<p>
		<button type="button" class="adp-btn adp-btn--secondary adp-report-btn" data-app-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<?php esc_html_e( 'Report an issue', 'apk-directory-pro' ); ?>
		</button>
	</p>
</section>
