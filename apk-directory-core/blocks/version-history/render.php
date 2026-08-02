<?php
$post_id = get_the_ID();
if ( ! $post_id || 'adp_app' !== get_post_type( $post_id ) ) {
	return '';
}

$repo     = new \Adp\Core\Versions\Repository();
$limit    = max( 1, min( 50, (int) ( $attributes['count'] ?? 10 ) ) );
$versions = $repo->list_by_app( $post_id, $limit );

if ( empty( $versions ) ) {
	return '<p class="adp-block-empty">' . esc_html__( 'No version history.', 'apk-directory-core' ) . '</p>';
}

$signer = new \Adp\Core\Downloads\Signer();
$html   = '<div class="adp-version-history"><table class="adp-version-table"><thead><tr>';
$html  .= '<th>' . esc_html__( 'Version', 'apk-directory-core' ) . '</th>';
$html  .= '<th>' . esc_html__( 'Date', 'apk-directory-core' ) . '</th>';
$html  .= '<th>' . esc_html__( 'Size', 'apk-directory-core' ) . '</th>';
$html  .= '<th></th></tr></thead><tbody>';

foreach ( $versions as $version ) {
	$url = $signer->get_interstitial_url( (int) $version['id'] );
	$html .= '<tr>';
	$html .= '<td>' . esc_html( $version['version_name'] );
	if ( $version['is_current'] ) {
		$html .= ' <span class="adp-badge">' . esc_html__( 'Current', 'apk-directory-core' ) . '</span>';
	}
	$html .= '</td>';
	$html .= '<td>' . esc_html( $version['release_date'] ? gmdate( 'Y-m-d', strtotime( (string) $version['release_date'] ) ) : '—' ) . '</td>';
	$html .= '<td>' . esc_html( size_format( (int) $version['file_size_bytes'] ) ) . '</td>';
	$html .= '<td><a href="' . esc_url( $url ) . '" rel="nofollow">' . esc_html__( 'Download', 'apk-directory-core' ) . '</a></td>';
	$html .= '</tr>';
}
$html .= '</tbody></table></div>';
return $html;
