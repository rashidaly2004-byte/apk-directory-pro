<?php
/**
 * Plugin fallback template for app versions archive.
 *
 * @package Adp\Core
 */

use Adp\Core\Versions\Frontend;

defined( 'ABSPATH' ) || exit;

$data     = Frontend::get_page_data();
$app      = $data['app'];
$versions = $data['versions'];

if ( ! $app ) {
	status_header( 404 );
	nocache_headers();
	wp_die( esc_html__( 'App not found.', 'apk-directory-core' ), '', array( 'response' => 404 ) );
}

get_header();
?>
<article class="adp-versions-page adp-container">
	<?php
	$crumbs = apply_filters( 'adp_theme_breadcrumbs', array() );
	if ( ! empty( $crumbs ) ) :
		?>
		<nav class="adp-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'apk-directory-core' ); ?>">
			<ol class="adp-breadcrumbs__list">
				<?php foreach ( $crumbs as $index => $crumb ) : ?>
					<li class="adp-breadcrumbs__item">
						<?php if ( ! empty( $crumb['url'] ) && $index < count( $crumbs ) - 1 ) : ?>
							<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
						<?php else : ?>
							<span aria-current="page"><?php echo esc_html( $crumb['label'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>

	<header class="adp-versions-page__header">
		<h1 class="adp-versions-page__title">
			<?php
			printf(
				/* translators: %s: app title */
				esc_html__( '%s — All Versions', 'apk-directory-core' ),
				esc_html( get_the_title( $app ) )
			);
			?>
		</h1>
		<p class="adp-versions-page__meta">
			<?php
			printf(
				/* translators: %d: total version count */
				esc_html( _n( '%d version', '%d versions', $data['total'], 'apk-directory-core' ) ),
				(int) $data['total']
			);
			?>
		</p>
	</header>

	<?php if ( empty( $versions ) ) : ?>
		<p><?php esc_html_e( 'No versions published yet.', 'apk-directory-core' ); ?></p>
	<?php else : ?>
		<table class="adp-version-history__table adp-versions-page__table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Version', 'apk-directory-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Released', 'apk-directory-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Size', 'apk-directory-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Type', 'apk-directory-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Download', 'apk-directory-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $versions as $version ) : ?>
					<tr>
						<td>
							<?php echo esc_html( $version['version_name'] ); ?>
							<?php if ( ! empty( $version['is_current'] ) ) : ?>
								<span class="adp-badge"><?php esc_html_e( 'Current', 'apk-directory-core' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							echo ! empty( $version['release_date'] )
								? esc_html( gmdate( 'Y-m-d', strtotime( (string) $version['release_date'] ) ) )
								: '—';
							?>
						</td>
						<td><?php echo esc_html( size_format( (int) $version['file_size_bytes'] ) ); ?></td>
						<td><?php echo esc_html( strtoupper( (string) ( $version['file_type'] ?? 'apk' ) ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( Frontend::get_version_download_url( $version ) ); ?>" rel="nofollow">
								<?php esc_html_e( 'Download', 'apk-directory-core' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $data['pages'] > 1 ) : ?>
			<nav class="adp-pagination" aria-label="<?php esc_attr_e( 'Versions pagination', 'apk-directory-core' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'total'     => $data['pages'],
							'current'   => $data['page'],
							'format'    => '?paged=%#%',
							'prev_text' => __( '&laquo; Previous', 'apk-directory-core' ),
							'next_text' => __( 'Next &raquo;', 'apk-directory-core' ),
						)
					)
				);
				?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</article>
<?php
get_footer();
