<?php
/**
 * App versions archive template.
 *
 * @package AdpTheme
 */

use Adp\Core\Versions\Frontend;

get_header();

$data     = Frontend::get_page_data();
$app      = $data['app'];
$versions = $data['versions'];

if ( ! $app ) {
	get_template_part( 'template-parts/content/none' );
	get_footer();
	return;
}
?>
<article class="adp-versions-page adp-container">
	<?php Adp\Theme\render_breadcrumbs(); ?>

	<header class="adp-versions-page__header">
		<h1 class="adp-versions-page__title">
			<?php
			printf(
				/* translators: %s: app title */
				esc_html__( '%s — All Versions', 'apk-directory-pro' ),
				esc_html( get_the_title( $app ) )
			);
			?>
		</h1>
		<p class="adp-versions-page__meta">
			<a href="<?php echo esc_url( get_permalink( $app ) ); ?>" class="adp-versions-page__back">
				<?php esc_html_e( 'Back to app', 'apk-directory-pro' ); ?>
			</a>
			<span class="adp-versions-page__count">
				<?php
				printf(
					/* translators: %d: version count */
					esc_html( _n( '%d version', '%d versions', $data['total'], 'apk-directory-pro' ) ),
					(int) $data['total']
				);
				?>
			</span>
		</p>
	</header>

	<?php if ( empty( $versions ) ) : ?>
		<p><?php esc_html_e( 'No versions published yet.', 'apk-directory-pro' ); ?></p>
	<?php else : ?>
		<div class="adp-version-history">
			<table class="adp-version-history__table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Released', 'apk-directory-pro' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'apk-directory-pro' ); ?></th>
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
							<td><?php echo esc_html( strtoupper( (string) ( $version['file_type'] ?? 'apk' ) ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( Frontend::get_version_download_url( $version ) ); ?>" class="adp-btn adp-btn--sm adp-btn--primary" rel="nofollow">
									<?php esc_html_e( 'Download', 'apk-directory-pro' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ( $data['pages'] > 1 ) : ?>
			<?php
			get_template_part(
				'template-parts/navigation/pagination',
				null,
				array(
					'total'   => $data['pages'],
					'current' => $data['page'],
					'base'    => Frontend::get_versions_url( $app ) . '%_%',
					'format'  => '?paged=%#%',
				)
			);
			?>
		<?php endif; ?>
	<?php endif; ?>
</article>
<?php
get_footer();
