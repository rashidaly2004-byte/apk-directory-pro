/**
 * Live search with debounce and ARIA combobox — APK Directory Pro
 */

const DEBOUNCE_MS = 250;
const MIN_CHARS = 2;
let activeIndex = -1;

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-adp-search-form]').forEach(initSearchForm);
});

/**
 * Initialize a search form instance.
 * @param {HTMLFormElement} form
 */
function initSearchForm(form) {
	const input = form.querySelector('[data-adp-search-input]');
	const results = form.querySelector('[data-adp-search-results]');
	if (!input || !results) return;

	let debounceTimer = null;
	let abortController = null;
	activeIndex = -1;

	input.addEventListener('input', () => {
		clearTimeout(debounceTimer);
		const query = input.value.trim();

		if (query.length < MIN_CHARS) {
			hideResults(results, input);
			return;
		}

		debounceTimer = setTimeout(() => search(query, results, input), DEBOUNCE_MS);
	});

	input.addEventListener('keydown', (e) => {
		const items = results.querySelectorAll('[role="option"]');
		if (!items.length || results.hidden) return;

		switch (e.key) {
			case 'ArrowDown':
				e.preventDefault();
				activeIndex = Math.min(activeIndex + 1, items.length - 1);
				updateActiveOption(items, activeIndex);
				break;
			case 'ArrowUp':
				e.preventDefault();
				activeIndex = Math.max(activeIndex - 1, 0);
				updateActiveOption(items, activeIndex);
				break;
			case 'Enter':
				if (activeIndex >= 0 && items[activeIndex]) {
					e.preventDefault();
					const link = items[activeIndex].querySelector('a');
					if (link) link.click();
				}
				break;
			case 'Escape':
				hideResults(results, input);
				break;
		}
	});

	document.addEventListener('click', (e) => {
		if (!form.contains(e.target)) {
			hideResults(results, input);
		}
	});
}

/**
 * Perform REST search via plugin endpoint.
 * @param {string} query
 * @param {HTMLElement} results
 * @param {HTMLInputElement} input
 */
async function search(query, results, input) {
	if (abortController) abortController.abort();
	abortController = new AbortController();

	const searchBase = window.adpTheme?.searchUrl || '/wp-json/adp/v1/search';
	const endpoint = `${searchBase}?q=${encodeURIComponent(query)}`;

	try {
		const res = await fetch(endpoint, {
			signal: abortController.signal,
			headers: {
				'X-WP-Nonce': window.adpTheme?.nonce || '',
			},
		});

		if (!res.ok) throw new Error('Search failed');

		const payload = await res.json();
		const items = Array.isArray(payload.results) ? payload.results : payload;
		renderResults(items, results, input, query);
	} catch (err) {
		if (err.name !== 'AbortError') {
			renderEmpty(results, input);
		}
	}
}

/**
 * Render search results.
 * @param {Array} items
 * @param {HTMLElement} results
 * @param {HTMLInputElement} input
 * @param {string} query
 */
function renderResults(items, results, input, query) {
	results.innerHTML = '';
	activeIndex = -1;

	if (!items.length) {
		renderEmpty(results, input);
		return;
	}

	items.forEach((item, index) => {
		const li = document.createElement('li');
		li.className = 'adp-search-results__item';
		li.setAttribute('role', 'option');
		li.id = `adp-search-option-${index}`;

		const title = item.title || '';
		const typeLabel = item.type_label || (item.type === 'app' ? 'App' : 'Blog');
		const meta = [item.developer, item.version].filter(Boolean).join(' · ');
		const icon = item.icon
			? `<img src="${escapeAttr(item.icon)}" alt="" class="adp-search-results__icon" width="32" height="32" loading="lazy" />`
			: '';

		li.innerHTML = `
			<a href="${escapeAttr(item.url)}">
				${icon}
				<span class="adp-search-results__body">
					<span class="adp-search-results__title">${title}</span>
					${meta ? `<span class="adp-search-results__meta">${escapeHtml(meta)}</span>` : ''}
				</span>
				<span class="adp-search-results__type">${escapeHtml(typeLabel)}</span>
			</a>`;
		results.appendChild(li);
	});

	showResults(results, input);
}

/**
 * Render empty state.
 * @param {HTMLElement} results
 * @param {HTMLInputElement} input
 */
function renderEmpty(results, input) {
	const msg = window.adpTheme?.i18n?.noResults || 'No results found';
	results.innerHTML = `<li class="adp-search-results__empty" role="option">${msg}</li>`;
	showResults(results, input);
}

/**
 * Show results dropdown.
 * @param {HTMLElement} results
 * @param {HTMLInputElement} input
 */
function showResults(results, input) {
	results.hidden = false;
	input.setAttribute('aria-expanded', 'true');
}

/**
 * Hide results dropdown.
 * @param {HTMLElement} results
 * @param {HTMLInputElement} input
 */
function hideResults(results, input) {
	results.hidden = true;
	input.setAttribute('aria-expanded', 'false');
}

/**
 * Update active option highlight.
 * @param {NodeListOf<Element>} items
 * @param {number} index
 */
function updateActiveOption(items, index) {
	items.forEach((item, i) => {
		const link = item.querySelector('a');
		if (link) link.setAttribute('aria-selected', i === index ? 'true' : 'false');
	});
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

/**
 * Escape attribute values.
 * @param {string} str
 * @returns {string}
 */
function escapeAttr(str) {
	return escapeHtml(str).replace(/"/g, '&quot;');
}
