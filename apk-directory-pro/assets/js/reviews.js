/**
 * Reviews progressive enhancement — APK Directory Pro
 */

document.addEventListener('DOMContentLoaded', () => {
	const reviewsSection = document.querySelector('[data-adp-reviews]');
	if (!reviewsSection) return;

	const postId = reviewsSection.dataset.postId;
	if (!postId) return;

	initReviewForm(reviewsSection, postId);
	loadReviews(postId, reviewsSection);
});

/**
 * Handle review form submission.
 * @param {HTMLElement} section
 * @param {string} postId
 */
function initReviewForm(section, postId) {
	const form = section.querySelector('[data-adp-review-form]');
	if (!form) return;

	const status = section.querySelector('[data-adp-review-status]');
	const restBase = window.adpTheme?.adpRestUrl || '/wp-json/adp/v1/';

	form.addEventListener('submit', async (e) => {
		e.preventDefault();
		const formData = new FormData(form);

		const payload = {
			rating: formData.get('rating'),
			author: formData.get('author'),
			email: formData.get('email'),
			content: formData.get('content'),
			website: formData.get('website') || '',
			nonce: window.adpTheme?.reviewNonce || '',
		};

		if (status) {
			status.hidden = false;
			status.textContent = window.adpTheme?.i18n?.loading || 'Loading…';
		}

		try {
			const res = await fetch(`${restBase}apps/${postId}/reviews`, {
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
}

/**
 * Load reviews via REST API if available.
 * @param {string} postId
 * @param {HTMLElement} container
 */
async function loadReviews(postId, container) {
	const listWrap = container.querySelector('[data-adp-reviews-list]');
	if (!listWrap || listWrap.children.length > 0) return;

	const restUrl = window.adpTheme?.restUrl || '/wp-json/wp/v2/';

	try {
		const res = await fetch(`${restUrl}comments?post=${postId}&per_page=5&orderby=date&order=desc&type=adp_review`);
		if (!res.ok) return;

		const comments = await res.json();
		if (!comments.length) return;

		const list = document.createElement('ol');
		list.className = 'adp-reviews__list';

		comments.forEach((comment) => {
			const li = document.createElement('li');
			li.className = 'adp-reviews__item';
			li.innerHTML = `
				<div class="adp-reviews__author">${escapeHtml(comment.author_name || 'Anonymous')}</div>
				<div class="adp-reviews__content">${comment.content?.rendered || ''}</div>
			`;
			list.appendChild(li);
		});

		listWrap.appendChild(list);
	} catch {
		// Reviews unavailable — graceful degradation
	}
}

/**
 * Escape HTML for safe text insertion.
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
	const div = document.createElement('div');
	div.textContent = str;
	return div.innerHTML;
}
