<?php
/**
 * Trust strip section.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();

$items = array();

if ( Adp\Theme\get_app_meta( $post_id, 'verified' ) === '1' ) {
	$items[] = array(
		'icon'  => 'verified',
		'label' => __( 'Verified source', 'apk-directory-pro' ),
	);
}

if ( Adp\Theme\get_app_meta( $post_id, 'virus_scanned' ) === '1' ) {
	$items[] = array(
		'icon'  => 'shield',
		'label' => __( 'Virus scanned', 'apk-directory-pro' ),
	);
}

$sig_check = Adp\Theme\get_app_meta( $post_id, 'signature_verified' );
if ( $sig_check === '1' ) {
	$items[] = array(
		'icon'  => 'shield',
		'label' => __( 'Signature verified', 'apk-directory-pro' ),
	);
}

if ( empty( $items ) ) {
	return;
}
?>
<section class="adp-trust-strip adp-container" aria-label="<?php esc_attr_e( 'Trust indicators', 'apk-directory-pro' ); ?>">
	<ul class="adp-trust-strip__list">
		<?php foreach ( $items as $item ) : ?>
			<li class="adp-trust-strip__item">
				<?php Adp\Theme\icon( $item['icon'] ); ?>
				<span><?php echo esc_html( $item['label'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
