<?php
$slot = sanitize_key( (string) ( $attributes['slot'] ?? 'sidebar' ) );
if ( ! current_user_can( 'manage_options' ) ) {
	$allowed = apply_filters( 'adp_ad_slot_allowed', array( 'sidebar', 'header', 'footer', 'content' ), $slot );
	if ( ! in_array( $slot, (array) $allowed, true ) ) {
		return '';
	}
}

$code = get_option( 'adp_ad_slot_' . $slot, '' );
if ( '' === $code && ! empty( $attributes['placeholder'] ) ) {
	return '<div class="adp-ad-slot adp-ad-slot--' . esc_attr( $slot ) . '" data-slot="' . esc_attr( $slot ) . '"></div>';
}

if ( '' === $code ) {
	return '';
}

return '<div class="adp-ad-slot adp-ad-slot--' . esc_attr( $slot ) . '" data-slot="' . esc_attr( $slot ) . '">' . wp_kses_post( $code ) . '</div>';
