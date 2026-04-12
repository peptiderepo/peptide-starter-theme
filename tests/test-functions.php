<?php
/**
 * Tests for functions.php helper functions.
 *
 * What: Validates theme helper functions return correct values.
 * Who calls it: PHPUnit via the "theme" test suite.
 * Dependencies: WordPress test framework, theme functions.php loaded.
 *
 * @package peptide-starter
 */

/**
 * Test helper functions defined in functions.php.
 */
class Test_Theme_Functions extends WP_UnitTestCase {

	/**
	 * Verify the theme version constant is defined and matches semver format.
	 */
	public function test_version_constant_defined() {
		$this->assertTrue( defined( 'PEPTIDE_STARTER_VERSION' ), 'PEPTIDE_STARTER_VERSION must be defined.' );
		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+$/', PEPTIDE_STARTER_VERSION, 'Version must follow semver (x.y.z).' );
	}

	/**
	 * Verify directory constant points to an existing path.
	 */
	public function test_dir_constant_defined() {
		$this->assertTrue( defined( 'PEPTIDE_STARTER_DIR' ), 'PEPTIDE_STARTER_DIR must be defined.' );
		$this->assertDirectoryExists( PEPTIDE_STARTER_DIR );
	}

	/**
	 * Hero title should return the site name when no customizer override is set.
	 */
	public function test_get_hero_title_returns_default() {
		$title = peptide_starter_get_hero_title();
		$this->assertNotEmpty( $title, 'Hero title must not be empty.' );
		$this->assertEquals( get_bloginfo( 'name' ), $title, 'Default hero title should match site name.' );
	}

	/**
	 * Hero subtitle should return a non-empty string.
	 */
	public function test_get_hero_subtitle_returns_default() {
		$subtitle = peptide_starter_get_hero_subtitle();
		$this->assertNotEmpty( $subtitle );
	}

	/**
	 * Search placeholder should return a non-empty string.
	 */
	public function test_get_search_placeholder_returns_default() {
		$placeholder = peptide_starter_get_search_placeholder();
		$this->assertNotEmpty( $placeholder );
	}

	/**
	 * Footer copyright should return a non-empty string.
	 */
	public function test_get_footer_copyright_returns_default() {
		$copyright = peptide_starter_get_footer_copyright();
		$this->assertNotEmpty( $copyright );
	}

	/**
	 * Newsletter form visibility filter should default to true.
	 */
	public function test_show_newsletter_form_default_true() {
		$this->assertTrue( peptide_starter_show_newsletter_form() );
	}

	/**
	 * Newsletter form visibility can be disabled via filter.
	 */
	public function test_show_newsletter_form_filterable() {
		add_filter( 'peptide_starter_show_newsletter', '__return_false' );
		$this->assertFalse( peptide_starter_show_newsletter_form() );
		remove_filter( 'peptide_starter_show_newsletter', '__return_false' );
	}

	/**
	 * Dark mode body class should only be added when dark_mode_default is true.
	 */
	public function test_dark_mode_body_class_not_added_by_default() {
		$classes = peptide_starter_dark_mode_body_class( array() );
		$this->assertNotContains( 'dark-mode-default', $classes );
	}

	/**
	 * Sanitize checkbox should return true for truthy input.
	 */
	public function test_sanitize_checkbox_truthy() {
		$this->assertTrue( peptide_starter_sanitize_checkbox( true ) );
	}

	/**
	 * Sanitize checkbox should return false for falsy input.
	 */
	public function test_sanitize_checkbox_falsy() {
		$this->assertFalse( peptide_starter_sanitize_checkbox( false ) );
		$this->assertFalse( peptide_starter_sanitize_checkbox( null ) );
		$this->assertFalse( peptide_starter_sanitize_checkbox( '' ) );
	}
}
