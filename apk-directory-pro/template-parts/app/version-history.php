<?php
/**
 * Version history section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$versions = array();
$total    = 0;
$versions_url = '';

if ( class_exists( '\Adp\Core\Versions\Repository' ) ) {
	$repo         = new \Adp\Core\Versions\Repository();
	$versions     = $repo->list_by_app( $post_id, 5 );
	$total        = $repo->count_by_app( $post_id );
	$versions_url = Adp\Theme\get_app_versions_url( $post_id );
}

if ( empty( $versions ) ) {
	$history = get_post_meta( $post_id, '_adp_version_history', true );
	if ( ! is_array( $history ) || empty( $history ) ) {
		return;
	}
	?>
	<section class="adp-version-history adp-container" aria-labelledby="adp-version-history-title">
		<h2 id="adp-version-history-title" class="adp-section-title"><?php esc_html_e( 'Version history', 'apk-directory-pro' ); ?></h2>
		<table class="adp-version-history__table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Date', 'apk-directory-pro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Notes', 'apk-directory-pro' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $history as $entry ) : ?>
					<?php if ( ! is_array( $entry ) ) {
						continue;
					} ?>
					<tr>
						<td><?php echo esc_html( $entry['version'] ?? '—' ); ?></td>
						<td><?php echo esc_html( $entry['date'] ?? '—' ); ?></td>
						<td><?php echo esc_html( isset( $entry['size'] ) && is_numeric( $entry['size'] ) ? Adp\Theme\format_bytes( (int) $entry['size'] ) : ( $entry['size'] ?? '—' ) ); ?></td>
						<td><?php echo esc_html( $entry['notes'] ?? '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
	<?php
	return;
}

$signer = class_exists( '\Adp\Core\Downloads\Signer' ) ? new \Adp\Core\Downloads\Signer() : null;
?>
<section class="adp-version-history adp-container" aria-labelledby="adp-version-history-title">
	<header class="adp-section__header">
		<h2 id="adp-version-history-title" class="adp-section-title"><?php esc_html_e( 'Version history', 'apk-directory-pro' ); ?></h2>
		<?php if ( $total > count( $versions ) && $versions_url ) : ?>
			<a href="<?php echo esc_url( $versions_url ); ?>" class="adp-section__link">
				<?php esc_html_e( 'View all versions', 'apk-directory-pro' ); ?>
			</a>
		<?php endif; ?>
	</header>
	<table class="adp-version-history__table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Released', 'apk-directory-pro' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $versions as $version ) : ?>
				<tr>
					<td>
						<?php echo esc_html( $version['version_name'] ); ?>
						<?php if ( ! empty( $version['is_current'] ) ) : ?>
							<span class="adp-badge"><?php esc_html_e( 'Current', 'apk-directory-pro' ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php
						echo ! empty( $version['release_date'] )
							? esc_html( gmdate( 'Y-m-d', strtotime( (string) $version['release_date'] ) ) )
							: '—';
						?>
					</td>
					<td><?php echo esc_html( Adp\Theme\format_bytes( (int) $version['file_size_bytes'] ) ); ?></td>
					<td>
						<?php if ( $signer ) : ?>
							<a href="<?php echo esc_url( $signer->get_interstitial_url( (int) $version['id'] ) ); ?>" rel="nofollow">
								<?php esc_html_e( 'Download', 'apk-directory-pro' ); ?>
							</a>
						<?php else : ?>
							—
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
