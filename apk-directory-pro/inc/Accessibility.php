<?php

namespace APD\Theme;

final class Accessibility {

	public static function init(): void {
		add_action( 'wp_footer', array( self::class, 'skip_link_focus' ), 20 );
	}

	public static function skip_link_focus(): void {
		// Focus management handled in navigation.js.
	}
}
