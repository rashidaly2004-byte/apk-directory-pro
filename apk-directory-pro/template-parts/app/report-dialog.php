<?php
/**
 * Report issue dialog template.
 */
?>
<div id="adp-report-dialog" class="adp-report-dialog" hidden role="dialog" aria-modal="true" aria-labelledby="adp-report-title">
	<div class="adp-report-dialog__overlay" data-report-close></div>
	<div class="adp-report-dialog__panel">
		<button type="button" class="adp-report-dialog__close" data-report-close aria-label="<?php esc_attr_e( 'Close', 'apk-directory-pro' ); ?>">×</button>
		<h2 id="adp-report-title"><?php esc_html_e( 'Report an issue', 'apk-directory-pro' ); ?></h2>
		<form id="adp-report-form" class="adp-report-form">
			<p>
				<label for="adp-report-reason"><?php esc_html_e( 'Reason', 'apk-directory-pro' ); ?></label>
				<select id="adp-report-reason" name="reason" required>
					<option value=""><?php esc_html_e( 'Select…', 'apk-directory-pro' ); ?></option>
					<option value="broken_link"><?php esc_html_e( 'Broken link', 'apk-directory-pro' ); ?></option>
					<option value="outdated"><?php esc_html_e( 'Outdated version', 'apk-directory-pro' ); ?></option>
					<option value="malware"><?php esc_html_e( 'Malware concern', 'apk-directory-pro' ); ?></option>
					<option value="copyright"><?php esc_html_e( 'Copyright', 'apk-directory-pro' ); ?></option>
					<option value="incorrect"><?php esc_html_e( 'Incorrect information', 'apk-directory-pro' ); ?></option>
					<option value="other"><?php esc_html_e( 'Other', 'apk-directory-pro' ); ?></option>
				</select>
			</p>
			<p>
				<label for="adp-report-details"><?php esc_html_e( 'Details', 'apk-directory-pro' ); ?></label>
				<textarea id="adp-report-details" name="details" rows="4" required></textarea>
			</p>
			<p>
				<label for="adp-report-email"><?php esc_html_e( 'Email (optional)', 'apk-directory-pro' ); ?></label>
				<input type="email" id="adp-report-email" name="email" />
			</p>
			<p class="adp-honeypot" aria-hidden="true" style="position:absolute;left:-9999px;">
				<label for="adp-report-hp"><?php esc_html_e( 'Leave empty', 'apk-directory-pro' ); ?></label>
				<input type="text" id="adp-report-hp" name="hp" tabindex="-1" autocomplete="off" />
			</p>
			<p>
				<label>
					<input type="checkbox" id="adp-report-consent" name="consent" value="1" required />
					<?php esc_html_e( 'I understand this report may be reviewed by site staff. Personal data is handled per the privacy policy.', 'apk-directory-pro' ); ?>
				</label>
			</p>
			<p id="adp-report-status" class="adp-report-status" role="status" aria-live="polite"></p>
			<div class="adp-report-actions">
				<button type="button" class="adp-btn adp-btn--secondary" data-report-close><?php esc_html_e( 'Cancel', 'apk-directory-pro' ); ?></button>
				<button type="submit" class="adp-btn adp-btn--primary"><?php esc_html_e( 'Submit report', 'apk-directory-pro' ); ?></button>
			</div>
		</form>
	</div>
</div>
