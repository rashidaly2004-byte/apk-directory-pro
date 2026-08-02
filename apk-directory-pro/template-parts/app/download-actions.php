<?php
/**
 * Download actions section.
 *
 * @package AdpTheme
 *
 * @var array $args Template args.
 */

$post_id  = get_the_ID();
$context  = $args['context'] ?? 'main';
$download = Adp\Theme\get_app_meta( $post_id, 'download_url' );
$version  = Adp\Theme\get_app_meta( $post_id, 'version' );
$size     = Adp\Theme\get_app_meta( $post_id, 'file_size' );
$size_fmt = is_numeric( $size ) ? Adp\Theme\format_bytes( (int) $size ) : $size;

$wrapper_class = $context === 'sidebar' ? 'adp-download-actions adp-download-actions--sidebar' : 'adp-download-actions adp-container';
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php if ( $download ) : ?>
		<a href="<?php echo esc_url( $download ); ?>" class="adp-btn adp-btn--primary adp-btn--download" rel="nofollow noopener" download>
			<?php Adp\Theme\icon( 'download' ); ?>
			<span><?php esc_html_e( 'Download APK', 'apk-directory-pro' ); ?></span>
		</a>
	<?php else : ?>
		<span class="adp-btn adp-btn--primary adp-btn--download adp-btn--disabled" aria-disabled="true">
			<?php Adp\Theme\icon( 'download' ); ?>
			<span><?php esc_html_e( 'Download unavailable', 'apk-directory-pro' ); ?></span>
		</span>
	<?php endif; ?>

	<dl class="adp-download-meta">
		<?php if ( $version ) : ?>
			<div class="adp-download-meta__item">
				<dt><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></dt>
				<dd><?php echo esc_html( $version ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( $size_fmt ) : ?>
			<div class="adp-download-meta__item">
				<dt><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></dt>
				<dd><?php echo esc_html( $size_fmt ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>
</div>
