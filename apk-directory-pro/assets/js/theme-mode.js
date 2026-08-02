(function () {
	'use strict';

	var toggle = document.querySelector('.adp-theme-toggle');
	var root = document.documentElement;

	function setTheme(mode) {
		root.setAttribute('data-theme', mode);
		try {
			localStorage.setItem('adp-theme-mode', mode);
		} catch (e) {}
	}

	if (toggle) {
		toggle.addEventListener('click', function () {
			var current = root.getAttribute('data-theme');
			var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
			var isDark = current === 'dark' || (!current && prefersDark);
			setTheme(isDark ? 'light' : 'dark');
		});
	}
})();
