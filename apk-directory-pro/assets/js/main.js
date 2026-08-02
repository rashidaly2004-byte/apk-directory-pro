/**
 * Main entry point — APK Directory Pro
 */

import { initDarkMode } from './dark-mode.js';
import { initNav } from './nav.js';

document.addEventListener('DOMContentLoaded', () => {
	initDarkMode();
	initNav();
	initStickyDownload();
	initLoadMore();
	initReportForms();
});

/**
 * Report form submission handler.
 */
function initReportForms() {
	document.querySelectorAll('[data-adp-report-form]').forEach((form) => {
		const postId = form.dataset.postId;
		const status = form.querySelector('[data-adp-report-status]');
		const restBase = window.adpTheme?.adpRestUrl || '/wp-json/adp/v1/';

		form.addEventListener('submit', async (e) => {
			e.preventDefault();
			const formData = new FormData(form);
			const payload = {
				reason: formData.get('reason'),
				details: formData.get('details') || '',
				email: formData.get('email') || '',
				consent: formData.get('consent') === '1',
				company: formData.get('company') || '',
				nonce: window.adpTheme?.reportNonce || '',
			};

			if (status) {
				status.hidden = false;
				status.textContent = window.adpTheme?.i18n?.loading || 'Loading…';
			}

			try {
				const res = await fetch(`${restBase}apps/${postId}/report`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': window.adpTheme?.nonce || '',
					},
					body: JSON.stringify(payload),
				});
				const data = await res.json();
				if (!res.ok) {
					throw new Error(data.message || 'Submission failed');
				}
				if (status) {
					status.textContent = data.message || 'Thank you!';
				}
				form.reset();
			} catch (err) {
				if (status) {
					status.textContent = err.message;
				}
			}
		});
	});
}

/**
 * Sticky mobile download bar visibility.
 */
function initStickyDownload() {
	const bar = document.querySelector('[data-adp-sticky-download]');
	const hero = document.querySelector('.adp-app-hero');
	if (!bar || !hero) return;

	bar.removeAttribute('hidden');

	const observer = new IntersectionObserver(
		([entry]) => {
			bar.classList.toggle('is-visible', !entry.isIntersecting);
		},
		{ threshold: 0, rootMargin: '0px 0px -60px 0px' }
	);

	observer.observe(hero);
}

/**
 * Load more button enhancement (progressive).
 */
function initLoadMore() {
	const btn = document.querySelector('[data-adp-load-more]');
	if (!btn) return;

	btn.addEventListener('click', async () => {
		const page = parseInt(btn.dataset.page || '1', 10) + 1;
		const max = parseInt(btn.dataset.max || '1', 10);
		const list = document.querySelector('[data-adp-app-list]');
		if (!list || page > max) return;

		btn.disabled = true;
		btn.textContent = window.adpTheme?.i18n?.loading || 'Loading…';

		const url = new URL(window.location.href);
		url.searchParams.set('paged', String(page));

		try {
			const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
			const html = await res.text();
			const parser = new DOMParser();
			const doc = parser.parseFromString(html, 'text/html');
			const items = doc.querySelectorAll('[data-adp-app-row], .adp-app-row, .adp-app-grid-item');

			items.forEach((item) => list.appendChild(item));
			btn.dataset.page = String(page);

			if (page >= max) {
				btn.closest('.adp-load-more-wrap')?.remove();
			} else {
				btn.disabled = false;
				btn.textContent = window.adpTheme?.i18n?.loadMore || 'Load more';
			}
		} catch {
			btn.disabled = false;
			btn.textContent = window.adpTheme?.i18n?.loadMore || 'Load more';
		}
	});
}
