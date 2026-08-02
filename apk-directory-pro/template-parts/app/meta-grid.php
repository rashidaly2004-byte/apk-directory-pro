<?php
/**
 * App metadata grid.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();

$fields = array(
	'version'       => __( 'Version', 'apk-directory-pro' ),
	'file_size'     => __( 'Size', 'apk-directory-pro' ),
	'updated_at'    => __( 'Updated', 'apk-directory-pro' ),
	'downloads'     => __( 'Downloads', 'apk-directory-pro' ),
	'min_android'   => __( 'Requires Android', 'apk-directory-pro' ),
	'price_type'    => __( 'Price', 'apk-directory-pro' ),
	'file_type'     => __( 'File type', 'apk-directory-pro' ),
	'package_name'  => __( 'Package name', 'apk-directory-pro' ),
);

$has_data = false;
foreach ( $fields as $key => $label ) {
	if ( Adp\Theme\get_app_meta( $post_id, $key ) !== '' ) {
		$has_data = true;
		break;
	}
}

$dev_terms = get_the_terms( $post_id, 'adp_developer' );
$cat_terms = get_the_terms( $post_id, 'adp_app_category' );
$plat_terms = get_the_terms( $post_id, 'adp_platform' );

if ( ! $has_data && ! $dev_terms && ! $cat_terms && ! $plat_terms ) {
	return;
}
?>
<section class="adp-meta-grid adp-container" aria-labelledby="adp-meta-grid-title">
	<h2 id="adp-meta-grid-title" class="adp-section-title"><?php esc_html_e( 'App information', 'apk-directory-pro' ); ?></h2>
	<dl class="adp-meta-grid__list">
		<?php foreach ( $fields as $key => $label ) : ?>
			<?php
			$value = Adp\Theme\get_app_meta( $post_id, $key );
			if ( $value === '' ) {
				continue;
			}
			if ( $key === 'file_size' && is_numeric( $value ) ) {
				$value = Adp\Theme\format_bytes( (int) $value );
			}
			if ( $key === 'downloads' && is_numeric( $value ) ) {
				$value = Adp\Theme\format_downloads( (int) $value );
			}
			if ( $key === 'price_type' ) {
				$value = $value === 'free' ? __( 'Free', 'apk-directory-pro' ) : __( 'Paid', 'apk-directory-pro' );
			}
			?>
			<div class="adp-meta-grid__item">
				<dt><?php echo esc_html( $label ); ?></dt>
				<dd><?php echo esc_html( $value ); ?></dd>
			</div>
		<?php endforeach; ?>

		<?php if ( $dev_terms && ! is_wp_error( $dev_terms ) ) : ?>
			<div class="adp-meta-grid__item">
				<dt><?php esc_html_e( 'Developer', 'apk-directory-pro' ); ?></dt>
				<dd><a href="<?php echo esc_url( get_term_link( $dev_terms[0] ) ); ?>"><?php echo esc_html( $dev_terms[0]->name ); ?></a></dd>
			</div>
		<?php endif; ?>

		<?php if ( $cat_terms && ! is_wp_error( $cat_terms ) ) : ?>
			<div class="adp-meta-grid__item">
				<dt><?php esc_html_e( 'Category', 'apk-directory-pro' ); ?></dt>
				<dd><a href="<?php echo esc_url( get_term_link( $cat_terms[0] ) ); ?>"><?php echo esc_html( $cat_terms[0]->name ); ?></a></dd>
			</div>
		<?php endif; ?>

		<?php if ( $plat_terms && ! is_wp_error( $plat_terms ) ) : ?>
			<div class="adp-meta-grid__item">
				<dt><?php esc_html_e( 'Platform', 'apk-directory-pro' ); ?></dt>
				<dd><?php echo esc_html( implode( ', ', wp_list_pluck( $plat_terms, 'name' ) ) ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>
</section>
