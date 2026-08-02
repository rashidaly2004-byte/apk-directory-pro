(function () {
	'use strict';

	var dialog = document.getElementById('adp-report-dialog');
	var form = document.getElementById('adp-report-form');
	var statusEl = document.getElementById('adp-report-status');
	var appId = null;
	var lastFocus = null;

	if (!dialog || !form) return;

	function openReport(appIdValue) {
		appId = appIdValue;
		lastFocus = document.activeElement;
		dialog.hidden = false;
		document.body.style.overflow = 'hidden';
		var first = dialog.querySelector('#adp-report-reason');
		if (first) first.focus();
	}

	function closeReport() {
		dialog.hidden = true;
		document.body.style.overflow = '';
		form.reset();
		if (statusEl) statusEl.textContent = '';
		if (lastFocus) lastFocus.focus();
	}

	document.querySelectorAll('.adp-report-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			openReport(btn.getAttribute('data-app-id'));
		});
	});

	dialog.querySelectorAll('[data-report-close]').forEach(function (el) {
		el.addEventListener('click', closeReport);
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && !dialog.hidden) closeReport();
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		if (!appId) return;

		if (document.getElementById('adp-report-hp').value) {
			closeReport();
			return;
		}

		var payload = {
			reason: document.getElementById('adp-report-reason').value,
			details: document.getElementById('adp-report-details').value,
			email: document.getElementById('adp-report-email').value,
			consent: document.getElementById('adp-report-consent').checked
		};

		if (statusEl) statusEl.textContent = 'Submitting…';

		fetch('/wp-json/adp/v1/apps/' + appId + '/report', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.adpReportNonce || ''
			},
			body: JSON.stringify(payload)
		})
			.then(function (res) {
				if (!res.ok) throw new Error('failed');
				if (statusEl) statusEl.textContent = 'Report submitted. Thank you.';
				setTimeout(closeReport, 2000);
			})
			.catch(function () {
				if (statusEl) statusEl.textContent = 'Could not submit report. Please try again later.';
			});
	});
})();
