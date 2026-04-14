<?php
/**
 * Transient-backed Rate Limiter
 *
 * Generic check/record/reset limiter used by every public endpoint
 * (login, register, contact, newsletter, verify-resend). State lives
 * in WordPress transients — auto-expires, no cleanup required.
 *
 * Storage key format: ps_rl_{action}_{hash} where
 *   hash = substr( wp_hash( $ip . '|' . $identifier ), 0, 16 ).
 * Raw IPs are never stored.
 *
 * @see inc/config.php — supplies per-action limit + window.
 * @see inc/auth-handlers.php, inc/contact-handler.php — primary callers.
 * @see functions.php — includes this file.
 *
 * What: Rate limit check/record/reset for any action+identifier pair.
 * Who calls it: Every public handler after nonce verification.
 * Dependencies: WordPress transients, inc/config.php, peptide_starter_get_client_ip().
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static rate limiter using WordPress transients.
 *
 * What: Tracks attempt counts per (action, identifier) tuple.
 * Who calls it: Public handlers after nonce verification.
 * Dependencies: Transient storage, peptide_starter_security_config().
 */
class Peptide_Starter_Rate_Limiter {

	/**
	 * Build the transient key for a given action + identifier.
	 *
	 * Uses a truncated wp_hash so the stored key never exposes the raw IP
	 * or raw identifier. 16 hex chars ≈ 64 bits of collision resistance —
	 * sufficient for the cardinality of a WP site.
	 *
	 * @param string $action     Action slug (e.g. 'login').
	 * @param string $identifier Extra identifier (email hash, form slug, etc.).
	 * @return string Transient key, always prefixed ps_rl_.
	 */
	protected static function build_key( $action, $identifier ) {
		$ip   = peptide_starter_get_client_ip();
		$hash = substr( wp_hash( $ip . '|' . $identifier ), 0, 16 );
		return 'ps_rl_' . preg_replace( '/[^a-z0-9_]/i', '', $action ) . '_' . $hash;
	}

	/**
	 * Check whether the caller is still within their budget.
	 *
	 * Does not mutate state — call record() after a failed attempt (or at the
	 * start of every attempt if you want to meter successes too).
	 *
	 * @param string $action     Action slug configured in security config.
	 * @param string $identifier Caller-supplied identifier (e.g. sha256(email)).
	 * @return bool True if allowed, false if over budget.
	 */
	public static function check( $action, $identifier ) {
		$limits  = peptide_starter_config_rate_limit( $action );
		$key     = self::build_key( $action, $identifier );
		$current = (int) get_transient( $key );
		return $current < $limits['limit'];
	}

	/**
	 * Record an attempt — increments the counter, sets TTL on first write.
	 *
	 * Side effects: writes a transient.
	 *
	 * @param string $action     Action slug.
	 * @param string $identifier Caller-supplied identifier.
	 * @return void
	 */
	public static function record( $action, $identifier ) {
		$limits  = peptide_starter_config_rate_limit( $action );
		$key     = self::build_key( $action, $identifier );
		$current = (int) get_transient( $key );
		set_transient( $key, $current + 1, $limits['window'] );
	}

	/**
	 * Clear the counter for an action + identifier.
	 *
	 * Call on successful login so a legit user isn't locked out by their
	 * own earlier typos.
	 *
	 * Side effects: deletes a transient.
	 *
	 * @param string $action     Action slug.
	 * @param string $identifier Caller-supplied identifier.
	 * @return void
	 */
	public static function reset( $action, $identifier ) {
		$key = self::build_key( $action, $identifier );
		delete_transient( $key );
	}
}

/**
 * Best-effort client IP resolver.
 *
 * Order of trust (first match wins):
 *   1. HTTP_CF_CONNECTING_IP — set by Cloudflare edge (peptiderepo.com uses CF).
 *   2. HTTP_X_FORWARDED_FOR  — first entry; only trusted on hosts that set it.
 *   3. REMOTE_ADDR           — direct peer.
 *
 * Note: the hosting environment (Hostinger + Cloudflare) reliably populates
 * HTTP_CF_CONNECTING_IP. If deployment moves off CF, the filter below can be
 * used to override.
 *
 * @return string IPv4/IPv6 string or '0.0.0.0' when nothing parseable.
 */
function peptide_starter_get_client_ip() {
	$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	$ip         = '';

	foreach ( $candidates as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}
		$raw   = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
		$first = trim( explode( ',', $raw )[0] );
		if ( filter_var( $first, FILTER_VALIDATE_IP ) ) {
			$ip = $first;
			break;
		}
	}

	if ( '' === $ip ) {
		$ip = '0.0.0.0';
	}

	/**
	 * Filter the resolved client IP — for custom infra overrides.
	 *
	 * @param string $ip Resolved IP.
	 */
	return apply_filters( 'peptide_starter_client_ip', $ip );
}

/**
 * Hash an identifier for rate-limit keying without storing plaintext.
 *
 * Use this for any identifier you don't want to hash in-line at every call
 * site (typically the lowercased email address on login).
 *
 * @param string $value Raw identifier.
 * @return string 32-char hex substring of a sha256.
 */
function peptide_starter_hash_identifier( $value ) {
	return substr( hash( 'sha256', (string) $value ), 0, 32 );
}
