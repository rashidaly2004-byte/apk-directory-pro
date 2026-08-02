<?php
$post_id = get_the_ID();
if ( ! class_exists( 'APD\\Core\\Versions\\Repository' ) ) {
	return;
}
$repo    = new \APD\Core\Versions\Repository();
$version = $repo->get_current( $post_id );
?>
<div class="adp-trust-strip" role="region" aria-label="<?php esc_attr_e( 'File information', 'apk-directory-pro' ); ?>">
	<?php if ( $version && ! empty( $version['sha256'] ) ) : ?>
		<span class="adp-trust-strip__item"><?php esc_html_e( 'SHA-256 available', 'apk-directory-pro' ); ?></span>
	<?php endif; ?>
	<?php if ( $version ) : ?>
		<span class="adp-trust-strip__item">
			<?php
			$status = $version['virus_scan_status'] ?? 'unknown';
			echo esc_html( sprintf( __( 'Scan: %s', 'apk-directory-pro' ), $status ) );
			?>
		</span>
		<span class="adp-trust-strip__item">
			<?php echo esc_html( sprintf( __( 'Source: %s', 'apk-directory-pro' ), $version['download_type'] ) ); ?>
		</span>
	<?php endif; ?>
</div>
