<?php
/**
 * Tests for inc/rate-limiter.php and inc/config.php.
 *
 * @package peptide-starter
 */

/**
 * Rate limiter check / record / reset lifecycle and filterability.
 */
class Test_Rate_Limiter extends WP_UnitTestCase {

	/**
	 * Reset all transients we touched between tests.
	 */
	public function tear_down() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ps_rl_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ps_rl_%'" );
		remove_all_filters( 'peptide_starter_security_config' );
		parent::tear_down();
	}

	public function test_check_allows_under_limit() {
		$this->assertTrue( Peptide_Starter_Rate_Limiter::check( 'login', 'user-a' ) );
	}

	public function test_record_increments_and_eventually_blocks() {
		$config = peptide_starter_config_rate_limit( 'login' );
		for ( $i = 0; $i < $config['limit']; $i++ ) {
			Peptide_Starter_Rate_Limiter::record( 'login', 'user-b' );
		}
		$this->assertFalse(
			Peptide_Starter_Rate_Limiter::check( 'login', 'user-b' ),
			'check() must return false after recording limit-many attempts.'
		);
	}

	public function test_reset_clears_counter() {
		$config = peptide_starter_config_rate_limit( 'login' );
		for ( $i = 0; $i < $config['limit']; $i++ ) {
			Peptide_Starter_Rate_Limiter::record( 'login', 'user-c' );
		}
		Peptide_Starter_Rate_Limiter::reset( 'login', 'user-c' );
		$this->assertTrue( Peptide_Starter_Rate_Limiter::check( 'login', 'user-c' ) );
	}

	public function test_identifiers_are_isolated() {
		$config = peptide_starter_config_rate_limit( 'login' );
		for ( $i = 0; $i < $config['limit']; $i++ ) {
			Peptide_Starter_Rate_Limiter::record( 'login', 'user-d' );
		}
		$this->assertTrue(
			Peptide_Starter_Rate_Limiter::check( 'login', 'user-e' ),
			'Recording against user-d must not affect user-e.'
		);
	}

	public function test_filter_override_changes_limit() {
		add_filter(
			'peptide_starter_security_config',
			function ( $c ) {
				$c['rate_limits']['login']['limit'] = 1;
				return $c;
			}
		);
		Peptide_Starter_Rate_Limiter::record( 'login', 'user-f' );
		$this->assertFalse( Peptide_Starter_Rate_Limiter::check( 'login', 'user-f' ) );
	}

	public function test_hash_identifier_is_stable_and_non_reversible() {
		$a = peptide_starter_hash_identifier( 'alice@example.com' );
		$b = peptide_starter_hash_identifier( 'alice@example.com' );
		$c = peptide_starter_hash_identifier( 'bob@example.com' );
		$this->assertSame( $a, $b );
		$this->assertNotSame( $a, $c );
		$this->assertSame( 32, strlen( $a ) );
		$this->assertStringNotContainsString( 'alice', $a );
	}

	public function test_client_ip_trusts_cf_header_when_peer_is_cloudflare() {
		// 172.68.0.5 is inside 172.64.0.0/13 (published CF range).
		$_SERVER['REMOTE_ADDR']           = '172.68.0.5';
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.5';
		$this->assertSame( '203.0.113.5', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['REMOTE_ADDR'] );
	}

	public function test_client_ip_ignores_spoofed_cf_header_from_non_cloudflare_peer() {
		// 8.8.8.8 is not Cloudflare — any CF-Connecting-IP header must be
		// treated as hostile and ignored. This is PSEC-007.
		$_SERVER['REMOTE_ADDR']           = '8.8.8.8';
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.5';
		$this->assertSame( '8.8.8.8', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['REMOTE_ADDR'] );
	}

	public function test_client_ip_rejects_invalid_cf_header_value() {
		$_SERVER['REMOTE_ADDR']           = '172.68.0.5';
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'not-an-ip';
		$this->assertSame( '172.68.0.5', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['REMOTE_ADDR'] );
	}

	public function test_client_ip_ignores_xff_by_default() {
		$_SERVER['REMOTE_ADDR']           = '10.0.0.1';
		$_SERVER['HTTP_X_FORWARDED_FOR']  = '203.0.113.5';
		$this->assertSame( '10.0.0.1', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR'] );
	}

	public function test_client_ip_respects_xff_when_filter_enables_trust() {
		$_SERVER['REMOTE_ADDR']           = '10.0.0.1';
		$_SERVER['HTTP_X_FORWARDED_FOR']  = '203.0.113.5, 172.68.0.5';
		add_filter( 'peptide_starter_trust_xff', '__return_true' );
		$this->assertSame( '203.0.113.5', peptide_starter_get_client_ip() );
		remove_filter( 'peptide_starter_trust_xff', '__return_true' );
		unset( $_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR'] );
	}

	public function test_cloudflare_ranges_filter_override_is_honoured() {
		add_filter(
			'peptide_starter_cloudflare_ip_ranges',
			function () {
				return array( 'v4' => array( '10.0.0.0/8' ), 'v6' => array() );
			}
		);
		// Default snapshot would treat 172.68.0.5 as CF; filter override
		// should now treat 10.0.0.5 as CF instead.
		$this->assertTrue( peptide_starter_is_cloudflare_peer( '10.0.0.5' ) );
		$this->assertFalse( peptide_starter_is_cloudflare_peer( '172.68.0.5' ) );
		remove_all_filters( 'peptide_starter_cloudflare_ip_ranges' );
	}

	public function test_cidr_match_handles_ipv6() {
		$this->assertTrue( peptide_starter_cidr_match( '2606:4700::1234', '2606:4700::/32' ) );
		$this->assertFalse( peptide_starter_cidr_match( '2001:db8::1', '2606:4700::/32' ) );
	}

	public function test_cidr_match_rejects_family_mismatch() {
		$this->assertFalse( peptide_starter_cidr_match( '203.0.113.5', '2606:4700::/32' ) );
	}
}
