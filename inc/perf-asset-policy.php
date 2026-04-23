<?php
/**
 * Performance Asset Policy Module
 *
 * Optimizes the front-end asset graph by dequeuing unnecessary plugin styles/scripts
 * and slimming font requests. Targets mobile LCP improvement through:
 * - Conditional dequeue of WC, Elementor, and USMI assets
 * - Google Fonts weight slimming (36 faces → 5)
 * - Preconnect hints for font.googleapis.com + fonts.gstatic.com
 * - Defer attribute on cookie-notice script
 *
 * What: Performance optimizations; no template changes.
 * Who calls it: functions.php via add_action( 'after_setup_theme', '..._init' ).
 * Dependencies: WordPress hooks system; works independently.
 *
 * @see functions.php — requires and hooks this module
 * @see CHANGELOG.md — [1.6.0] release notes
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the performance asset policy module.
 *
 * Wires all dequeue, font slim, preconnect, and defer hooks at the correct priorities.
 * Called once per request from the 'after_setup_theme' action.
 *
 * @return void
 */
function peptide_starter_perf_asset_policy_init() {
	// Dequeue unnecessary plugin assets at priority 100 (one beat after plugin_overrides).
	add_action( 'wp_enqueue_scripts', 'peptide_starter_perf_dequeue_plugin_assets', 100 );

	// Slim Google Fonts to essential weights only.
	add_filter( 'style_loader_src', 'peptide_starter_perf_slim_google_fonts', 10, 2 );

	// Add preconnect hints for font servers.
	add_filter( 'wp_resource_hints', 'peptide_starter_perf_resource_hints', 10, 2 );

	// Defer cookie-notice script to prevent render blocking.
	add_filter( 'script_loader_tag', 'peptide_starter_perf_defer_cookie_notice', 10, 3 );
}

/**
 * Dequeue assets on non-applicable page types.
 *
 * Removes WooCommerce, Elementor, and Ultimate Social Media Icons assets from pages
 * that don't use them. Controlled by the kill-switch constant PEPTIDE_STARTER_PERF_DEQUEUE.
 *
 * Dequeue conditions:
 * - WC: removed unless on shop, cart, checkout, or account pages
 * - Elementor: removed if page doesn't use Elementor builder
 * - USMI: removed on homepage, archives, and blog index
 *
 * @return void
 */
function peptide_starter_perf_dequeue_plugin_assets() {
	// Kill-switch: if disabled in wp-config.php, do nothing.
	if ( defined( 'PEPTIDE_STARTER_PERF_DEQUEUE' ) && ! PEPTIDE_STARTER_PERF_DEQUEUE ) {
		return;
	}

	// WooCommerce assets: dequeue on non-shop pages.
	if ( function_exists( 'is_woocommerce' ) && ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		// Get configured WC handles from filter; default list.
		$wc_styles = apply_filters(
			'peptide_starter_perf_woocommerce_styles',
			array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' )
		);

		foreach ( $wc_styles as $handle ) {
			wp_dequeue_style( $handle );
		}

		$wc_scripts = apply_filters(
			'peptide_starter_perf_woocommerce_scripts',
			array( 'wc-add-to-cart', 'woocommerce', 'sourcebuster-js', 'wc-order-attribution' )
		);

		foreach ( $wc_scripts as $handle ) {
			wp_dequeue_script( $handle );
		}
	}

	// Elementor assets: dequeue if page doesn't use Elementor.
	if ( ! peptide_starter_page_uses_elementor( get_queried_object_id() ) ) {
		$elementor_handles = apply_filters(
			'peptide_starter_perf_elementor_handles',
			array( 'elementor-frontend', 'elementor-frontend-legacy' )
		);

		foreach ( $elementor_handles as $handle ) {
			wp_dequeue_style( $handle );
		}

		// Dequeue Elementor per-post CSS files (pattern: posts-*.css or post-*.css).
		// These are added dynamically; we identify them by src pattern and remove before render.
		// Note: post-22 and post-34 are specific Elementor archive template files.
		global $wp_styles;
		if ( isset( $wp_styles->queue ) ) {
			$elementor_post_styles = array();
			foreach ( $wp_styles->queue as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$src = $wp_styles->registered[ $handle ]->src;
					// Detect Elementor per-post CSS by path pattern.
					if ( $src && preg_match( '/uploads\/elementor\/css\/post-\d+\.css/', $src ) ) {
						$elementor_post_styles[] = $handle;
					}
				}
			}

			foreach ( $elementor_post_styles as $handle ) {
				wp_dequeue_style( $handle );
			}
		}
	}

	// Ultimate Social Media Icons: dequeue on homepage, archives, and blog index.
	if ( is_front_page() || is_home() || is_archive() ) {
		$usmi_handles = apply_filters(
			'peptide_starter_perf_usmi_handles',
			array(
				'SFSImainCss',
				'SFSIjqueryModernizr',
				'SFSIjqueryShuffle',
				'SFSIjqueryrandom-shuffle',
				'SFSICustomJs',
			)
		);

		foreach ( $usmi_handles as $handle ) {
			wp_dequeue_style( $handle );
			wp_dequeue_script( $handle );
		}
	}
}

