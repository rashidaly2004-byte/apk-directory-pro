<?php
/**
 * App report form.
 *
 * @package AdpTheme
 */

$post_id = get_the_ID();
$reasons = class_exists( '\Adp\Core\Reports\Controller' )
	? \Adp\Core\Reports\Controller::REASONS
	: array( 'broken_link', 'outdated_version', 'malware_concern', 'copyright', 'incorrect_information', 'other' );

$reason_labels = array(
	'broken_link'           => __( 'Broken download link', 'apk-directory-pro' ),
	'outdated_version'      => __( 'Outdated version', 'apk-directory-pro' ),
	'malware_concern'       => __( 'Malware concern', 'apk-directory-pro' ),
	'copyright'             => __( 'Copyright issue', 'apk-directory-pro' ),
	'incorrect_information' => __( 'Incorrect information', 'apk-directory-pro' ),
	'other'                 => __( 'Other', 'apk-directory-pro' ),
);
?>
<section class="adp-report adp-container" id="adp-report" aria-labelledby="adp-report-title">
	<h2 id="adp-report-title" class="adp-section-title"><?php esc_html_e( 'Report an issue', 'apk-directory-pro' ); ?></h2>
	<p class="adp-report__intro"><?php esc_html_e( 'Found a problem with this listing? Let us know and our team will review it.', 'apk-directory-pro' ); ?></p>

	<form class="adp-report__form" data-adp-report-form data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<div class="adp-report__field">
			<label for="adp-report-reason"><?php esc_html_e( 'Reason', 'apk-directory-pro' ); ?></label>
			<select id="adp-report-reason" name="reason" required>
				<option value=""><?php esc_html_e( 'Select a reason', 'apk-directory-pro' ); ?></option>
				<?php foreach ( $reasons as $reason ) : ?>
					<option value="<?php echo esc_attr( $reason ); ?>">
						<?php echo esc_html( $reason_labels[ $reason ] ?? $reason ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="adp-report__field">
			<label for="adp-report-details"><?php esc_html_e( 'Details', 'apk-directory-pro' ); ?></label>
			<textarea id="adp-report-details" name="details" rows="4" placeholder="<?php esc_attr_e( 'Describe the issue…', 'apk-directory-pro' ); ?>"></textarea>
		</div>

		<div class="adp-report__field">
			<label for="adp-report-email"><?php esc_html_e( 'Email (optional)', 'apk-directory-pro' ); ?></label>
			<input type="email" id="adp-report-email" name="email" autocomplete="email" />
		</div>

		<div class="adp-report__field adp-report__consent">
			<label>
				<input type="checkbox" name="consent" value="1" required />
				<?php esc_html_e( 'I confirm this report is submitted in good faith.', 'apk-directory-pro' ); ?>
			</label>
		</div>

		<input type="text" name="company" class="adp-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

		<button type="submit" class="adp-btn adp-btn--secondary"><?php esc_html_e( 'Submit report', 'apk-directory-pro' ); ?></button>
		<p class="adp-report__status" data-adp-report-status hidden></p>
	</form>
</section>
