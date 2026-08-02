/**
 * Dark mode toggle — APK Directory Pro
 */

const STORAGE_KEY = 'adp-color-scheme';

/**
 * Initialize dark mode toggle buttons.
 */
export function initDarkMode() {
	document.querySelectorAll('[data-adp-dark-toggle]').forEach((btn) => {
		btn.addEventListener('click', toggleTheme);
	});
}

/**
 * Toggle between light and dark themes.
 */
function toggleTheme() {
	const current = document.documentElement.getAttribute('data-theme');
	const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
	let next;

	if (current === 'dark') {
		next = 'light';
	} else if (current === 'light') {
		next = 'dark';
	} else {
		next = prefersDark ? 'light' : 'dark';
	}

	applyTheme(next);

	try {
		localStorage.setItem(STORAGE_KEY, next);
	} catch {
		// localStorage unavailable
	}
}

/**
 * Apply theme to document.
 * @param {'light'|'dark'} theme
 */
function applyTheme(theme) {
	document.documentElement.setAttribute('data-theme', theme);
}

// Auto-init when loaded as module entry
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initDarkMode);
} else {
	initDarkMode();
}
