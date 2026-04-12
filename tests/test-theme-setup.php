<?php
/**
 * Tests for theme setup and registration.
 *
 * What: Validates theme supports, nav menus, and widget areas are registered.
 * Who calls it: PHPUnit via the "theme" test suite.
 * Dependencies: WordPress test framework, theme functions.php loaded.
 *
 * @package peptide-starter
 */

/**
 * Test theme setup: supports, menus, widgets, scripts.
 */
class Test_Theme_Setup extends WP_UnitTestCase {

	/**
	 * Verify essential theme supports are registered.
	 */
	public function test_theme_supports_registered() {
		$this->assertTrue( current_theme_supports( 'title-tag' ), 'Theme must support title-tag.' );
		$this->assertTrue( current_theme_supports( 'post-thumbnails' ), 'Theme must support post-thumbnails.' );
		$this->assertTrue( current_theme_supports( 'custom-logo' ), 'Theme must support custom-logo.' );
		$this->assertTrue( current_theme_supports( 'html5' ), 'Theme must support html5.' );
	}

	/**
	 * Verify navigation menus are registered.
	 */
	public function test_nav_menus_registered() {
		$menus = get_registered_nav_menus();
		$this->assertArrayHasKey( 'primary', $menus, 'Primary menu must be registered.' );
		$this->assertArrayHasKey( 'footer', $menus, 'Footer menu must be registered.' );
	}

	/**
	 * Verify footer widget areas are registered.
	 */
	public function test_footer_widget_areas_registered() {
		global $wp_registered_sidebars;

		for ( $i = 1; $i <= 4; $i++ ) {
			$this->assertArrayHasKey(
				'footer-' . $i,
				$wp_registered_sidebars,
				"Footer widget area footer-{$i} must be registered."
			);
		}
	}

	/**
	 * Verify the custom nav walker class exists.
	 */
	public function test_nav_walker_class_exists() {
		$this->assertTrue(
			class_exists( 'Peptide_Starter_Nav_Walker' ),
			'Peptide_Starter_Nav_Walker class must be defined.'
		);
	}

	/**
	 * Verify scripts and styles are enqueued on the front end.
	 */
	public function test_scripts_and_styles_enqueued() {
		// Simulate a front-end page load.
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'peptide-starter-style', 'enqueued' ), 'Main stylesheet must be enqueued.' );
		$this->assertTrue( wp_script_is( 'peptide-starter-navigation', 'enqueued' ), 'Navigation script must be enqueued.' );
		$this->assertTrue( wp_script_is( 'peptide-starter-theme', 'enqueued' ), 'Theme script must be enqueued.' );
	}
}
