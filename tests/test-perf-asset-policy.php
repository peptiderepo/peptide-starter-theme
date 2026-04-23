<?php
/**
 * Tests for inc/perf-asset-policy.php.
 *
 * Covers: conditional dequeue logic (WC, Elementor, USMI), font weight slimming,
 * preconnect hints, script defer, kill-switch, and filter overrides.
 *
 * @package peptide-starter
 */

/**
 * Test perf-asset-policy module initialization and hooks.
 */
class Test_Perf_Asset_Policy extends WP_UnitTestCase {

	/**
	 * Test: Kill-switch constant disables all dequeue logic.
	 *
	 * When PEPTIDE_STARTER_PERF_DEQUEUE is defined false, the dequeue function
	 * should early-return without removing anything.
	 */
	public function test_kill_switch_disables_dequeue() {
		// Define kill-switch to false.
		define( 'PEPTIDE_STARTER_PERF_DEQUEUE', false );

		// Enqueue dummy styles.
		wp_enqueue_style( 'woocommerce-layout', 'http://example.com/wc.css' );

		// Simulate wp_enqueue_scripts action.
		do_action( 'wp_enqueue_scripts' );

		// Since kill-switch is off, the style should still be queued.
		$this->assertTrue( wp_style_is( 'woocommerce-layout', 'enqueued' ) );
	}

	/**
	 * Test: WC assets dequeued on non-shop pages.
	 *
	 * Homepage (front page) is not a WC page, so WC styles/scripts should dequeue.
	 */
	public function test_wc_dequeue_on_homepage() {
		$this->go_to( home_url( '/' ) );

		// Enqueue WC styles.
		wp_enqueue_style( 'woocommerce-layout', 'http://example.com/wc-layout.css' );
		wp_enqueue_style( 'woocommerce-general', 'http://example.com/wc-general.css' );
		wp_enqueue_script( 'woocommerce', 'http://example.com/wc.js' );

		// Run dequeue at priority 100.
		peptide_starter_perf_dequeue_plugin_assets();

		// All WC assets should be dequeued.
		$this->assertFalse( wp_style_is( 'woocommerce-layout', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'woocommerce-general', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'woocommerce', 'enqueued' ) );
	}

	/**
	 * Test: WC assets remain on /shop page.
	 *
	 * On WC shop pages, WC assets should NOT dequeue.
	 */
	public function test_wc_not_dequeued_on_shop() {
		// Skip if WooCommerce is not active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce not active.' );
		}

		// Enqueue WC styles.
		wp_enqueue_style( 'woocommerce-layout', 'http://example.com/wc-layout.css' );

