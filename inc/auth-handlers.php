<?php
/**
 * Authentication AJAX Handlers
 *
 * Login + registration endpoints for the branded /auth page. Both are
 * nonce-verified, rate-limited, honeypot-checked, and emit only unified
 * error messages — no enumeration surface.
 *
 * Registration does NOT auto-login — users must click the verification
 * link emailed to them (see inc/email-verification.php).
 *
 * @see page-auth.php — renders the forms
 * @see assets/js/auth.js — AJAX submitter
 * @see inc/rate-limiter.php — Peptide_Starter_Rate_Limiter
 * @see inc/email-verification.php — sends verification email on register
 * @see inc/config.php — thresholds for rate limits, username/password rules
 * @see functions.php — includes this file
 *
 * What: AJAX login / register with unified errors and abuse controls.
 * Who calls it: admin-ajax.php via auth.js POST.
 * Dependencies: wp_signon, wp_create_user, rate limiter, email verification.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unified login failure message.
 *
 * Kept in a helper so every failure path returns byte-identical output —
 * critical for defeating account enumeration via error-message oracle.
 *
 * @return string Translated failure message.
 */
function peptide_starter_login_failure_message() {
	return __( 'Invalid email or password.', 'peptide-starter' );
}

/**
 * Unified registration failure message.
 *
 * @return string Translated failure message.
 */
function peptide_starter_register_failure_message() {
	return __( 'Unable to create account. Please check your entries and try again.', 'peptide-starter' );
}

/**
 * Handle AJAX login.
 *
 * Order of operations:
 *   1. Nonce check (returns security error — separate from auth failure).
 *   2. Honeypot check (silent fake success).
 *   3. Rate-limit check keyed on IP + sha256(lowercased email).
 *   4. Email lookup + wp_signon.
 *   5. On success: reset the limiter; redirect via wp_validate_redirect.
 *
 * Side effects: may set auth cookie, writes rate-limit transient.
 *
 * @return void Emits JSON and exits.
 */
function peptide_starter_ajax_login() {
	if ( ! isset( $_POST['ps_login_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_login_nonce'] ) ), 'ps_auth_login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	// Honeypot — any value means bot. Return fake success, don't log PII.
	if ( peptide_starter_honeypot_triggered( 'ps_hp_login' ) ) {
		wp_send_json_success( array( 'redirect' => home_url( '/' ) ) );
	}

	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password must not be modified.

	$identifier = peptide_starter_hash_identifier( strtolower( $email ) );

	if ( ! Peptide_Starter_Rate_Limiter::check( 'login', $identifier ) ) {
		wp_send_json_error( array( 'message' => peptide_starter_login_failure_message() ) );
	}

	if ( '' === $email || '' === $password ) {
		Peptide_Starter_Rate_Limiter::record( 'login', $identifier );
		wp_send_json_error( array( 'message' => peptide_starter_login_failure_message() ) );
	}

	$user = get_user_by( 'email', $email );
	if ( ! $user ) {
		Peptide_Starter_Rate_Limiter::record( 'login', $identifier );
		wp_send_json_error( array( 'message' => peptide_starter_login_failure_message() ) );
	}

	$creds = array(
		'user_login'    => $user->user_login,
		'user_password' => $password,
		'remember'      => true,
	);

	$signed_in = wp_signon( $creds, is_ssl() );

	if ( is_wp_error( $signed_in ) ) {
		Peptide_Starter_Rate_Limiter::record( 'login', $identifier );
		wp_send_json_error( array( 'message' => peptide_starter_login_failure_message() ) );
	}

	Peptide_Starter_Rate_Limiter::reset( 'login', $identifier );

	$redirect_raw = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );
	$redirect     = wp_validate_redirect( $redirect_raw, home_url( '/' ) );

	// Unverified users land on profile with the verify banner.
	if ( ! peptide_starter_user_is_verified( $signed_in->ID ) ) {
		$redirect = home_url( '/profile?verify_required=1' );
	}

	wp_send_json_success( array( 'redirect' => $redirect ) );
}
add_action( 'wp_ajax_nopriv_ps_auth_login', 'peptide_starter_ajax_login' );

