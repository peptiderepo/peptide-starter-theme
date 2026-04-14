<?php
/**
 * Security and Feature Configuration
 *
 * Single source of truth for every security threshold, cooldown, and
 * toggle used by the theme. All values are filterable via
 * peptide_starter_security_config so deployments can override without
 * forking code.
 *
 * @see inc/rate-limiter.php — consumes login/register/contact/newsletter limits
 * @see inc/auth-handlers.php — consumes registration + email-verify settings
 * @see inc/email-verification.php — consumes token TTL + resend limit
 * @see inc/contact-handler.php — consumes contact rate limit
 * @see functions.php — includes this file
 *
 * What: Returns the security configuration array for all abuse controls.
 * Who calls it: Any handler needing a threshold; called once per request.
 * Dependencies: None — pure array + filter.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the theme-wide security configuration.
 *
 * Every threshold in the theme must be read from this function; no magic
 * numbers in handler files. Override with add_filter( 'peptide_starter_security_config', ... ).
 *
 * Cost note: this function is cheap and pure — no DB reads. Call it freely.
 *
 * @return array {
 *     @type array $rate_limits         Per-action limit + window tuples.
 *     @type int   $verify_token_ttl    Seconds a verification token stays valid.
 *     @type int   $verify_resend_limit Resends permitted per hour per IP+email.
 *     @type bool  $email_verify_required Whether new registrations require email verification.
 *     @type bool  $registration_enabled Master switch (separate from users_can_register).
 *     @type int   $newsletter_autoload_threshold Subscribers before admin migration notice.
 *     @type array $contact_topics      Allowlisted contact form topic slugs.
 *     @type int   $username_min        Minimum username length.
 *     @type int   $username_max        Maximum username length.
 *     @type int   $password_min        Minimum password length.
 * }
 */
function peptide_starter_security_config() {
	$defaults = array(
		'rate_limits'                   => array(
			// action => array( limit, window_seconds ).
			'login'      => array(
				'limit'  => 5,
				'window' => 60,
			),
			'register'   => array(
				'limit'  => 3,
				'window' => HOUR_IN_SECONDS,
			),
			'contact'    => array(
				'limit'  => 5,
				'window' => HOUR_IN_SECONDS,
			),
			'newsletter' => array(
				'limit'  => 3,
				'window' => HOUR_IN_SECONDS,
			),
			'verify_resend' => array(
				'limit'  => 2,
				'window' => HOUR_IN_SECONDS,
			),
		),
		'verify_token_ttl'              => DAY_IN_SECONDS,
		'email_verify_required'         => true,
		'registration_enabled'          => true,
		'newsletter_autoload_threshold' => 1000,
		'contact_topics'                => array( 'bug', 'feature', 'data', 'other' ),
		'username_min'                  => 5,
		'username_max'                  => 15,
		'password_min'                  => 8,
	);

	/**
	 * Filter the theme's security configuration.
	 *
	 * @param array $defaults Default configuration.
	 */
	return apply_filters( 'peptide_starter_security_config', $defaults );
}

/**
 * Shortcut to read a single top-level config key.
 *
 * @param string $key     Top-level key (e.g. 'verify_token_ttl').
 * @param mixed  $fallback Value returned when the key is missing.
 * @return mixed Configured value or $fallback.
 */
function peptide_starter_config_get( $key, $fallback = null ) {
	$config = peptide_starter_security_config();
	return array_key_exists( $key, $config ) ? $config[ $key ] : $fallback;
}

/**
 * Read a rate limit tuple (limit, window) for a given action.
 *
 * @param string $action Action slug matching a key in rate_limits.
 * @return array { 'limit' => int, 'window' => int } with safe defaults.
 */
function peptide_starter_config_rate_limit( $action ) {
	$limits = peptide_starter_config_get( 'rate_limits', array() );
	if ( isset( $limits[ $action ]['limit'], $limits[ $action ]['window'] ) ) {
		return array(
			'limit'  => (int) $limits[ $action ]['limit'],
			'window' => (int) $limits[ $action ]['window'],
		);
	}
	return array(
		'limit'  => 5,
		'window' => HOUR_IN_SECONDS,
	);
}
