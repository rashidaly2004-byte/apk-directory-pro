/**
 * Archive filters — APK Directory Pro
 */

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-adp-auto-submit]').forEach((el) => {
		el.addEventListener('change', () => {
			const form = el.closest('form');
			if (form) form.submit();
		});
	});

	// Persist view preference
	const viewToggle = document.querySelector('.adp-view-toggle');
	if (viewToggle) {
		const active = viewToggle.querySelector('.is-active');
		if (active) {
			try {
				localStorage.setItem('adp-archive-view', active.textContent?.trim().toLowerCase() || 'list');
			} catch {
				// localStorage unavailable
			}
		}
	}
});
