<?php
/**
 * Contact Form AJAX Handler
 *
 * Processes the contact/support form from the settings panel.
 * Sends email via wp_mail() to the site admin.
 *
 * @see template-parts/settings-panel.php — renders the contact form
 * @see assets/js/settings-panel.js — submits form via XMLHttpRequest
 * @see functions.php — includes this file
 *
 * What: AJAX endpoint for the support contact form.
 * Who calls it: WordPress admin-ajax.php via settings-panel.js.
 * Dependencies: wp_mail() for email delivery.
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
 * Validates nonce, sanitizes all inputs, sends email to admin.
 * Works for both authenticated and unauthenticated users.
 *
 * @return void Sends JSON response and exits.
 */
function peptide_starter_ajax_contact() {
	// Verify CSRF nonce.
	if ( ! isset( $_POST['ps_contact_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_contact_nonce'] ) ), 'ps_contact_form' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$topic   = isset( $_POST['topic'] ) ? sanitize_text_field( wp_unslash( $_POST['topic'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	// Validate required fields.
	if ( empty( $name ) || empty( $email ) || empty( $topic ) || empty( $message ) ) {
		wp_send_json_error( array( 'message' => __( 'All fields are required.', 'peptide-starter' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'peptide-starter' ) ) );
	}

	// Allowed topics — reject anything unexpected.
	$allowed_topics = array( 'bug', 'feature', 'data', 'other' );
	if ( ! in_array( $topic, $allowed_topics, true ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid topic selected.', 'peptide-starter' ) ) );
	}

	// Build the email.
	$topic_labels = array(
		'bug'     => __( 'Bug Report', 'peptide-starter' ),
		'feature' => __( 'Feature Request', 'peptide-starter' ),
		'data'    => __( 'Data Correction', 'peptide-starter' ),
		'other'   => __( 'Other', 'peptide-starter' ),
	);

	$subject = sprintf(
		/* translators: 1: topic label, 2: sender name */
		__( '[Peptide Repo] %1$s from %2$s', 'peptide-starter' ),
		$topic_labels[ $topic ],
		$name
	);

	$body  = sprintf( __( 'Name: %s', 'peptide-starter' ), $name ) . "\n";
	$body .= sprintf( __( 'Email: %s', 'peptide-starter' ), $email ) . "\n";
	$body .= sprintf( __( 'Topic: %s', 'peptide-starter' ), $topic_labels[ $topic ] ) . "\n\n";
	$body .= __( 'Message:', 'peptide-starter' ) . "\n";
	$body .= $message;

	$headers = array(
		'Reply-To: ' . $name . ' <' . $email . '>',
		'Content-Type: text/plain; charset=UTF-8',
	);

	$sent = wp_mail( get_option( 'admin_email' ), $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( array( 'message' => __( 'Message sent successfully. We will respond within 48 hours.', 'peptide-starter' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to send message. Please try again later.', 'peptide-starter' ) ) );
	}
}
add_action( 'wp_ajax_ps_contact_submit', 'peptide_starter_ajax_contact' );
add_action( 'wp_ajax_nopriv_ps_contact_submit', 'peptide_starter_ajax_contact' );
