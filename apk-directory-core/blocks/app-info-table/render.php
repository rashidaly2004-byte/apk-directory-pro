<?php
$post_id = get_the_ID();
if ( ! $post_id || 'adp_app' !== get_post_type( $post_id ) ) {
	return '';
}

$fields = array(
	__( 'Version', 'apk-directory-core' )     => \Adp\Core\Content\Meta::get( $post_id, '_adp_current_version' ),
	__( 'Package', 'apk-directory-core' )     => \Adp\Core\Content\Meta::get( $post_id, '_adp_package_name' ),
	__( 'Size', 'apk-directory-core' )        => size_format( (int) \Adp\Core\Content\Meta::get( $post_id, '_adp_file_size_bytes' ) ),
	__( 'Android', 'apk-directory-core' )     => \Adp\Core\Content\Meta::get( $post_id, '_adp_android_requirement' ),
	__( 'Content Rating', 'apk-directory-core' ) => \Adp\Core\Content\Meta::get( $post_id, '_adp_content_rating' ),
	__( 'License', 'apk-directory-core' )     => \Adp\Core\Content\Meta::get( $post_id, '_adp_license' ),
);

$developers = wp_get_post_terms( $post_id, 'adp_developer', array( 'fields' => 'names' ) );
if ( ! is_wp_error( $developers ) && ! empty( $developers ) ) {
	$fields[ __( 'Developer', 'apk-directory-core' ) ] = $developers[0];
}

$html = '<table class="adp-info-table"><tbody>';
foreach ( $fields as $label => $value ) {
	if ( '' === (string) $value ) {
		continue;
	}
	$html .= sprintf(
		'<tr><th scope="row">%s</th><td>%s</td></tr>',
		esc_html( $label ),
		esc_html( (string) $value )
	);
}
$html .= '</tbody></table>';
return $html;
