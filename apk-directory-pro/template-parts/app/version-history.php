<?php
$post_id = get_the_ID();
if ( ! class_exists( 'APD\\Core\\Versions\\Repository' ) ) {
	return;
}
$repo     = new \APD\Core\Versions\Repository();
$versions = $repo->find_by_app( $post_id, 10 );
$versions_url = trailingslashit( get_permalink( $post_id ) ) . 'versions/';
?>
<section class="adp-section adp-version-history">
	<div class="adp-section__header">
		<h2><?php esc_html_e( 'Version history', 'apk-directory-pro' ); ?></h2>
		<?php if ( count( $versions ) > 5 ) : ?>
			<a href="<?php echo esc_url( $versions_url ); ?>" class="adp-section__link"><?php esc_html_e( 'All versions', 'apk-directory-pro' ); ?></a>
		<?php endif; ?>
	</div>
	<?php if ( empty( $versions ) ) : ?>
		<p><?php esc_html_e( 'No version history available.', 'apk-directory-pro' ); ?></p>
	<?php else : ?>
		<div class="adp-version-list">
			<?php foreach ( $versions as $v ) :
				$controller = new \APD\Core\Downloads\Controller();
				$dl_url     = $controller->get_download_url( (int) $v['id'], $post_id );
				?>
				<details class="adp-version-item" <?php echo $v['is_current'] ? 'open' : ''; ?>>
					<summary>
						<span class="adp-version-item__name"><?php echo esc_html( $v['version_name'] ); ?></span>
						<?php if ( $v['is_current'] ) : ?>
							<span class="adp-badge"><?php esc_html_e( 'Current', 'apk-directory-pro' ); ?></span>
						<?php endif; ?>
						<span class="adp-version-item__meta"><?php echo esc_html( size_format( (int) $v['file_size_bytes'] ) ); ?></span>
					</summary>
					<div class="adp-version-item__body">
						<?php if ( $v['changelog'] ) : ?>
							<div class="adp-version-item__changelog"><?php echo wp_kses_post( $v['changelog'] ); ?></div>
						<?php endif; ?>
						<a href="<?php echo esc_url( $dl_url ); ?>" class="adp-btn adp-btn--small"><?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></a>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
