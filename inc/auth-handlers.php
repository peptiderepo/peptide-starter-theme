<?php
/**
 * Authentication AJAX Handlers
 *
 * Handles login and registration via AJAX from the branded auth page.
 * Both handlers validate CSRF nonces and return JSON responses.
 *
 * @see page-auth.php — renders the auth forms
 * @see assets/js/auth.js — submits forms via XMLHttpRequest
 * @see functions.php — includes this file
 *
 * What: AJAX endpoints for sign-in and registration.
 * Who calls it: WordPress admin-ajax.php via auth.js.
 * Dependencies: WordPress core auth functions (wp_signon, wp_create_user).
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX login.
 *
 * Verifies nonce, authenticates via wp_signon(), returns JSON with redirect URL.
 * Rate limiting: WordPress core handles lockout via wp_login_failed hooks.
 *
 * @return void Sends JSON response and exits.
 */
function peptide_starter_ajax_login() {
	// Verify CSRF nonce.
	if ( ! isset( $_POST['ps_login_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_login_nonce'] ) ), 'ps_auth_login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password must not be modified.

	if ( empty( $email ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Email and password are required.', 'peptide-starter' ) ) );
	}

	// WordPress authenticates by username or email. Resolve email to username.
	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'Invalid email or password.', 'peptide-starter' ) ) );
	}

	$creds = array(
		'user_login'    => $user->user_login,
		'user_password' => $password,
		'remember'      => true,
	);

	$signed_in = wp_signon( $creds, is_ssl() );

	if ( is_wp_error( $signed_in ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid email or password.', 'peptide-starter' ) ) );
	}

	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );
	// Prevent open redirect attacks — only allow internal URLs.
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	wp_send_json_success( array( 'redirect' => $redirect ) );
}
add_action( 'wp_ajax_nopriv_ps_auth_login', 'peptide_starter_ajax_login' );

/**
 * Handle AJAX registration.
 *
 * Validates username (5-15 alphanumeric), email, password (min 8 chars).
 * Creates user via wp_create_user() and auto-logs them in.
 *
 * @return void Sends JSON response and exits.
 */
function peptide_starter_ajax_register() {
	// Verify CSRF nonce.
	if ( ! isset( $_POST['ps_register_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_register_nonce'] ) ), 'ps_auth_register' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	// Check if registration is enabled in WordPress settings.
	if ( ! get_option( 'users_can_register' ) ) {
		wp_send_json_error( array( 'message' => __( 'Registration is currently disabled.', 'peptide-starter' ) ) );
	}

	$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	// Validate username: 5-15 alphanumeric characters.
	if ( ! preg_match( '/^[a-zA-Z0-9]{5,15}$/', $username ) ) {
		wp_send_json_error( array( 'message' => __( 'Username must be 5-15 alphanumeric characters.', 'peptide-starter' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'peptide-starter' ) ) );
	}

	if ( strlen( $password ) < 8 ) {
		wp_send_json_error( array( 'message' => __( 'Password must be at least 8 characters.', 'peptide-starter' ) ) );
	}

	if ( username_exists( $username ) ) {
		wp_send_json_error( array( 'message' => __( 'This username is already taken.', 'peptide-starter' ) ) );
	}

	if ( email_exists( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'An account with this email already exists.', 'peptide-starter' ) ) );
	}

	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
	}

	// Auto-login after registration.
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true, is_ssl() );

	/**
	 * Fires after a new user registers via the frontend auth form.
	 *
	 * @param int $user_id The newly created user ID.
	 */
	do_action( 'peptide_starter_user_registered', $user_id );

	wp_send_json_success( array( 'redirect' => home_url( '/profile' ) ) );
}
add_action( 'wp_ajax_nopriv_ps_auth_register', 'peptide_starter_ajax_register' );
