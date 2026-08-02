<?php
$items = $attributes['items'] ?? array();
if ( empty( $items ) || ! is_array( $items ) ) {
	$items = array(
		array(
			'question' => __( 'Is this app safe to install?', 'apk-directory-core' ),
			'answer'   => __( 'We provide factual metadata only. Always verify file hashes and install from sources you trust.', 'apk-directory-core' ),
		),
		array(
			'question' => __( 'How do I install an APK?', 'apk-directory-core' ),
			'answer'   => __( 'Download the file, enable installation from unknown sources in your device settings if needed, then open the APK file.', 'apk-directory-core' ),
		),
	);
}

$html = '<div class="adp-faq">';
foreach ( $items as $item ) {
	if ( ! is_array( $item ) ) {
		continue;
	}
	$q = sanitize_text_field( (string) ( $item['question'] ?? '' ) );
	$a = wp_kses_post( (string) ( $item['answer'] ?? '' ) );
	if ( '' === $q ) {
		continue;
	}
	$html .= '<details class="adp-faq__item"><summary>' . esc_html( $q ) . '</summary><div class="adp-faq__answer">' . $a . '</div></details>';
}
$html .= '</div>';
return $html;
