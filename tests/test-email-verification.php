<?php
/**
 * Tests for inc/email-verification.php.
 *
 * @package peptide-starter
 */

class Test_Email_Verification extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Capture wp_mail without sending.
		add_filter(
			'pre_wp_mail',
			function () {
				return true;
			}
		);
	}

	public function tear_down() {
		remove_all_filters( 'pre_wp_mail' );
		parent::tear_down();
	}

	public function test_send_sets_meta_and_returns_true() {
		$user_id = self::factory()->user->create();
		$sent    = peptide_starter_send_verification_email( $user_id );

		$this->assertTrue( $sent );
		$this->assertSame( '1', (string) get_user_meta( $user_id, 'ps_pending_verification', true ) );
		$this->assertNotEmpty( get_user_meta( $user_id, 'ps_verify_token', true ) );
		$this->assertGreaterThan( time(), (int) get_user_meta( $user_id, 'ps_verify_expires', true ) );
	}

	public function test_user_is_verified_default_without_meta() {
		$user_id = self::factory()->user->create();
		$this->assertTrue( peptide_starter_user_is_verified( $user_id ) );
	}

	public function test_user_is_unverified_with_pending_meta() {
		$user_id = self::factory()->user->create();
		update_user_meta( $user_id, 'ps_pending_verification', 1 );
		$this->assertFalse( peptide_starter_user_is_verified( $user_id ) );
	}

	public function test_verification_handler_rejects_bad_token() {
		$user_id = self::factory()->user->create();
		peptide_starter_send_verification_email( $user_id );

		$_GET['uid']   = $user_id;
		$_GET['token'] = 'obviously-wrong';
		set_query_var( 'ps_verify', 1 );

		$redirected = null;
		add_filter(
			'wp_redirect',
			function ( $url ) use ( &$redirected ) {
				$redirected = $url;
				return false;
			}
		);

		try {
			peptide_starter_handle_verify_request();
		} catch ( Exception $e ) { /* wp_safe_redirect + exit */ }

		$this->assertNotNull( $redirected );
		$this->assertStringContainsString( 'verify_error=1', (string) $redirected );
		remove_all_filters( 'wp_redirect' );
		set_query_var( 'ps_verify', 0 );
	}

	public function test_verification_handler_rejects_expired_token() {
		$user_id = self::factory()->user->create();
		$token   = wp_generate_password( 43, false, false );
		update_user_meta( $user_id, 'ps_pending_verification', 1 );
		update_user_meta( $user_id, 'ps_verify_token', $token );
		update_user_meta( $user_id, 'ps_verify_expires', time() - 10 );

		$_GET['uid']   = $user_id;
		$_GET['token'] = $token;
		set_query_var( 'ps_verify', 1 );

		$redirected = null;
		add_filter(
			'wp_redirect',
			function ( $url ) use ( &$redirected ) {
				$redirected = $url;
				return false;
			}
		);

		try {
			peptide_starter_handle_verify_request();
		} catch ( Exception $e ) { /* ok */ }

		$this->assertNotNull( $redirected );
		$this->assertStringContainsString( 'verify_error=1', (string) $redirected );
		remove_all_filters( 'wp_redirect' );
		set_query_var( 'ps_verify', 0 );
	}
}
