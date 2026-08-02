(function ($) {
	'use strict';

	function runBatch() {
		$.post(adpMigration.ajaxUrl, {
			action: 'adp_migration_batch',
			nonce: adpMigration.nonce
		}, function (res) {
			if (!res.success) return;
			var s = res.data;
			$('#adp-migration-progress').text(
				'Processed ' + s.processed + ' / ' + s.total + ' (' + s.status + ')'
			);
			if (s.status === 'running') {
				setTimeout(runBatch, 500);
			}
		});
	}

	$('#adp-migration-continue').on('click', runBatch);

	// Auto-continue if migration is running.
	var status = document.querySelector('.wrap strong');
	if (status && status.textContent.indexOf('running') !== -1) {
		runBatch();
	}
})(jQuery);
