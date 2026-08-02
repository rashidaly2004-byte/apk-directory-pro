<?php
$post_id = get_the_ID();
$rows = [
	__( 'Package name', 'apk-directory-pro' ) => adp_get_meta( $post_id, 'package_name' ),
	__( 'License', 'apk-directory-pro' )       => adp_get_meta( $post_id, 'license' ),
	__( 'Content rating', 'apk-directory-pro' ) => adp_get_meta( $post_id, 'content_rating' ),
	__( 'Official URL', 'apk-directory-pro' )  => adp_get_meta( $post_id, 'official_url' ),
	__( 'Privacy URL', 'apk-directory-pro' )   => adp_get_meta( $post_id, 'privacy_url' ),
];
$has_rows = array_filter( $rows );
if ( empty( $has_rows ) ) {
	return;
}
?>
<section class="adp-section">
	<h2><?php esc_html_e( 'Technical details', 'apk-directory-pro' ); ?></h2>
	<table class="adp-table">
		<tbody>
			<?php foreach ( $rows as $label => $value ) :
				if ( ! $value ) continue;
				?>
				<tr>
					<th><?php echo esc_html( $label ); ?></th>
					<td>
						<?php if ( filter_var( $value, FILTER_VALIDATE_URL ) ) : ?>
							<a href="<?php echo esc_url( $value ); ?>" rel="noopener noreferrer"><?php echo esc_html( $value ); ?></a>
						<?php else : ?>
							<?php echo esc_html( (string) $value ); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
