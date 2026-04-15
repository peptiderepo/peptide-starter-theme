<?php
/**
 * Cloudflare Edge IP Allowlist
 *
 * Static snapshot of Cloudflare's published IPv4 and IPv6 ranges used by
 * the client-IP resolver to decide whether HTTP_CF_CONNECTING_IP can be
 * trusted. Embedded — not fetched — so a compromised external source
 * cannot grant attackers the right to spoof this header.
 *
 * Snapshot date: 2026-04-14
 * Sources:
 *   - https://www.cloudflare.com/ips-v4
 *   - https://www.cloudflare.com/ips-v6
 *
 * Refresh cadence: review quarterly. Cloudflare historically adds ranges
 * a few times a year and rarely removes them; a stale-but-superset list
 * still protects us because an attacker cannot inject themselves into
 * the list, and legitimate CF traffic from newer ranges simply falls
 * back to REMOTE_ADDR (no loss of functionality, only loss of header
 * trust on new-range origins — acceptable until next refresh).
 *
 * @see inc/rate-limiter.php — peptide_starter_get_client_ip() consumes
 * @see functions.php — includes this file
 *
 * What: Provides peptide_starter_cloudflare_ip_ranges() (filterable) and
 *       peptide_starter_is_cloudflare_peer( $ip ) CIDR matcher.
 * Who calls it: Only the IP resolver, on every public request.
 * Dependencies: inet_pton, PHP bitwise.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the Cloudflare edge IPv4/IPv6 CIDR list.
 *
 * Override with add_filter( 'peptide_starter_cloudflare_ip_ranges', ... )
 * if the list needs an out-of-band bump between releases.
 *
 * @return array { 'v4' => string[], 'v6' => string[] } of CIDR blocks.
 */
function peptide_starter_cloudflare_ip_ranges() {
	$ranges = array(
		'v4' => array(
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/13',
			'104.24.0.0/14',
			'172.64.0.0/13',
			'131.0.72.0/22',
		),
		'v6' => array(
			'2400:cb00::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2405:b500::/32',
			'2405:8100::/32',
			'2a06:98c0::/29',
			'2c0f:f248::/32',
		),
	);

	/**
	 * Filter the Cloudflare edge IP ranges used by the client-IP resolver.
	 *
	 * Operators can override this without a code deploy. Shape must be an
	 * array with 'v4' and 'v6' keys, each a list of CIDR strings.
	 *
	 * @param array $ranges Default snapshot.
	 */
	return apply_filters( 'peptide_starter_cloudflare_ip_ranges', $ranges );
}

/**
 * Check whether an IP address is within a Cloudflare edge range.
 *
 * Handles both IPv4 and IPv6. Returns false on unparseable input.
 *
 * @param string $ip Candidate IP (e.g. REMOTE_ADDR).
 * @return bool True if $ip falls inside any CF CIDR in the current snapshot.
 */
function peptide_starter_is_cloudflare_peer( $ip ) {
	if ( ! is_string( $ip ) || '' === $ip ) {
		return false;
	}
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return false;
	}

	$ranges = peptide_starter_cloudflare_ip_ranges();
	$is_v6  = ( false !== strpos( $ip, ':' ) );
	$list   = $is_v6 ? ( isset( $ranges['v6'] ) ? $ranges['v6'] : array() ) : ( isset( $ranges['v4'] ) ? $ranges['v4'] : array() );

	foreach ( $list as $cidr ) {
		if ( peptide_starter_cidr_match( $ip, $cidr ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Test whether $ip is inside $cidr. Supports v4 and v6.
 *
 * Implementation note: we use inet_pton for both families so the same
 * byte-compare loop handles v4 (4 bytes) and v6 (16 bytes). Mask bits
 * beyond the prefix length are zeroed by ignoring them in the compare.
 *
 * @param string $ip   IP to test.
 * @param string $cidr Block in CIDR notation.
 * @return bool
 */
function peptide_starter_cidr_match( $ip, $cidr ) {
	if ( ! is_string( $cidr ) || false === strpos( $cidr, '/' ) ) {
		return false;
	}
	list( $subnet, $bits ) = explode( '/', $cidr, 2 );
	$bits                  = (int) $bits;

	$ip_bin     = @inet_pton( $ip );
	$subnet_bin = @inet_pton( $subnet );
	if ( false === $ip_bin || false === $subnet_bin ) {
		return false;
	}
	if ( strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
		return false; // Family mismatch.
	}
	if ( $bits < 0 || $bits > ( strlen( $ip_bin ) * 8 ) ) {
		return false;
	}

	$full_bytes = intdiv( $bits, 8 );
	$remaining  = $bits % 8;

	if ( $full_bytes > 0 && 0 !== substr_compare( $ip_bin, $subnet_bin, 0, $full_bytes ) ) {
		return false;
	}
	if ( 0 === $remaining ) {
		return true;
	}

	$mask        = chr( ( 0xFF << ( 8 - $remaining ) ) & 0xFF );
	$ip_byte     = substr( $ip_bin, $full_bytes, 1 );
	$subnet_byte = substr( $subnet_bin, $full_bytes, 1 );
	return ( $ip_byte & $mask ) === ( $subnet_byte & $mask );
}
