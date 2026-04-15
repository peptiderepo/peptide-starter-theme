<?php
/**
 * Transient-backed Rate Limiter + Trustworthy Client IP
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
 * @see inc/cloudflare-ips.php — edge-range allowlist for CF-Connecting-IP trust.
 * @see inc/auth-handlers.php, inc/contact-handler.php — primary callers.
 * @see functions.php — includes this file.
 *
 * What: Rate limit check/record/reset + peer-validated client-IP resolver.
 * Who calls it: Every public handler after nonce verification.
 * Dependencies: WordPress transients, inc/config.php, inc/cloudflare-ips.php.
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
	 * or raw identifier. 16 hex chars ≈ 64 bits of collision resistance.
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
	 * Whether the caller is still within their budget.
	 *
	 * Does not mutate state — call record() on failure (or at the start of
	 * every attempt if you want to meter successes too).
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
 * Resolve the client IP we will trust for rate-limit keying.
 *
 * Trust model (v1.5.2):
 *   - REMOTE_ADDR is the ground truth — it is always returned as the
 *     fallback and cannot be forged by a client.
 *   - HTTP_CF_CONNECTING_IP is trusted ONLY when REMOTE_ADDR is a known
 *     Cloudflare edge (see inc/cloudflare-ips.php). If the origin is
 *     reached directly (bypassing CF), attackers could otherwise spoof
 *     any IP they liked and bypass every rate limiter.
 *   - HTTP_X_FORWARDED_FOR is ignored by default. Operators who run
 *     behind a different trusted proxy can opt-in via the
 *     peptide_starter_trust_xff filter returning true.
 *
 * Invalid / unparseable header values are silently ignored.
 *
 * @return string Validated IPv4/IPv6 string; '0.0.0.0' only if REMOTE_ADDR
 *                is empty or unparseable (shouldn't happen in practice).
 */
function peptide_starter_get_client_ip() {
	$remote_raw = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$remote     = filter_var( $remote_raw, FILTER_VALIDATE_IP ) ? $remote_raw : '0.0.0.0';

	// CF-Connecting-IP — only trusted when the peer itself is a CF edge.
	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] )
		&& function_exists( 'peptide_starter_is_cloudflare_peer' )
		&& peptide_starter_is_cloudflare_peer( $remote ) ) {
		$cf = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
			/**
			 * Filter the resolved client IP.
			 *
			 * @param string $ip Resolved IP.
			 */
			return apply_filters( 'peptide_starter_client_ip', $cf );
		}
	}

	/**
	 * Whether to trust HTTP_X_FORWARDED_FOR. Off by default — only enable
	 * when a known, trusted non-CF proxy sits in front of the origin.
	 *
	 * @param bool $trust Default false.
	 */
	$trust_xff = (bool) apply_filters( 'peptide_starter_trust_xff', false );
	if ( $trust_xff && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$xff   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$first = trim( explode( ',', $xff )[0] );
		if ( filter_var( $first, FILTER_VALIDATE_IP ) ) {
			return apply_filters( 'peptide_starter_client_ip', $first );
		}
	}

	return apply_filters( 'peptide_starter_client_ip', $remote );
}

/**
 * Hash an identifier for rate-limit keying without storing plaintext.
 *
 * @param string $value Raw identifier.
 * @return string 32-char hex substring of a sha256.
 */
function peptide_starter_hash_identifier( $value ) {
	return substr( hash( 'sha256', (string) $value ), 0, 32 );
}
