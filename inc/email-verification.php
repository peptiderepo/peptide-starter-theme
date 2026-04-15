<?php
/**
 * Email Verification Flow
 *
 * Generates signed tokens, sends verification email, handles the /verify
 * click-through, and exposes a resend endpoint + gate helper for downstream
 * handlers that should refuse writes from unverified accounts.
 *
 * @see inc/auth-handlers.php — calls send_verification_email after register
 * @see inc/config.php — supplies verify_token_ttl and verify_resend limit
 * @see inc/contact-handler.php — gates on peptide_starter_user_is_verified()
 * @see page-profile.php — renders the "verify your email" notice
 * @see functions.php — includes this file
 *
 * What: Token-gated email verification for new registrations.
 * Who calls it: Auth handlers, /verify rewrite, AJAX resend endpoint.
 * Dependencies: wp_mail, user meta (ps_verify_token, ps_verify_expires,
 *   ps_pending_verification), Peptide_Starter_Rate_Limiter.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a verification token, persist it, and email the user.
 *
 * Side effects:
 *   - Sets user meta ps_pending_verification, ps_verify_token, ps_verify_expires.
 *   - Calls wp_mail (network I/O).
 *   - Logs wp_mail return value via error_log at debug level for triage.
 *
 * Event: fires 'peptide_starter_verification_sent' with ( $user_id, $sent_ok ).
 *   No listeners registered in-theme; plugins may attach for auditing.
 *
 * @param int $user_id Target user.
 * @return bool wp_mail() return value; true on dispatch, false on failure.
 */
function peptide_starter_send_verification_email( $user_id ) {
	$user = get_user_by( 'id', (int) $user_id );
	if ( ! $user ) {
		return false;
	}

	$token   = wp_generate_password( 43, false, false );
	$ttl     = (int) peptide_starter_config_get( 'verify_token_ttl', DAY_IN_SECONDS );
	$expires = time() + $ttl;

	update_user_meta( $user_id, 'ps_pending_verification', 1 );
	update_user_meta( $user_id, 'ps_verify_token', $token );
	update_user_meta( $user_id, 'ps_verify_expires', $expires );

	$verify_url = add_query_arg(
		array(
			'uid'   => $user_id,
			'token' => $token,
		),
		home_url( '/verify' )
	);

	$subject = sprintf(
		/* translators: %s: site name */
		__( '[%s] Verify your email address', 'peptide-starter' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
	);

	$body  = sprintf(
		/* translators: %s: user display name */
		__( 'Hi %s,', 'peptide-starter' ),
		$user->display_name
	) . "\n\n";
	$body .= __( 'Thanks for registering with Peptide Repo. To activate your account, click the link below within 24 hours:', 'peptide-starter' ) . "\n\n";
	$body .= $verify_url . "\n\n";
	$body .= __( "If you didn't create this account, you can safely ignore this email — it will expire on its own.", 'peptide-starter' ) . "\n\n";
	$body .= __( '— Peptide Repo', 'peptide-starter' );

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	$sent = wp_mail( $user->user_email, $subject, $body, $headers );

	// Log for deliverability triage; never log the token or body.
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( sprintf( 'peptide_starter_verify_mail user=%d sent=%s', $user_id, $sent ? '1' : '0' ) );

	/**
	 * Fires after the verification email dispatch attempt.
	 *
	 * @param int  $user_id Recipient user ID.
	 * @param bool $sent    Whether wp_mail returned true.
	 */
	do_action( 'peptide_starter_verification_sent', $user_id, (bool) $sent );

	return (bool) $sent;
}

/**
 * Whether a user has completed email verification.
 *
 * Users without the ps_pending_verification meta are treated as verified.
 * This includes all accounts created before v1.5.2 — the v1.5.1 re-verify
 * migration was removed in v1.5.2 (see CHANGELOG § [1.5.2] PSEC-008);
 * existing accounts are grandfathered. Verification is enforced on new
 * registrations from v1.5.2 forward.
 *
 * @param int $user_id User ID.
 * @return bool True if verified or exempt; false if pending.
 */
function peptide_starter_user_is_verified( $user_id ) {
	$pending = get_user_meta( (int) $user_id, 'ps_pending_verification', true );
	return '1' !== (string) $pending;
}

/**
 * Add the /verify rewrite rule and the query var it emits.
 *
 * @return void
 */
function peptide_starter_register_verify_rewrite() {
	add_rewrite_rule( '^verify/?$', 'index.php?ps_verify=1', 'top' );
}
add_action( 'init', 'peptide_starter_register_verify_rewrite' );

/**
 * Register the ps_verify query var.
 *
 * @param array $vars Existing query vars.
 * @return array
 */
function peptide_starter_register_verify_query_var( $vars ) {
	$vars[] = 'ps_verify';
	return $vars;
}
add_filter( 'query_vars', 'peptide_starter_register_verify_query_var' );

/**
 * Handle a /verify click-through.
 *
 * Timing-safe token comparison. On success: clears verify meta, logs the
 * user in, redirects to /profile?verified=1. On failure: redirects to
 * /auth?verify_error=1.
 *
 * Side effects: deletes user meta, sets auth cookie, emits 302.
 *
 * @return void
 */
function peptide_starter_handle_verify_request() {
	if ( ! (int) get_query_var( 'ps_verify' ) ) {
		return;
	}

	$uid   = isset( $_GET['uid'] ) ? (int) $_GET['uid'] : 0;
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

	if ( $uid <= 0 || '' === $token ) {
		wp_safe_redirect( home_url( '/auth?verify_error=1' ) );
		exit;
	}

	$stored_token   = (string) get_user_meta( $uid, 'ps_verify_token', true );
	$stored_expires = (int) get_user_meta( $uid, 'ps_verify_expires', true );

	if ( '' === $stored_token || $stored_expires < time() || ! hash_equals( $stored_token, $token ) ) {
		wp_safe_redirect( home_url( '/auth?verify_error=1' ) );
		exit;
	}

	delete_user_meta( $uid, 'ps_pending_verification' );
	delete_user_meta( $uid, 'ps_verify_token' );
	delete_user_meta( $uid, 'ps_verify_expires' );

	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true, is_ssl() );

	/**
	 * Fires when a user successfully verifies their email.
	 *
	 * @param int $uid Verified user ID.
	 */
	do_action( 'peptide_starter_user_verified', $uid );

	wp_safe_redirect( home_url( '/profile?verified=1' ) );
	exit;
}
add_action( 'template_redirect', 'peptide_starter_handle_verify_request' );

