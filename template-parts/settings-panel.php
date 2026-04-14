<?php
/**
 * Template Part: Settings Panel
 *
 * Slide-out panel triggered by the settings icon in the header.
 * Contains contact form, compound data submission link, and support info.
 *
 * @see assets/js/settings-panel.js — open/close toggle
 * @see functions.php — AJAX handler for contact form
 * @see header.php — settings icon trigger
 *
 * What: Slide-out support panel with contact form and links.
 * Who calls it: get_template_part( 'template-parts/settings', 'panel' ) in footer.php.
 * Dependencies: None — self-contained HTML.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();
$user_name    = is_user_logged_in() ? $current_user->display_name : '';
$user_email   = is_user_logged_in() ? $current_user->user_email : '';
?>

<!-- Settings Panel Overlay -->
<div class="ps-settings-overlay" id="ps-settings-overlay" aria-hidden="true"></div>

<!-- Settings Panel -->
<aside class="ps-settings-panel" id="ps-settings-panel" aria-hidden="true" aria-label="<?php esc_attr_e( 'Support panel', 'peptide-starter' ); ?>">
	<div class="ps-settings-panel__header">
		<h2><?php esc_html_e( 'Support', 'peptide-starter' ); ?></h2>
		<button class="ps-settings-panel__close" id="ps-settings-close" aria-label="<?php esc_attr_e( 'Close panel', 'peptide-starter' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
				<path d="M15 5L5 15M5 5L15 15"/>
			</svg>
		</button>
	</div>

	<div class="ps-settings-panel__body">
		<!-- Contact Engineering Form -->
		<div class="ps-settings-section">
			<h3><?php esc_html_e( 'Contact Engineering', 'peptide-starter' ); ?></h3>
			<form id="ps-contact-form" class="ps-contact-form" novalidate>
				<?php wp_nonce_field( 'ps_contact_form', 'ps_contact_nonce' ); ?>
				<input type="hidden" name="action" value="ps_contact_submit">
				<?php peptide_starter_render_honeypot( 'contact' ); ?>

				<div class="ps-form-group">
					<label for="ps-contact-name"><?php esc_html_e( 'Name', 'peptide-starter' ); ?></label>
					<input type="text" id="ps-contact-name" name="name" value="<?php echo esc_attr( $user_name ); ?>" required>
				</div>

				<div class="ps-form-group">
					<label for="ps-contact-email"><?php esc_html_e( 'Email', 'peptide-starter' ); ?></label>
					<input type="email" id="ps-contact-email" name="email" value="<?php echo esc_attr( $user_email ); ?>" required>
				</div>

				<div class="ps-form-group">
					<label for="ps-contact-topic"><?php esc_html_e( 'Topic', 'peptide-starter' ); ?></label>
					<select id="ps-contact-topic" name="topic" required>
						<option value=""><?php esc_html_e( 'Select a topic', 'peptide-starter' ); ?></option>
						<option value="bug"><?php esc_html_e( 'Bug Report', 'peptide-starter' ); ?></option>
						<option value="feature"><?php esc_html_e( 'Feature Request', 'peptide-starter' ); ?></option>
						<option value="data"><?php esc_html_e( 'Data Correction', 'peptide-starter' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'peptide-starter' ); ?></option>
					</select>
				</div>

				<div class="ps-form-group">
					<label for="ps-contact-message"><?php esc_html_e( 'Description', 'peptide-starter' ); ?></label>
					<textarea id="ps-contact-message" name="message" rows="4" required></textarea>
				</div>

				<button type="submit" class="ps-btn ps-btn-primary ps-btn-full">
					<?php esc_html_e( 'Send Message', 'peptide-starter' ); ?>
				</button>

				<div id="ps-contact-status" class="ps-contact-status" aria-live="polite"></div>
			</form>
		</div>

		<!-- Submit Compound Data -->
		<div class="ps-settings-section">
			<h3><?php esc_html_e( 'Submit Compound Data', 'peptide-starter' ); ?></h3>
			<p><?php esc_html_e( 'Have data on a peptide not in our directory? Submit it for review.', 'peptide-starter' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/compound-request' ) ); ?>" class="ps-btn ps-btn-secondary ps-btn-full">
				<?php esc_html_e( 'Submit Compound', 'peptide-starter' ); ?>
			</a>
		</div>

		<!-- Support Policy -->
		<div class="ps-settings-section ps-settings-disclaimer">
			<h3><?php esc_html_e( 'Support Policy', 'peptide-starter' ); ?></h3>
			<p>
				<?php esc_html_e( 'Peptide Repo is a research reference database only. All data is provided for informational and educational purposes. This platform does not provide medical advice, diagnoses, or treatment recommendations. Always consult qualified professionals for clinical decisions.', 'peptide-starter' ); ?>
			</p>
		</div>
	</div>
</aside>
