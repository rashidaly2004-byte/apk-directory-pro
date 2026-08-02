<?php
/**
 * Technical details section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();

$tech_fields = array(
	'package_name'    => __( 'Package name', 'apk-directory-pro' ),
	'permissions'     => __( 'Permissions', 'apk-directory-pro' ),
	'content_rating'  => __( 'Content rating', 'apk-directory-pro' ),
	'architecture'    => __( 'Architecture', 'apk-directory-pro' ),
	'md5'             => __( 'MD5 checksum', 'apk-directory-pro' ),
	'sha256'          => __( 'SHA-256 checksum', 'apk-directory-pro' ),
);

$has_tech = false;
foreach ( $tech_fields as $key => $label ) {
	if ( Adp\Theme\get_app_meta( $post_id, $key ) !== '' ) {
		$has_tech = true;
		break;
	}
}

if ( ! $has_tech ) {
	return;
}
?>
<section class="adp-tech-details adp-container" aria-labelledby="adp-tech-details-title">
	<h2 id="adp-tech-details-title" class="adp-section-title"><?php esc_html_e( 'Technical details', 'apk-directory-pro' ); ?></h2>
	<dl class="adp-tech-details__list">
		<?php foreach ( $tech_fields as $key => $label ) : ?>
			<?php $value = Adp\Theme\get_app_meta( $post_id, $key ); ?>
			<?php if ( $value === '' ) {
				continue;
			} ?>
			<div class="adp-tech-details__item">
				<dt><?php echo esc_html( $label ); ?></dt>
				<dd><code><?php echo esc_html( $value ); ?></code></dd>
			</div>
		<?php endforeach; ?>
	</dl>
</section>
