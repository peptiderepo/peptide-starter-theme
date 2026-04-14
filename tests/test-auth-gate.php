<?php
/**
 * Tests for peptide_starter_require_login() helper.
 *
 * @package peptide-starter
 */

class Test_Auth_Gate extends WP_UnitTestCase {

	/**
	 * @var string|null Captured redirect target.
	 */
	protected $redirect = null;

	public function set_up() {
		parent::set_up();
		$this->redirect = null;
		add_filter(
			'wp_redirect',
			function ( $url ) {
				$this->redirect = $url;
				return false;
			}
		);
	}

	public function tear_down() {
		remove_all_filters( 'wp_redirect' );
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	public function test_anonymous_user_redirected_to_auth() {
		$_SERVER['REQUEST_URI'] = '/tracker';
		try { peptide_starter_require_login(); } catch ( Exception $e ) { /* exit */ }
		$this->assertNotNull( $this->redirect );
		$this->assertStringContainsString( '/auth', (string) $this->redirect );
		$this->assertStringContainsString( 'redirect_to=', (string) $this->redirect );
	}

	public function test_unverified_user_redirected_to_profile_banner() {
		$user_id = self::factory()->user->create();
		update_user_meta( $user_id, 'ps_pending_verification', 1 );
		wp_set_current_user( $user_id );

		try { peptide_starter_require_login(); } catch ( Exception $e ) { /* exit */ }
		$this->assertNotNull( $this->redirect );
		$this->assertStringContainsString( 'verify_required=1', (string) $this->redirect );
	}

	public function test_verified_logged_in_user_passes_through() {
		$user_id = self::factory()->user->create();
		wp_set_current_user( $user_id );

		$reached = false;
		try {
			peptide_starter_require_login();
			$reached = true;
		} catch ( Exception $e ) { /* would be redirect */ }

		$this->assertTrue( $reached );
		$this->assertNull( $this->redirect );
	}
}