/**
 * Handle AJAX registration.
 *
 * Never auto-logs-in. Sends a verification email and responds with a
 * "check your inbox" message. All validation-failure paths collapse to
 * one message to avoid username/email enumeration.
 *
 * Side effects: creates a user, writes verify meta, sends email.
 *
 * @return void Emits JSON and exits.
 */
function peptide_starter_ajax_register() {
	if ( ! isset( $_POST['ps_register_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_register_nonce'] ) ), 'ps_auth_register' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	if ( peptide_starter_honeypot_triggered( 'ps_hp_register' ) ) {
		wp_send_json_success( array( 'message' => __( 'Check your email for a verification link.', 'peptide-starter' ) ) );
	}

	$config = peptide_starter_security_config();

	if ( ! $config['registration_enabled'] || ! get_option( 'users_can_register' ) ) {
		wp_send_json_error( array( 'message' => __( 'Registration is currently disabled.', 'peptide-starter' ) ) );
	}

	// Rate-limit registration by IP only (identifier empty → IP-only bucket).
	if ( ! Peptide_Starter_Rate_Limiter::check( 'register', 'ip' ) ) {
		wp_send_json_error( array( 'message' => peptide_starter_register_failure_message() ) );
	}
	Peptide_Starter_Rate_Limiter::record( 'register', 'ip' );

	$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password must not be modified.

	$pattern = sprintf( '/^[a-zA-Z0-9]{%d,%d}$/', (int) $config['username_min'], (int) $config['username_max'] );

	// Evaluate every check unconditionally so response time doesn't reveal
	// which branch failed. `username_exists` and `email_exists` are each a
	// DB round-trip; skipping them when an earlier cheap check already
	// failed produces a response-time oracle that enables enumeration. We
	// intentionally accept the extra queries as the cost of not leaking.
	$invalid_username = ! preg_match( $pattern, $username );
	$invalid_email    = ! is_email( $email );
	$invalid_password = strlen( $password ) < (int) $config['password_min'];
	$username_taken   = (bool) username_exists( $username );
	$email_taken      = (bool) email_exists( $email );

	if ( $invalid_username || $invalid_email || $invalid_password || $username_taken || $email_taken ) {
		wp_send_json_error( array( 'message' => peptide_starter_register_failure_message() ) );
	}

	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => peptide_starter_register_failure_message() ) );
	}

	// Send verification email. Intentionally ignore the wp_mail boolean —
	// we never tell the client whether delivery succeeded (attacker signal).
	peptide_starter_send_verification_email( $user_id );

	/**
	 * Fires after a new user registers via the frontend auth form.
	 *
	 * Listeners may attach additional profile-setup logic. Fired BEFORE
	 * email verification is complete — do not grant privileged access from
	 * this hook without checking peptide_starter_user_is_verified().
	 *
	 * @param int $user_id The newly created user ID.
	 */
	do_action( 'peptide_starter_user_registered', $user_id );

	wp_send_json_success(
		array(
			'message' => __( 'Account created. Check your inbox for a verification link — it expires in 24 hours.', 'peptide-starter' ),
		)
	);
}
add_action( 'wp_ajax_nopriv_ps_auth_register', 'peptide_starter_ajax_register' );

/**
 * Detect a tripped honeypot field.
 *
 * Hidden fields should never receive input from a human using the real
 * form. Any non-empty value → bot, silently drop the request.
 *
 * Side effects: on trip, writes a single low-signal log line with only
 * a hash of the client IP — zero PII.
 *
 * @param string $field_name POST key for the honeypot input.
 * @return bool True if the trap was triggered.
 */
function peptide_starter_honeypot_triggered( $field_name ) {
	if ( empty( $_POST[ $field_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- caller already verifies nonce.
		return false;
	}
	$hash = substr( hash( 'sha256', peptide_starter_get_client_ip() ), 0, 12 );
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( sprintf( 'peptide_starter_honeypot field=%s ip_hash=%s', $field_name, $hash ) );
	return true;
}
