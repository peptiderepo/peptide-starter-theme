<?php
/**
 * Tests for newsletter signup handler + CSV-safe export helper.
 *
 * @package peptide-starter
 */

class Test_Newsletter extends WP_UnitTestCase {

	public function tear_down() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ps_rl_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ps_rl_%'" );
		delete_option( 'ps_newsletter_emails' );
		$_POST = array();
		parent::tear_down();
	}

	public function test_csv_safe_prefixes_dangerous_leading_chars() {
		$this->assertSame( "'=cmd|' /C calc'!A0", peptide_starter_csv_safe( "=cmd|' /C calc'!A0" ) );
		$this->assertSame( "'+1234",              peptide_starter_csv_safe( '+1234' ) );
		$this->assertSame( "'-1234",              peptide_starter_csv_safe( '-1234' ) );
		$this->assertSame( "'@evil",              peptide_starter_csv_safe( '@evil' ) );
		$this->assertSame( "'\tTab",              peptide_starter_csv_safe( "\tTab" ) );
		$this->assertSame( "'\rCR",               peptide_starter_csv_safe( "\rCR" ) );
	}

	public function test_csv_safe_passes_safe_values_through() {
		$this->assertSame( 'alice@example.com', peptide_starter_csv_safe( 'alice@example.com' ) );
		$this->assertSame( '2026-04-14 10:30:00', peptide_starter_csv_safe( '2026-04-14 10:30:00' ) );
		$this->assertSame( '', peptide_starter_csv_safe( '' ) );
	}

	public function test_signup_stores_email_with_unsub_token() {
		$_POST = array(
			'ps_newsletter_nonce' => wp_create_nonce( 'peptide_starter_newsletter' ),
			'email'               => 'sub@example.com',
			'ps_consent'          => '1',
		);
		$this->invoke_signup();

		$emails = get_option( 'ps_newsletter_emails', array() );
		$this->assertCount( 1, $emails );
		$this->assertSame( 'sub@example.com', $emails[0]['email'] );
		$this->assertNotEmpty( $emails[0]['unsub_token'] );
	}

	public function test_signup_without_consent_rejected() {
		$_POST = array(
			'ps_newsletter_nonce' => wp_create_nonce( 'peptide_starter_newsletter' ),
			'email'               => 'nope@example.com',
		);
		$this->invoke_signup();
		$emails = get_option( 'ps_newsletter_emails', array() );
		$this->assertCount( 0, $emails );
	}

	public function test_signup_duplicate_does_not_add_second_row() {
		$initial = array(
			array(
				'email'       => 'dup@example.com',
				'date'        => '2026-04-10 00:00:00',
				'unsub_token' => 'abc',
			),
		);
		update_option( 'ps_newsletter_emails', $initial, false );

		$_POST = array(
			'ps_newsletter_nonce' => wp_create_nonce( 'peptide_starter_newsletter' ),
			'email'               => 'dup@example.com',
			'ps_consent'          => '1',
		);
		$this->invoke_signup();

		$emails = get_option( 'ps_newsletter_emails', array() );
		$this->assertCount( 1, $emails );
	}

	/**
	 * Invoke the handler and catch the wp_safe_redirect exit.
	 */
	protected function invoke_signup() {
		$captured = null;
		$filter   = function ( $url ) use ( &$captured ) {
			$captured = $url;
			return false;
		};
		add_filter( 'wp_redirect', $filter );

		try {
			peptide_starter_handle_newsletter_signup();
		} catch ( Exception $e ) { /* exit path */ }

		remove_filter( 'wp_redirect', $filter );
	}
}
