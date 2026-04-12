<?php
/**
 * PHPUnit bootstrap file for Peptide Starter theme tests.
 *
 * What: Loads WordPress test framework and the theme.
 * Who calls it: PHPUnit via phpunit.xml bootstrap attribute.
 * Dependencies: WordPress test suite (WP_TESTS_DIR env var or default path).
 *
 * @package peptide-starter
 */

// Composer autoloader.
$autoloader = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoloader ) ) {
	require_once $autoloader;
}

/*
 * Path to the WordPress test suite.
 * Set WP_TESTS_DIR env var, or fall back to /tmp/wordpress-tests-lib.
 * CI pipelines should install the WP test suite before running tests.
 */
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Check that the test suite is installed.
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test suite not found at {$_tests_dir}.\n";
	echo "Run: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]\n";
	exit( 1 );
}

// Load the WP test suite functions.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Register the theme so WordPress loads it during test setup.
 */
function _register_theme() {
	$theme_dir  = dirname( __DIR__ );
	$theme_name = basename( $theme_dir );

	// Tell WordPress where our theme lives.
	register_theme_directory( dirname( $theme_dir ) );
	add_filter(
		'pre_option_stylesheet',
		function () use ( $theme_name ) {
			return $theme_name;
		}
	);
	add_filter(
		'pre_option_template',
		function () use ( $theme_name ) {
			return $theme_name;
		}
	);
}
tests_add_filter( 'muplugins_loaded', '_register_theme' );

// Boot the WP test framework.
require $_tests_dir . '/includes/bootstrap.php';
