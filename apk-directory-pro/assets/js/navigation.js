(function () {
	'use strict';

	var menuToggle = document.querySelector('.adp-menu-toggle');
	var drawer = document.getElementById('adp-mobile-drawer');
	var closeButtons = document.querySelectorAll('[data-close-drawer]');
	var lastFocus = null;

	function openDrawer() {
		if (!drawer) return;
		lastFocus = document.activeElement;
		drawer.hidden = false;
		menuToggle.setAttribute('aria-expanded', 'true');
		var closeBtn = drawer.querySelector('.adp-drawer__close');
		if (closeBtn) closeBtn.focus();
		document.body.style.overflow = 'hidden';
	}

	function closeDrawer() {
		if (!drawer) return;
		drawer.hidden = true;
		if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
		if (lastFocus) lastFocus.focus();
	}

	if (menuToggle && drawer) {
		menuToggle.addEventListener('click', openDrawer);
		closeButtons.forEach(function (btn) {
			btn.addEventListener('click', closeDrawer);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !drawer.hidden) closeDrawer();
		});
	}
})();
