<?php
/**
 * Version history page template.
 *
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$repo     = new \APD\Core\Versions\Repository();
$per_page = 20;
$paged    = max( 1, (int) get_query_var( 'paged', 1 ) );
$offset   = ( $paged - 1 ) * $per_page;
$versions = $repo->find_by_app( $post->ID, $per_page, $offset );
$total    = $repo->count_by_app( $post->ID );
$controller = new \APD\Core\Downloads\Controller();

status_header( 200 );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $post->post_title ); ?> — <?php esc_html_e( 'All versions', 'apk-directory-pro' ); ?></title>
	<link rel="canonical" href="<?php echo esc_url( trailingslashit( get_permalink( $post ) ) . 'versions/' ); ?>" />
	<?php wp_head(); ?>
</head>
<body class="adp-versions-page">
	<main class="adp-container" style="max-width:760px;margin:2rem auto;padding:1rem;">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'apk-directory-pro' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'apk-directory-pro' ); ?></a>
			/ <a href="<?php echo esc_url( get_post_type_archive_link( 'adp_app' ) ); ?>"><?php esc_html_e( 'Apps', 'apk-directory-pro' ); ?></a>
			/ <a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
			/ <?php esc_html_e( 'Versions', 'apk-directory-pro' ); ?>
		</nav>
		<h1><?php echo esc_html( $post->post_title ); ?> — <?php esc_html_e( 'Version history', 'apk-directory-pro' ); ?></h1>
		<?php if ( empty( $versions ) ) : ?>
			<p><?php esc_html_e( 'No versions found.', 'apk-directory-pro' ); ?></p>
		<?php else : ?>
			<table class="adp-table" style="width:100%;border-collapse:collapse;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Version', 'apk-directory-pro' ); ?></th>
						<th><?php esc_html_e( 'Date', 'apk-directory-pro' ); ?></th>
						<th><?php esc_html_e( 'Size', 'apk-directory-pro' ); ?></th>
						<th><?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $versions as $v ) : ?>
						<tr>
							<td>
								<?php echo esc_html( $v['version_name'] ); ?>
								<?php if ( $v['is_current'] ) : ?>
									<small>(<?php esc_html_e( 'current', 'apk-directory-pro' ); ?>)</small>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $v['release_date'] ?? '' ); ?></td>
							<td><?php echo esc_html( size_format( (int) $v['file_size_bytes'] ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( $controller->get_download_url( (int) $v['id'], $post->ID ) ); ?>">
									<?php esc_html_e( 'Download', 'apk-directory-pro' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( $total > $per_page ) : ?>
				<p>
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'total'   => (int) ceil( $total / $per_page ),
								'current' => $paged,
							)
						)
					);
					?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
	</main>
	<?php wp_footer(); ?>
</body>
</html>
