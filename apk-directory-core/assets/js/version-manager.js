(function ($) {
	'use strict';

	$('#adp-add-version').on('click', function () {
		$('#adp-version-form').toggle();
	});

	$('#adp-save-version').on('click', function () {
		var data = {
			action: 'adp_version_action',
			nonce: adpVersions.nonce,
			post_id: $('#adp-post-id').val(),
			version_action: 'create',
			version_name: $('#adp-v-name').val(),
			file_size_bytes: $('#adp-v-size').val(),
			file_type: $('#adp-v-type').val(),
			external_url: $('#adp-v-url').val(),
			is_current: $('#adp-v-current').is(':checked') ? 1 : 0
		};
		$.post(adpVersions.ajaxUrl, data, function () {
			location.reload();
		});
	});

	$('.adp-set-current').on('click', function () {
		$.post(adpVersions.ajaxUrl, {
			action: 'adp_version_action',
			nonce: adpVersions.nonce,
			post_id: $('#adp-post-id').val(),
			version_action: 'set_current',
			version_id: $(this).data('id')
		}, function () {
			location.reload();
		});
	});

	$('.adp-delete-version').on('click', function () {
		if (!confirm('Delete this version?')) return;
		$.post(adpVersions.ajaxUrl, {
			action: 'adp_version_action',
			nonce: adpVersions.nonce,
			post_id: $('#adp-post-id').val(),
			version_action: 'delete',
			version_id: $(this).data('id')
		}, function () {
			location.reload();
		});
	});
})(jQuery);
