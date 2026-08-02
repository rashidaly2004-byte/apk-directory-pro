<?php
$post_id = get_the_ID();
if ( ! $post_id || 'adp_app' !== get_post_type( $post_id ) ) {
	return '';
}

$url  = \Adp\Core\Blocks\Helpers::get_download_url( $post_id );
$size = (int) \Adp\Core\Content\Meta::get( $post_id, '_adp_file_size_bytes' );

if ( '' === $url ) {
	return '<p class="adp-block-empty">' . esc_html__( 'No download available.', 'apk-directory-core' ) . '</p>';
}

$label = __( 'Download APK', 'apk-directory-core' );
if ( $size > 0 ) {
	$label .= ' (' . size_format( $size ) . ')';
}

return sprintf(
	'<a href="%s" class="adp-download-button" rel="nofollow">%s</a>',
	esc_url( $url ),
	esc_html( $label )
);
