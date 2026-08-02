(function () {
	'use strict';

	var list = document.querySelector('.adp-app-list');
	var toggles = document.querySelectorAll('.adp-layout-toggle [data-layout]');
	if (!list || !toggles.length) return;

	var saved = null;
	try {
		saved = localStorage.getItem('adp-archive-layout');
	} catch (e) {}

	if (saved) {
		list.setAttribute('data-layout', saved);
		toggles.forEach(function (btn) {
			btn.setAttribute('aria-pressed', btn.getAttribute('data-layout') === saved ? 'true' : 'false');
		});
	}

	toggles.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var layout = btn.getAttribute('data-layout');
			list.setAttribute('data-layout', layout);
			toggles.forEach(function (b) {
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});
			try {
				localStorage.setItem('adp-archive-layout', layout);
			} catch (e) {}
		});
	});
})();