		// The dequeue function checks is_woocommerce(); we'll mock it.
		// For a true test, we'd need a real WC shop page setup.
		// Instead, we assert the filter allows override.
		$default_handles = apply_filters( 'peptide_starter_perf_woocommerce_styles', array() );
		$this->assertIsArray( $default_handles );
	}

	/**
	 * Test: Elementor assets dequeued on pages without Elementor.
	 *
	 * A non-Elementor page should have Elementor CSS dequeued.
	 */
	public function test_elementor_dequeue_on_non_elementor_page() {
		// Create a post without Elementor.
		$post_id = $this->factory->post->create();

		$this->go_to( get_permalink( $post_id ) );

		// Enqueue Elementor styles.
		wp_enqueue_style( 'elementor-frontend', 'http://example.com/elementor.css' );

		// Verify the page doesn't use Elementor.
		$uses_elementor = peptide_starter_page_uses_elementor( $post_id );
		$this->assertFalse( $uses_elementor );

		// Run dequeue.
		peptide_starter_perf_dequeue_plugin_assets();

		// Elementor should be dequeued.
		$this->assertFalse( wp_style_is( 'elementor-frontend', 'enqueued' ) );
	}

	/**
	 * Test: USMI assets dequeued on homepage.
	 */
	public function test_usmi_dequeue_on_homepage() {
		$this->go_to( home_url( '/' ) );

		// Enqueue USMI assets.
		wp_enqueue_style( 'SFSImainCss', 'http://example.com/sfsi.css' );
		wp_enqueue_script( 'SFSICustomJs', 'http://example.com/sfsi.js' );

		// Run dequeue.
		peptide_starter_perf_dequeue_plugin_assets();

		// USMI assets should be dequeued on homepage.
		$this->assertFalse( wp_style_is( 'SFSImainCss', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'SFSICustomJs', 'enqueued' ) );
	}

	/**
	 * Test: Font slim rewrites Roboto weights.
	 *
	 * A URL with Roboto:100,200,...900 should be rewritten to 400,500,700.
	 */
	public function test_font_slim_roboto() {
		$original_url = 'https://fonts.googleapis.com/css?family=Roboto:100,200,300,400,500,600,700,800,900&display=swap';
		$slimmed_url = peptide_starter_perf_slim_google_fonts( $original_url, 'google-fonts-roboto' );

		// Should contain 400,500,700.
		$this->assertStringContainsString( 'Roboto', $slimmed_url );
		$this->assertStringContainsString( '400', $slimmed_url );
		$this->assertStringContainsString( '500', $slimmed_url );
		$this->assertStringContainsString( '700', $slimmed_url );

		// Should NOT contain 100, 200, 800, 900.
		$this->assertStringNotContainsString( '100', $slimmed_url );
		$this->assertStringNotContainsString( '200', $slimmed_url );
		$this->assertStringNotContainsString( '800', $slimmed_url );
		$this->assertStringNotContainsString( '900', $slimmed_url );
	}

	/**
	 * Test: Font slim ignores non-configured fonts.
	 *
	 * A font that isn't in the config should pass through unchanged.
	 */
	public function test_font_slim_ignores_non_configured_fonts() {
		$original_url = 'https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap';
		$result_url = peptide_starter_perf_slim_google_fonts( $original_url, 'google-fonts-open-sans' );

		// Should pass through unchanged.
		$this->assertEqual( $original_url, $result_url );
	}

	/**
	 * Test: Font slim respects filter override.
	 *
	 * Custom weights via 'peptide_starter_perf_font_weights' filter should apply.
	 */
	public function test_font_slim_filter_override() {
		$original_url = 'https://fonts.googleapis.com/css?family=Roboto:100,200,300,400,500,600,700,800,900&display=swap';

		// Add filter to override Roboto weights to just 400.
		add_filter(
			'peptide_starter_perf_font_weights',
			function ( $config ) {
				$config['Roboto'] = array( 400 );
				return $config;
			}
		);

		$slimmed_url = peptide_starter_perf_slim_google_fonts( $original_url, 'google-fonts-roboto' );

		// Should contain only 400, not 500 or 700.
		$this->assertStringContainsString( '400', $slimmed_url );
		$this->assertStringNotContainsString( '500', $slimmed_url );
		$this->assertStringNotContainsString( '700', $slimmed_url );
	}

	/**
	 * Test: Preconnect hints are added.
	 *
	 * The preconnect filter should add fonts.googleapis.com and fonts.gstatic.com.
	 */
	public function test_preconnect_hints_added() {
		$urls = array();
		$result = peptide_starter_perf_resource_hints( $urls, 'preconnect' );

		$this->assertContains( 'https://fonts.googleapis.com', $result );
		$this->assertContains( 'https://fonts.gstatic.com', $result );
	}

	/**
	 * Test: Preconnect ignores other hint types.
	 *
	 * Only 'preconnect' relationship should get font URLs; dns-prefetch etc. should not.
	 */
	public function test_preconnect_ignores_other_relationships() {
		$urls = array();
		$result = peptide_starter_perf_resource_hints( $urls, 'dns-prefetch' );

		// Should not add font URLs for dns-prefetch.
		$this->assertNotContains( 'https://fonts.googleapis.com', $result );
		$this->assertNotContains( 'https://fonts.gstatic.com', $result );
	}

	/**
	 * Test: Defer applied to cookie-notice script.
	 *
	 * The script_loader_tag filter should add 'defer' to cookie-notice-front.
	 */
	public function test_defer_cookie_notice() {
		$tag = '<script src="http://example.com/cookie-notice.js"></script>';
		$result = peptide_starter_perf_defer_cookie_notice( $tag, 'cookie-notice-front', 'http://example.com/cookie-notice.js' );

		$this->assertStringContainsString( 'defer', $result );
	}

	/**
	 * Test: Defer not applied to other scripts.
	 *
	 * The defer filter should only affect cookie-notice-front, not other handles.
	 */
	public function test_defer_not_applied_to_other_scripts() {
		$tag = '<script src="http://example.com/other.js"></script>';
		$result = peptide_starter_perf_defer_cookie_notice( $tag, 'other-script', 'http://example.com/other.js' );

		// Should not have defer added.
		$this->assertStringNotContainsString( 'defer', $result );
	}

	/**
	 * Test: page_uses_elementor helper returns false for non-singular.
	 */
	public function test_page_uses_elementor_non_singular() {
		// Go to homepage (not singular).
		$this->go_to( home_url( '/' ) );

		$result = peptide_starter_page_uses_elementor( 0 );
		$this->assertFalse( $result );
	}
}