/**
 * Slim Google Fonts to essential weights only.
 *
 * Rewrites fonts.googleapis.com URLs to keep only:
 * - Roboto: 400, 500, 700 (no italics)
 * - Roboto Slab: 400, 700 (no italics)
 *
 * This reduces the font CSS from ~6KB to <1KB and the font payload by ~80%.
 * Filterable per family via 'peptide_starter_perf_font_weights'.
 *
 * @param string $src    Stylesheet URL.
 * @param string $handle Stylesheet handle.
 * @return string Rewritten URL or original.
 */
function peptide_starter_perf_slim_google_fonts( $src, $handle ) {
	if ( ! $src || false === strpos( $src, 'fonts.googleapis.com' ) ) {
		return $src;
	}

	// Parse the URL to get the query string.
	$parsed = wp_parse_url( $src );
	if ( empty( $parsed['query'] ) ) {
		return $src;
	}

	// Extract family parameter.
	parse_str( $parsed['query'], $query_vars );
	if ( empty( $query_vars['family'] ) ) {
		return $src;
	}

	$families_str = $query_vars['family'];

	// Decode the family string (it may be URL-encoded).
	$families_str = rawurldecode( $families_str );

	// Split families by + (multiple families in one request).
	$families = explode( '+', $families_str );

	// Get configured font weights from filter.
	$font_weights_config = apply_filters(
		'peptide_starter_perf_font_weights',
		array(
			'Roboto'       => array( 400, 500, 700 ),
			'Roboto Slab'  => array( 400, 700 ),
		)
	);

	$rewritten_families = array();
	foreach ( $families as $family ) {
		// Clean up the family name (URL decode + trim).
		$family = trim( rawurldecode( $family ) );

		// Check if this family has a configured weight list.
		$matched = false;
		foreach ( $font_weights_config as $config_family => $weights ) {
			if ( 0 === strcasecmp( $family, $config_family ) ) {
				// Rewrite with only the specified weights.
				$weights_str = implode( ',', $weights );
				$rewritten_families[] = $config_family . ':' . $weights_str;
				$matched = true;
				break;
			}
		}

		// If no config match, pass through unchanged.
		if ( ! $matched ) {
			$rewritten_families[] = $family;
		}
	}

	// If nothing changed, return original.
	if ( count( $rewritten_families ) === count( $families ) ) {
		// Check if any actually changed (compare raw values).
		$original_str = implode( '+', $families );
		$rewritten_str = implode( '+', $rewritten_families );

		if ( $original_str === $rewritten_str ) {
			return $src;
		}
	}

	// Rebuild the URL with the new family parameter.
	$new_query_vars = $query_vars;
	$new_query_vars['family'] = implode( '+', $rewritten_families );
	$new_query_str = http_build_query( $new_query_vars );

	// Reconstruct the full URL.
	$new_src = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'] . '?' . $new_query_str;

	return $new_src;
}

/**
 * Add preconnect hints for font servers.
 *
 * Reduces TLS + DNS latency for Google Fonts requests on cold cache.
 * Adds preconnect for fonts.googleapis.com and fonts.gstatic.com.
 *
 * @param array  $urls  List of URLs to add hints for.
 * @param string $rel   The relationship type (preconnect, dns-prefetch, etc.).
 * @return array Modified URL list.
 */
function peptide_starter_perf_resource_hints( $urls, $rel ) {
	// Only add hints for preconnect relationships.
	if ( 'preconnect' !== $rel ) {
		return $urls;
	}

	// Add font servers if not already present.
	$font_urls = apply_filters(
		'peptide_starter_perf_font_preconnect_urls',
		array(
			'https://fonts.googleapis.com',
			'https://fonts.gstatic.com',
		)
	);

	foreach ( $font_urls as $url ) {
		if ( ! in_array( $url, $urls, true ) ) {
			$urls[] = $url;
		}
	}

	return $urls;
}

/**
 * Defer the cookie-notice script to avoid render-blocking.
 *
 * Adds 'defer' attribute to cookie-notice JS while preserving DOM-ready order.
 * This ensures the cookie banner loads without blocking initial rendering.
 *
 * @param string $tag    The script tag HTML.
 * @param string $handle The script handle.
 * @param string $src    The script URL (unused).
 * @return string Modified script tag.
 */
function peptide_starter_perf_defer_cookie_notice( $tag, $handle, $src ) {
	// Only modify the cookie-notice script.
	if ( 'cookie-notice-front' !== $handle ) {
		return $tag;
	}

	// Add defer attribute if not already present.
	if ( false === strpos( $tag, 'defer' ) ) {
		// Insert defer before the closing >.
		$tag = str_replace( '<script ', '<script defer ', $tag );
	}

	return $tag;
}

/**
 * Check if a page uses the Elementor builder.
 *
 * Inspects the _elementor_edit_mode post meta. If the page is in builder mode,
 * we assume Elementor styles are needed (even if no actual content yet).
 *
 * @param int $post_id The post ID to check.
 * @return bool True if page uses Elementor, false otherwise.
 */
function peptide_starter_page_uses_elementor( $post_id ) {
	if ( ! $post_id || ! is_singular() ) {
		return false;
	}

	$edit_mode = get_post_meta( $post_id, '_elementor_edit_mode', true );

	return ! empty( $edit_mode ) && 'builder' === $edit_mode;
}
