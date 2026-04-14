<?php
/**
 * Theme Helper Functions
 *
 * Customizer output helpers, nav walker, custom logo, pagination,
 * and other utility functions shared across the theme.
 *
 * @see functions.php — includes this file
 * @see header.php — uses peptide_starter_the_custom_logo()
 * @see front-page.php — uses hero getters
 *
 * What: Utility functions for template output and theme mods.
 * Who calls it: Various templates and functions.php.
 * Dependencies: WordPress Customizer API, Walker_Nav_Menu.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation Menu Walker for handling active states.
 *
 * Adds an 'active' class to current menu items and their parents
 * for CSS styling via the .primary-navigation a.active selector.
 */
class Peptide_Starter_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Render a single menu item.
	 *
	 * @param string   $output Item output.
	 * @param WP_Post  $item   Menu item data.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Current item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent  = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
			$classes[] = 'active';
		}

		$args       = apply_filters( 'nav_menu_item_args', $args, $item, $depth );
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$item_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$item_id = $item_id ? ' id="' . esc_attr( $item_id ) . '"' : '';

		$output .= $indent . '<li' . $item_id . $class_names . '>';

		$atts = array(
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'href'   => ! empty( $item->url ) ? $item->url : '',
		);

		$atts       = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
		$attributes = '';

		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$link  = $args->before;
		$link .= '<a' . $attributes . '>';
		$link .= $args->link_before . $title . $args->link_after;
		$link .= '</a>';
		$link .= $args->after;

		$output .= $link . "</li>\n";
	}
}

/**
 * Display custom logo with fallback to site name.
 *
 * @return void
 */
function peptide_starter_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	} else {
		$site_name = get_bloginfo( 'name' );
		echo '<a class="site-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( $site_name ) . '</a>';
	}
}

/**
 * Get hero title from Customizer.
 *
 * @return string Hero title text.
 */
function peptide_starter_get_hero_title() {
	return get_theme_mod( 'hero_title', get_bloginfo( 'name' ) );
}

/**
 * Get hero subtitle from Customizer.
 *
 * @return string Hero subtitle text.
 */
function peptide_starter_get_hero_subtitle() {
	return get_theme_mod( 'hero_subtitle', esc_html__( 'A scientific peptide reference database', 'peptide-starter' ) );
}

/**
 * Get search placeholder from Customizer.
 *
 * @return string Search placeholder text.
 */
function peptide_starter_get_search_placeholder() {
	return get_theme_mod( 'hero_search_placeholder', esc_html__( 'Search peptides, sequences, or research...', 'peptide-starter' ) );
}

/**
 * Get footer copyright text from Customizer.
 *
 * @return string Copyright text (may contain safe HTML).
 */
function peptide_starter_get_footer_copyright() {
	return get_theme_mod( 'footer_copyright', esc_html__( 'Copyright © 2026 Peptide Repo. All rights reserved.', 'peptide-starter' ) );
}

/**
 * Check if newsletter form should display.
 *
 * @return bool Whether to show the newsletter form.
 */
function peptide_starter_show_newsletter_form() {
	return apply_filters( 'peptide_starter_show_newsletter', true );
}

/**
 * Render pagination links for archive pages.
 *
 * @return void
 */
function peptide_starter_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$current = max( 1, get_query_var( 'paged' ) );
	$total   = $wp_query->max_num_pages;

	echo '<nav class="ps-pagination">';
	echo paginate_links(
		array(
			'base'      => get_pagenum_link( 1 ) . '%_%',
			'format'    => 'page/%#%/',
			'current'   => $current,
			'total'     => $total,
			'prev_text' => esc_html__( '← Previous', 'peptide-starter' ),
			'next_text' => esc_html__( 'Next →', 'peptide-starter' ),
			'type'      => 'plain',
		)
	);
	echo '</nav>';
}

/**
 * Check if a specific plugin is active by its main file path.
 *
 * @param string $plugin Plugin file path relative to plugins directory.
 * @return bool Whether the plugin is active.
 */
function peptide_starter_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}
