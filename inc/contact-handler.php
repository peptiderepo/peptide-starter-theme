<?php
/**
 * Contact Form AJAX Handler
 *
 * Support contact form from the settings panel. Sanitises inputs,
 * rejects header-injection characters in the sender name, enforces
 * rate limits, honors the honeypot, and emails the admin.
 *
 * Unverified users may still use contact (it isn't a write endpoint
 * on user data) — the verify gate is only applied to user-data tools.
 *
 * @see template-parts/settings-panel.php — renders the form
 * @see inc/rate-limiter.php — Peptide_Starter_Rate_Limiter
 * @see inc/config.php — contact_topics allowlist + rate limit
 * @see functions.php — includes this file
 *
 * What: AJAX contact endpoint with abuse controls.
 * Who calls it: admin-ajax.php via settings-panel.js POST.
 * Dependencies: wp_mail, rate limiter, honeypot helper.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX contact form submission.
 *
 * Side effects: may call wp_mail. Writes rate-limit transient.
 *
 * @return void Emits JSON and exits.
 */
function peptide_starter_ajax_contact() {
	if ( ! isset( $_POST['ps_contact_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_contact_nonce'] ) ), 'ps_contact_form' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	if ( peptide_starter_honeypot_triggered( 'ps_hp_contact' ) ) {
		// Fake success to poison the bot feedback loop.
		wp_send_json_success( array( 'message' => __( 'Message sent successfully. We will respond within 48 hours.', 'peptide-starter' ) ) );
	}

	if ( ! Peptide_Starter_Rate_Limiter::check( 'contact', 'ip' ) ) {
		wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again later.', 'peptide-starter' ) ) );
	}
	Peptide_Starter_Rate_Limiter::record( 'contact', 'ip' );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$topic   = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	// Reject header-injection characters in the name explicitly — don't
	// rely on sanitize_text_field side-effects for email-header safety.
	if ( preg_match( '/[\r\n,<>]/', $name ) ) {
		peptide_starter_contact_log_failure( 'name_injection' );
		wp_send_json_error( array( 'message' => __( 'Invalid characters in name.', 'peptide-starter' ) ) );
	}

	if ( '' === $name || '' === $email || '' === $topic || '' === $message ) {
		peptide_starter_contact_log_failure( 'missing_field' );
		wp_send_json_error( array( 'message' => __( 'All fields are required.', 'peptide-starter' ) ) );
	}

	if ( ! is_email( $email ) ) {
		peptide_starter_contact_log_failure( 'bad_email' );
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'peptide-starter' ) ) );
	}

	$allowed_topics = (array) peptide_starter_config_get( 'contact_topics', array( 'bug', 'feature', 'data', 'other' ) );
	if ( ! in_array( $topic, $allowed_topics, true ) ) {
		peptide_starter_contact_log_failure( 'bad_topic' );
		wp_send_json_error( array( 'message' => __( 'Invalid topic selected.', 'peptide-starter' ) ) );
	}

	$topic_labels = array(
		'bug'     => __( 'Bug Report', 'peptide-starter' ),
		'feature' => __( 'Feature Request', 'peptide-starter' ),
		'data'    => __( 'Data Correction', 'peptide-starter' ),
		'other'   => __( 'Other', 'peptide-starter' ),
	);

	$request_id = substr( wp_hash( microtime() . wp_rand() ), 0, 10 );

	$subject = sprintf(
		/* translators: 1: topic label, 2: sender name, 3: request id */
		__( '[Peptide Repo] %1$s from %2$s (req %3$s)', 'peptide-starter' ),
		isset( $topic_labels[ $topic ] ) ? $topic_labels[ $topic ] : $topic,
		$name,
		$request_id
	);

	$body  = sprintf( __( 'Name: %s', 'peptide-starter' ), $name ) . "\n";
	$body .= sprintf( __( 'Email: %s', 'peptide-starter' ), $email ) . "\n";
	$body .= sprintf( __( 'Topic: %s', 'peptide-starter' ), isset( $topic_labels[ $topic ] ) ? $topic_labels[ $topic ] : $topic ) . "\n";
	$body .= sprintf( __( 'Request ID: %s', 'peptide-starter' ), $request_id ) . "\n\n";
	$body .= __( 'Message:', 'peptide-starter' ) . "\n";
	$body .= $message;

	$headers = array(
		'Reply-To: ' . $email,
		'Content-Type: text/plain; charset=UTF-8',
	);

	$sent = wp_mail( get_option( 'admin_email' ), $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( array( 'message' => __( 'Message sent successfully. We will respond within 48 hours.', 'peptide-starter' ) ) );
	}

	peptide_starter_contact_log_failure( 'mail_failed:' . $request_id );
	wp_send_json_error( array( 'message' => __( 'Failed to send message. Please try again later.', 'peptide-starter' ) ) );
}
add_action( 'wp_ajax_ps_contact_submit', 'peptide_starter_ajax_contact' );
add_action( 'wp_ajax_nopriv_ps_contact_submit', 'peptide_starter_ajax_contact' );

/**
 * Log a contact-handler failure with no PII — only a reason code and
 * a hashed IP so admins can correlate without exposing submitters.
 *
 * @param string $reason Short reason code.
 * @return void
 */
function peptide_starter_contact_log_failure( $reason ) {
	$hash = substr( hash( 'sha256', peptide_starter_get_client_ip() ), 0, 12 );
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( sprintf( 'peptide_starter_contact_failure reason=%s ip_hash=%s', $reason, $hash ) );
}
