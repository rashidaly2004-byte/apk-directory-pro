<?php
/**
 * Download interstitial template.
 *
 * @var WP_Post $app
 * @var array   $version
 * @var string  $token
 */

defined( 'ABSPATH' ) || exit;

$download_url = add_query_arg( 'adp_file', '1', home_url( '/download/' . rawurlencode( $token ) . '/' ) );
$icon         = get_the_post_thumbnail_url( $app, 'thumbnail' );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $app->post_title ); ?> — <?php esc_html_e( 'Download', 'apk-directory-pro' ); ?></title>
	<style>
		body { font-family: system-ui, sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; line-height: 1.55; }
		.adp-dl-card { border: 1px solid #e4e7ec; border-radius: 14px; padding: 1.5rem; }
		.adp-dl-icon { width: 64px; height: 64px; border-radius: 12px; }
		.adp-dl-btn { display: inline-block; background: #18a957; color: #fff; padding: 12px 24px; border-radius: 10px; text-decoration: none; min-height: 44px; }
		.adp-dl-meta { color: #667085; font-size: 0.9rem; }
		.adp-warning { background: #fef3c7; padding: 1rem; border-radius: 10px; margin-top: 1rem; }
	</style>
</head>
<body>
	<div class="adp-dl-card">
		<?php if ( $icon ) : ?>
			<img src="<?php echo esc_url( $icon ); ?>" alt="" class="adp-dl-icon" width="64" height="64" />
		<?php endif; ?>
		<h1><?php echo esc_html( $app->post_title ); ?></h1>
		<p class="adp-dl-meta">
			<?php echo esc_html( $version['version_name'] ); ?>
			· <?php echo esc_html( size_format( (int) $version['file_size_bytes'] ) ); ?>
			· <?php echo esc_html( strtoupper( $version['file_type'] ) ); ?>
		</p>
		<?php if ( $version['sha256'] ) : ?>
			<p class="adp-dl-meta">SHA-256: <code><?php echo esc_html( $version['sha256'] ); ?></code></p>
		<?php endif; ?>
		<p><a href="<?php echo esc_url( $download_url ); ?>" class="adp-dl-btn"><?php esc_html_e( 'Download file', 'apk-directory-pro' ); ?></a></p>
		<div class="adp-warning">
			<p><?php esc_html_e( 'Install only apps from sources you trust. This site does not guarantee file safety.', 'apk-directory-pro' ); ?></p>
		</div>
	</div>
</body>
</html>
