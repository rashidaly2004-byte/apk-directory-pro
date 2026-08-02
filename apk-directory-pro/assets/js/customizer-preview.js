/**
 * Customizer live preview — APK Directory Pro
 */
/* global wp */

(function () {
	if (typeof wp === 'undefined' || !wp.customize) return;

	wp.customize('adp_site_tagline', (value) => {
		value.bind((newval) => {
			const el = document.querySelector('.adp-header__tagline, .adp-hero__title');
			if (el) el.textContent = newval;
		});
	});
})();
