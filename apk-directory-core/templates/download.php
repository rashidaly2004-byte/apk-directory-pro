<?php
/**
 * Download interstitial template.
 *
 * @var WP_Post $app
 * @var array   $version
 * @var string  $token
 * @var string  $file_url
 * @var string  $icon_url
 * @var string  $file_size
 * @var string  $sha256
 * @var string  $scan_status
 * @var string  $disclaimer
 * @var bool    $is_external
 *
 * @package Adp\Core
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo esc_html( sprintf( __( 'Download %1$s %2$s', 'apk-directory-core' ), get_the_title( $app ), $version['version_name'] ?? '' ) ); ?></title>
	<style>
		:root { --adp-primary: #18a957; --adp-text: #17202a; --adp-muted: #667085; --adp-border: #e4e7ec; --adp-surface: #fff; }
		body { font-family: system-ui, sans-serif; background: #f5f7fa; color: var(--adp-text); margin: 0; padding: 2rem 1rem; line-height: 1.55; }
		.adp-download { max-width: 560px; margin: 0 auto; background: var(--adp-surface); border: 1px solid var(--adp-border); border-radius: 14px; padding: 2rem; }
		.adp-download__header { display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; }
		.adp-download__icon { width: 72px; height: 72px; border-radius: 14px; object-fit: cover; }
		.adp-download__title { margin: 0 0 .25rem; font-size: 1.25rem; }
		.adp-download__version { color: var(--adp-muted); margin: 0; }
		.adp-download__meta { margin: 1rem 0; padding: 1rem; background: #f5f7fa; border-radius: 10px; font-size: .9rem; }
		.adp-download__meta dt { font-weight: 600; margin-top: .5rem; }
		.adp-download__meta dt:first-child { margin-top: 0; }
		.adp-download__meta dd { margin: .15rem 0 0; word-break: break-all; }
		.adp-download__warning { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 10px; padding: 1rem; margin: 1rem 0; font-size: .9rem; }
		.adp-download__btn { display: block; width: 100%; padding: .875rem 1.5rem; background: var(--adp-primary); color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; min-height: 44px; }
		.adp-download__btn:hover { background: #128345; color: #fff; }
		.adp-download__disclaimer { font-size: .8rem; color: var(--adp-muted); margin-top: 1rem; }
	</style>
</head>
<body>
	<main class="adp-download" role="main">
		<header class="adp-download__header">
			<?php if ( ! empty( $icon_url ) ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="" class="adp-download__icon" width="72" height="72" />
			<?php endif; ?>
			<div>
				<h1 class="adp-download__title"><?php echo esc_html( get_the_title( $app ) ); ?></h1>
				<p class="adp-download__version"><?php echo esc_html( sprintf( __( 'Version %s', 'apk-directory-core' ), $version['version_name'] ?? '' ) ); ?></p>
			</div>
		</header>

		<dl class="adp-download__meta">
			<dt><?php esc_html_e( 'File Type', 'apk-directory-core' ); ?></dt>
			<dd><?php echo esc_html( strtoupper( (string) ( $version['file_type'] ?? 'apk' ) ) ); ?></dd>
			<dt><?php esc_html_e( 'File Size', 'apk-directory-core' ); ?></dt>
			<dd><?php echo esc_html( $file_size ); ?></dd>
			<?php if ( ! empty( $sha256 ) ) : ?>
				<dt><?php esc_html_e( 'SHA-256', 'apk-directory-core' ); ?></dt>
				<dd><code><?php echo esc_html( $sha256 ); ?></code></dd>
			<?php endif; ?>
			<dt><?php esc_html_e( 'Scan Status', 'apk-directory-core' ); ?></dt>
			<dd><?php echo esc_html( ucfirst( (string) $scan_status ) ); ?></dd>
		</dl>

		<?php if ( $is_external ) : ?>
			<div class="adp-download__warning" role="alert">
				<?php esc_html_e( 'This file is hosted on an external server. We cannot verify its safety. Proceed at your own risk.', 'apk-directory-core' ); ?>
			</div>
		<?php else : ?>
			<div class="adp-download__warning" role="alert">
				<?php esc_html_e( 'Install only apps from sources you trust. Enable "Install from unknown sources" only when necessary and disable it afterward.', 'apk-directory-core' ); ?>
			</div>
		<?php endif; ?>

		<a href="<?php echo esc_url( $file_url ); ?>" class="adp-download__btn" rel="nofollow">
			<?php esc_html_e( 'Download Now', 'apk-directory-core' ); ?>
		</a>

		<?php if ( ! empty( $disclaimer ) ) : ?>
			<p class="adp-download__disclaimer"><?php echo esc_html( (string) $disclaimer ); ?></p>
		<?php else : ?>
			<p class="adp-download__disclaimer">
				<?php esc_html_e( 'Files are provided for informational purposes. This site does not guarantee safety, official status, or compatibility.', 'apk-directory-core' ); ?>
			</p>
		<?php endif; ?>
	</main>
</body>
</html>
