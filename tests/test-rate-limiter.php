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

	public function test_client_ip_prefers_cf_header() {
		$_SERVER['HTTP_CF_CONNECTING_IP'] = '203.0.113.5';
		$_SERVER['REMOTE_ADDR']           = '10.0.0.1';
		$this->assertSame( '203.0.113.5', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
	}

	public function test_client_ip_rejects_garbage() {
		$_SERVER['HTTP_CF_CONNECTING_IP'] = 'not-an-ip';
		$_SERVER['REMOTE_ADDR']           = '10.0.0.1';
		$this->assertSame( '10.0.0.1', peptide_starter_get_client_ip() );
		unset( $_SERVER['HTTP_CF_CONNECTING_IP'] );
	}
}
