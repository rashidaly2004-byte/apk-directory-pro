(function () {
	'use strict';

	var lightbox = document.getElementById('adp-lightbox');
	if (!lightbox) return;

	var img = lightbox.querySelector('.adp-lightbox__img');
	var closeBtn = lightbox.querySelector('.adp-lightbox__close');
	var lastFocus = null;

	function open(src, alt) {
		lastFocus = document.activeElement;
		img.src = src;
		img.alt = alt || '';
		lightbox.hidden = false;
		closeBtn.focus();
		document.body.style.overflow = 'hidden';
	}

	function close() {
		lightbox.hidden = true;
		img.src = '';
		document.body.style.overflow = '';
		if (lastFocus) lastFocus.focus();
	}

	document.querySelectorAll('.adp-screenshot-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var src = btn.getAttribute('data-lightbox-src');
			var altImg = btn.querySelector('img');
			open(src, altImg ? altImg.alt : '');
		});
	});

	closeBtn.addEventListener('click', close);
	lightbox.addEventListener('click', function (e) {
		if (e.target === lightbox) close();
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && !lightbox.hidden) close();
	});
})();
