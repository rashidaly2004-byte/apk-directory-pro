/**
 * Mobile navigation drawer with focus trap — APK Directory Pro
 */

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Initialize mobile navigation.
 */
export function initNav() {
	const drawer = document.querySelector('[data-adp-drawer]');
	const openBtn = document.querySelector('[data-adp-menu-open]');
	const closeBtns = document.querySelectorAll('[data-adp-menu-close]');

	if (!drawer || !openBtn) return;

	let previousFocus = null;

	openBtn.addEventListener('click', () => {
		previousFocus = document.activeElement;
		openDrawer(drawer, openBtn);
	});

	closeBtns.forEach((btn) => {
		btn.addEventListener('click', () => closeDrawer(drawer, openBtn, previousFocus));
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
			closeDrawer(drawer, openBtn, previousFocus);
		}

		if (e.key === 'Tab' && drawer.classList.contains('is-open')) {
			trapFocus(e, drawer);
		}
	});
}

/**
 * Open the drawer.
 * @param {HTMLElement} drawer
 * @param {HTMLElement} openBtn
 */
function openDrawer(drawer, openBtn) {
	drawer.removeAttribute('hidden');
	requestAnimationFrame(() => {
		drawer.classList.add('is-open');
	});
	openBtn.setAttribute('aria-expanded', 'true');
	document.body.style.overflow = 'hidden';

	const panel = drawer.querySelector('.adp-drawer__panel');
	const firstFocusable = panel?.querySelector(FOCUSABLE);
	if (firstFocusable) firstFocusable.focus();
}

/**
 * Close the drawer.
 * @param {HTMLElement} drawer
 * @param {HTMLElement} openBtn
 * @param {Element|null} previousFocus
 */
function closeDrawer(drawer, openBtn, previousFocus) {
	drawer.classList.remove('is-open');
	openBtn.setAttribute('aria-expanded', 'false');
	document.body.style.overflow = '';

	setTimeout(() => {
		drawer.setAttribute('hidden', '');
	}, 300);

	if (previousFocus && typeof previousFocus.focus === 'function') {
		previousFocus.focus();
	}
}

/**
 * Trap focus within drawer panel.
 * @param {KeyboardEvent} e
 * @param {HTMLElement} drawer
 */
function trapFocus(e, drawer) {
	const panel = drawer.querySelector('.adp-drawer__panel');
	if (!panel) return;

	const focusable = panel.querySelectorAll(FOCUSABLE);
	if (!focusable.length) return;

	const first = focusable[0];
	const last = focusable[focusable.length - 1];

	if (e.shiftKey && document.activeElement === first) {
		e.preventDefault();
		last.focus();
	} else if (!e.shiftKey && document.activeElement === last) {
		e.preventDefault();
		first.focus();
	}
}
