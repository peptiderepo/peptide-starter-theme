<?php
/**
 * Tests for inc/auth-handlers.php.
 *
 * Covers: unified error messages, rate limiting, nonce failure,
 * honeypot drop, enumeration resistance.
 *
 * @package peptide-starter
 */

/**
 * @runTestsInSeparateProcesses disabled — WP_UnitTestCase handles isolation.
 */
class Test_Auth_Handlers extends WP_UnitTestCase {

	/**
	 * Capture wp_send_json_{success,error} via wp_die_handler so tests can
	 * assert on the response without ending the PHP process.
	 *
	 * @var array|null
	 */
	protected $captured = null;

	public function set_up() {
		parent::set_up();
		$this->captured = null;

		// Swap wp_die into a throwable we can catch.
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_die_handler' ) );
		add_filter( 'wp_die_handler', array( $this, 'get_die_handler' ) );

		// Capture the JSON body via the wp_json_encode filter path.
		add_filter(
			'wp_json_encode_data',
			function ( $data ) {
				$this->captured = $data;
				return $data;
			}
		);
	}

	public function tear_down() {
		remove_all_filters( 'wp_die_ajax_handler' );
		remove_all_filters( 'wp_die_handler' );
		remove_all_filters( 'wp_json_encode_data' );
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ps_rl_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ps_rl_%'" );
		$_POST = array();
		parent::tear_down();
	}

	public function get_die_handler() {
		return array( $this, 'throwing_die' );
	}

	public function throwing_die( $message = '', $title = '', $args = array() ) {
		throw new WPAjaxDieStopException( is_scalar( $message ) ? (string) $message : wp_json_encode( $message ) );
	}

	/**
	 * Run the login handler and return the captured response, if any.
	 */
	protected function invoke_login() {
		try {
			peptide_starter_ajax_login();
		} catch ( WPAjaxDieStopException $e ) {
			// Expected — JSON was sent.
			return;
		}
	}

	public function test_login_missing_nonce_rejects_with_security_message() {
		$_POST = array( 'email' => 'a@b.com', 'password' => 'whatever' );
		$this->invoke_login();
		$this->assertIsArray( $this->captured );
		$this->assertFalse( $this->captured['success'] );
		$this->assertStringContainsString( 'Security check', $this->captured['data']['message'] );
	}

	public function test_login_unknown_email_returns_unified_message() {
		$_POST = array(
			'ps_login_nonce' => wp_create_nonce( 'ps_auth_login' ),
			'email'          => 'nobody-' . wp_generate_password( 8, false ) . '@example.com',
			'password'       => 'wrong-password',
		);
		$this->invoke_login();
		$this->assertSame( peptide_starter_login_failure_message(), $this->captured['data']['message'] );
	}

	public function test_login_wrong_password_returns_unified_message() {
		$user = self::factory()->user->create_and_get( array( 'user_pass' => 'correct-horse-battery' ) );
		$_POST = array(
			'ps_login_nonce' => wp_create_nonce( 'ps_auth_login' ),
			'email'          => $user->user_email,
			'password'       => 'definitely-wrong',
		);
		$this->invoke_login();
		$this->assertSame( peptide_starter_login_failure_message(), $this->captured['data']['message'] );
	}

	public function test_login_honeypot_tripped_returns_fake_success() {
		$_POST = array(
			'ps_login_nonce' => wp_create_nonce( 'ps_auth_login' ),
			'email'          => 'a@b.com',
			'password'       => 'x',
			'ps_hp_login'    => 'botfill',
		);
		$this->invoke_login();
		$this->assertTrue( $this->captured['success'], 'Honeypot must silently fake success.' );
	}

	public function test_login_rate_limit_exceeded_still_returns_unified_message() {
		// Exhaust the budget.
		$config = peptide_starter_config_rate_limit( 'login' );
		for ( $i = 0; $i < $config['limit']; $i++ ) {
			Peptide_Starter_Rate_Limiter::record( 'login', peptide_starter_hash_identifier( 'same@same.com' ) );
		}
		$_POST = array(
			'ps_login_nonce' => wp_create_nonce( 'ps_auth_login' ),
			'email'          => 'same@same.com',
			'password'       => 'anything',
		);
		$this->invoke_login();
		$this->assertSame( peptide_starter_login_failure_message(), $this->captured['data']['message'] );
	}

	public function test_register_invalid_username_returns_unified_message() {
		$_POST = array(
			'ps_register_nonce' => wp_create_nonce( 'ps_auth_register' ),
			'username'          => 'ab',
			'email'             => 'newuser@example.com',
			'password'          => 'longenoughpassword',
		);
		update_option( 'users_can_register', 1 );
		try { peptide_starter_ajax_register(); } catch ( WPAjaxDieStopException $e ) { /* ok */ }
		$this->assertSame( peptide_starter_register_failure_message(), $this->captured['data']['message'] );
	}

	/**
	 * PSEC-009: registration validation path is not short-circuit.
	 *
	 * We can't directly mock `preg_match` / `username_exists` etc. from
	 * userland. Instead, we assert the behavioural contract: even when an
	 * early check has already failed (invalid username pattern), the
	 * handler still runs the later DB-backed checks. We observe this by
	 * filtering `pre_user_login` which wp_create_user-adjacent code paths
	 * consult, and by checking that `username_exists` was called via a
	 * filter it would fire. A simpler surrogate is to hook into WordPress'
	 * `user_search_query` / `query` action and confirm at least one user
	 * lookup happened during a failing registration — which would not
	 * happen if the handler short-circuited on the pattern failure.
	 */
	public function test_register_runs_all_checks_even_when_early_one_fails() {
		$queries_ran = 0;
		$counter     = function ( $query ) use ( &$queries_ran ) {
			// wp_cache_get avoids counting ourselves. We only care whether
			// any user-table-touching query fires during the handler run.
			if ( is_string( $query ) && false !== stripos( $query, 'users' ) ) {
				$queries_ran++;
			}
			return $query;
		};
		add_filter( 'query', $counter );

		update_option( 'users_can_register', 1 );
		$_POST = array(
			'ps_register_nonce' => wp_create_nonce( 'ps_auth_register' ),
			'username'          => 'ab', // Too short — early check fails.
			'email'             => 'validformat@example.com',
			'password'          => 'longenoughpassword',
		);
		try { peptide_starter_ajax_register(); } catch ( WPAjaxDieStopException $e ) { /* ok */ }

		remove_filter( 'query', $counter );

		$this->assertGreaterThan(
			0,
			$queries_ran,
			'username_exists / email_exists must still run even when pattern check fails (PSEC-009 non-short-circuit).'
		);
		$this->assertSame( peptide_starter_register_failure_message(), $this->captured['data']['message'] );
	}

	public function test_register_duplicate_email_returns_unified_message() {
		self::factory()->user->create( array( 'user_email' => 'dup@example.com' ) );
		update_option( 'users_can_register', 1 );
		$_POST = array(
			'ps_register_nonce' => wp_create_nonce( 'ps_auth_register' ),
			'username'          => 'newusername',
			'email'             => 'dup@example.com',
			'password'          => 'longenoughpassword',
		);
		try { peptide_starter_ajax_register(); } catch ( WPAjaxDieStopException $e ) { /* ok */ }
		$this->assertSame( peptide_starter_register_failure_message(), $this->captured['data']['message'] );
	}
}
