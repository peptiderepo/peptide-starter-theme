<?php
/**
 * Tests for inc/contact-handler.php.
 *
 * @package peptide-starter
 */

class Test_Contact_Handler extends WP_UnitTestCase {

	protected $captured = null;

	public function set_up() {
		parent::set_up();
		$this->captured = null;
		add_filter(
			'wp_die_ajax_handler',
			function () {
				return array( $this, 'throwing_die' );
			}
		);
		add_filter(
			'wp_die_handler',
			function () {
				return array( $this, 'throwing_die' );
			}
		);
		add_filter(
			'wp_json_encode_data',
			function ( $data ) {
				$this->captured = $data;
				return $data;
			}
		);
		add_filter(
			'pre_wp_mail',
			function () {
				return true;
			}
		);
	}

	public function tear_down() {
		remove_all_filters( 'wp_die_ajax_handler' );
		remove_all_filters( 'wp_die_handler' );
		remove_all_filters( 'wp_json_encode_data' );
		remove_all_filters( 'pre_wp_mail' );
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ps_rl_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ps_rl_%'" );
		$_POST = array();
		parent::tear_down();
	}

	public function throwing_die( $message = '' ) {
		throw new WPAjaxDieStopException( is_scalar( $message ) ? (string) $message : wp_json_encode( $message ) );
	}

	protected function post( $overrides = array() ) {
		$defaults = array(
			'ps_contact_nonce' => wp_create_nonce( 'ps_contact_form' ),
			'name'             => 'Dr Valid',
			'email'            => 'valid@example.com',
			'topic'            => 'bug',
			'message'          => 'There is a bug in the tracker.',
		);
		$_POST = array_merge( $defaults, $overrides );
		try { peptide_starter_ajax_contact(); } catch ( WPAjaxDieStopException $e ) { /* ok */ }
	}

	public function test_valid_submission_succeeds() {
		$this->post();
		$this->assertTrue( $this->captured['success'] );
	}

	public function test_nonce_failure_rejects() {
		$this->post( array( 'ps_contact_nonce' => 'bogus' ) );
		$this->assertFalse( $this->captured['success'] );
		$this->assertStringContainsString( 'Security', $this->captured['data']['message'] );
	}

	public function test_name_with_newline_rejected_for_header_injection() {
		$this->post( array( 'name' => "Mallory\r\nBcc: attacker@x.com" ) );
		$this->assertFalse( $this->captured['success'] );
	}

	public function test_name_with_angle_brackets_rejected() {
		$this->post( array( 'name' => 'Alice <injected@x.com>' ) );
		$this->assertFalse( $this->captured['success'] );
	}

	public function test_invalid_topic_rejected() {
		$this->post( array( 'topic' => 'not-an-allowlisted-topic' ) );
		$this->assertFalse( $this->captured['success'] );
	}

	public function test_honeypot_returns_fake_success() {
		$this->post( array( 'ps_hp_contact' => 'filled-by-bot' ) );
		$this->assertTrue( $this->captured['success'] );
	}

	public function test_rate_limit_exceeded_rejects() {
		$config = peptide_starter_config_rate_limit( 'contact' );
		for ( $i = 0; $i < $config['limit']; $i++ ) {
			Peptide_Starter_Rate_Limiter::record( 'contact', 'ip' );
		}
		$this->post();
		$this->assertFalse( $this->captured['success'] );
		$this->assertStringContainsString( 'Too many', $this->captured['data']['message'] );
	}
}
