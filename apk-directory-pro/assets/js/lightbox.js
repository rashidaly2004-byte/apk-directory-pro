/**
 * Screenshot lightbox — APK Directory Pro
 */

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-adp-lightbox-gallery]').forEach(initGallery);
});

/**
 * Initialize a lightbox gallery.
 * @param {HTMLElement} gallery
 */
function initGallery(gallery) {
	const triggers = gallery.querySelectorAll('[data-adp-lightbox-trigger]');
	if (!triggers.length) return;

	const sources = Array.from(triggers).map((t) => t.dataset.src || '');
	let currentIndex = 0;
	let lightbox = null;

	triggers.forEach((trigger, index) => {
		trigger.addEventListener('click', () => {
			currentIndex = index;
			openLightbox();
		});
	});

	/**
	 * Open lightbox overlay.
	 */
	function openLightbox() {
		if (lightbox) return;

		lightbox = document.createElement('div');
		lightbox.className = 'adp-lightbox';
		lightbox.setAttribute('role', 'dialog');
		lightbox.setAttribute('aria-modal', 'true');
		lightbox.setAttribute('aria-label', 'Screenshot viewer');

		const img = document.createElement('img');
		img.className = 'adp-lightbox__image';
		img.src = sources[currentIndex];
		img.alt = '';

		const closeBtn = document.createElement('button');
		closeBtn.className = 'adp-lightbox__close';
		closeBtn.setAttribute('aria-label', 'Close');
		closeBtn.innerHTML = '&times;';
		closeBtn.addEventListener('click', closeLightbox);

		if (sources.length > 1) {
			const prevBtn = document.createElement('button');
			prevBtn.className = 'adp-lightbox__nav adp-lightbox__prev';
			prevBtn.setAttribute('aria-label', 'Previous');
			prevBtn.textContent = '\u2039';
			prevBtn.addEventListener('click', () => navigate(-1, img));

			const nextBtn = document.createElement('button');
			nextBtn.className = 'adp-lightbox__nav adp-lightbox__next';
			nextBtn.setAttribute('aria-label', 'Next');
			nextBtn.textContent = '\u203A';
			nextBtn.addEventListener('click', () => navigate(1, img));

			lightbox.appendChild(prevBtn);
			lightbox.appendChild(nextBtn);
		}

		lightbox.appendChild(img);
		lightbox.appendChild(closeBtn);
		lightbox.addEventListener('click', (e) => {
			if (e.target === lightbox) closeLightbox();
		});

		document.body.appendChild(lightbox);
		document.body.style.overflow = 'hidden';
		closeBtn.focus();

		document.addEventListener('keydown', handleKeydown);
	}

	/**
	 * Close lightbox.
	 */
	function closeLightbox() {
		if (!lightbox) return;
		lightbox.remove();
		lightbox = null;
		document.body.style.overflow = '';
		document.removeEventListener('keydown', handleKeydown);
		triggers[currentIndex]?.focus();
	}

	/**
	 * Navigate between images.
	 * @param {number} dir
	 * @param {HTMLImageElement} img
	 */
	function navigate(dir, img) {
		currentIndex = (currentIndex + dir + sources.length) % sources.length;
		img.src = sources[currentIndex];
	}

	/**
	 * Handle keyboard navigation.
	 * @param {KeyboardEvent} e
	 */
	function handleKeydown(e) {
		if (e.key === 'Escape') closeLightbox();
		if (e.key === 'ArrowLeft') navigate(-1, lightbox.querySelector('.adp-lightbox__image'));
		if (e.key === 'ArrowRight') navigate(1, lightbox.querySelector('.adp-lightbox__image'));
	}
}