/**
 * AJAX endpoint: resend a verification email.
 *
 * Rate-limited via the 'verify_resend' action. Always responds with a
 * generic success to avoid leaking whether the address is registered.
 *
 * Side effects: may call peptide_starter_send_verification_email().
 *
 * @return void Sends JSON and exits.
 */
function peptide_starter_ajax_resend_verify() {
	if ( ! isset( $_POST['ps_resend_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_resend_nonce'] ) ), 'ps_resend_verify' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', 'peptide-starter' ) ) );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_success( array( 'message' => __( 'If that address needs a verification link, one has been sent.', 'peptide-starter' ) ) );
	}

	$identifier = peptide_starter_hash_identifier( strtolower( $email ) );
	if ( ! Peptide_Starter_Rate_Limiter::check( 'verify_resend', $identifier ) ) {
		wp_send_json_success( array( 'message' => __( 'If that address needs a verification link, one has been sent.', 'peptide-starter' ) ) );
	}
	Peptide_Starter_Rate_Limiter::record( 'verify_resend', $identifier );

	$user = get_user_by( 'email', $email );
	if ( $user && ! peptide_starter_user_is_verified( $user->ID ) ) {
		peptide_starter_send_verification_email( $user->ID );
	}

	wp_send_json_success( array( 'message' => __( 'If that address needs a verification link, one has been sent.', 'peptide-starter' ) ) );
}
add_action( 'wp_ajax_nopriv_ps_resend_verify', 'peptide_starter_ajax_resend_verify' );
add_action( 'wp_ajax_ps_resend_verify', 'peptide_starter_ajax_resend_verify' );

/**
 * Flush rewrite rules when the /verify rule is freshly registered.
 *
 * Idempotent — guarded by an option flag so we only flush once per release.
 *
 * @return void
 */
function peptide_starter_maybe_flush_verify_rewrite() {
	$stamp = get_option( 'peptide_starter_verify_rewrite_version' );
	if ( PEPTIDE_STARTER_VERSION === $stamp ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'peptide_starter_verify_rewrite_version', PEPTIDE_STARTER_VERSION, false );
}
add_action( 'init', 'peptide_starter_maybe_flush_verify_rewrite', 20 );
