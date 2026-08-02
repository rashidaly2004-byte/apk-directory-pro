<?php
$message = ! empty( $attributes['message'] )
	? sanitize_text_field( (string) $attributes['message'] )
	: __( 'Files on this site are provided for informational purposes. We do not guarantee safety, official status, or compatibility. Always verify downloads and install only from sources you trust.', 'apk-directory-core' );

return sprintf(
	'<aside class="adp-safety-notice" role="note"><p>%s</p></aside>',
	esc_html( $message )
);
