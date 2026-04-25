<?php
/**
 * Theme Helper Functions
 *
 * Customizer output helpers, nav walker, custom logo, pagination, menu
 * fallback, auth gates, and assorted utilities shared across the theme.
 *
 * @see functions.php — includes this file
 * @see header.php — uses peptide_starter_the_custom_logo() + menu fallback
 * @see page-subject-log.php, page-tracker.php, page-protocol-builder.php —
 *   use peptide_starter_require_login()
 *
 * What: Utility functions for template output, theme mods, auth gating.
 * Who calls it: Templates and handler files.
 * Dependencies: WordPress Customizer API, Walker_Nav_Menu, email-verification.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Navigation Menu Walker — adds active classes.
 *
 * What: Renders menu items with an 'active' class for current + ancestor.
 * Who calls it: wp_nav_menu() in header.php with walker => new instance.
 * Dependencies: Walker_Nav_Menu.
 */
class Peptide_Starter_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Render a single menu item.
	 *
	 * @param string   $output Item output accumulator.
	 * @param WP_Post  $item   Menu item data.
	 * @param int      $depth  Menu depth.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Current item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent    = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
			$classes[] = 'active';
		}

		$args        = apply_filters( 'nav_menu_item_args', $args, $item, $depth );
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
 * Fallback menu when no primary nav is assigned in WP admin.
 *
 * Previously lived inline inside header.php; moved here in v1.5.1 to
 * prevent redeclaration if header.php is ever included twice.
 *
 * @return void Echoes a complete <ul>.
 */
function peptide_starter_primary_menu_fallback() {
	?>
	<ul>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'peptide-starter' ); ?></a></li>
		<li class="menu-item-has-children">
			<a href="#"><?php esc_html_e( 'Tools', 'peptide-starter' ); ?></a>
			<ul class="sub-menu">
				<li><a href="<?php echo esc_url( home_url( '/calculator' ) ); ?>"><?php esc_html_e( 'Calculator', 'peptide-starter' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/protocol-builder' ) ); ?>"><?php esc_html_e( 'Protocol Builder', 'peptide-starter' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/tracker' ) ); ?>"><?php esc_html_e( 'Tracker', 'peptide-starter' ); ?></a></li>
			</ul>
		</li>
		<li class="menu-item-has-children">
			<a href="#"><?php esc_html_e( 'My Data', 'peptide-starter' ); ?></a>
			<ul class="sub-menu">
				<li><a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>"><?php esc_html_e( 'Peptides', 'peptide-starter' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/subject-log' ) ); ?>"><?php esc_html_e( 'Subject Log', 'peptide-starter' ); ?></a></li>
			</ul>
		</li>
		<li class="menu-item-has-children">
			<a href="#"><?php esc_html_e( 'Resources', 'peptide-starter' ); ?></a>
			<ul class="sub-menu">
				<li><a href="<?php echo esc_url( home_url( '/documentation' ) ); ?>"><?php esc_html_e( 'Documentation', 'peptide-starter' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/news' ) ); ?>"><?php esc_html_e( 'Science Feed', 'peptide-starter' ); ?></a></li>
			</ul>
		</li>
	</ul>
	<?php
}

/**
 * Display custom logo with fallback to site name link.
 *
 * @return void
 */
function peptide_starter_the_custom_logo() {
	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	// Use SVG logo if available
	$logo_path = get_template_directory() . '/assets/brand/logo-horizontal.svg';
	if ( file_exists( $logo_path ) ) {
		echo '<a class="site-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
		include $logo_path;
		echo '</a>';
		return;
	}
	// Fallback to text
	$site_name = get_bloginfo( 'name' );
	echo '<a class="site-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( $site_name ) . '</a>';
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
 * Whether to render the newsletter signup section. Filterable.
 *
 * @return bool
 */
function peptide_starter_show_newsletter_form() {
	return apply_filters( 'peptide_starter_show_newsletter', true );
}

/**
 * Render pagination for archive pages.
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
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links returns safe HTML.
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
 * Whether a plugin is active by its main file path.
 *
 * @param string $plugin Plugin file path relative to plugins/.
 * @return bool
 */
function peptide_starter_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}

/**
 * Safe wp_get_referer fallback — never returns empty.
 *
 * @return string Referer or home_url('/').
 */
function peptide_starter_safe_referer() {
	$ref = wp_get_referer();
	return $ref ? $ref : home_url( '/' );
}

/**
 * Gate a template behind login + verified email.
 *
 * Unauthenticated → redirect to /auth?redirect_to=current-uri.
 * Logged in but unverified → redirect to /profile?verify_required=1.
 *
 * Side effects: emits 302 and exits the request.
 *
 * @param string|null $redirect_to Optional override for post-login target.
 * @return void
 */
function peptide_starter_require_login( $redirect_to = null ) {
	if ( ! is_user_logged_in() ) {
		$target = $redirect_to ? $redirect_to : ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' );
		wp_safe_redirect( home_url( '/auth?redirect_to=' . rawurlencode( $target ) ) );
		exit;
	}

	if ( function_exists( 'peptide_starter_user_is_verified' ) && ! peptide_starter_user_is_verified( get_current_user_id() ) ) {
		wp_safe_redirect( home_url( '/profile?verify_required=1' ) );
		exit;
	}
}

/**
 * Render a honeypot input block for a given form.
 *
 * Pair with peptide_starter_honeypot_triggered() in the handler.
 *
 * @param string $form_name Short slug embedded in the field name.
 * @return void Echoes HTML.
 */
function peptide_starter_render_honeypot( $form_name ) {
	$field = 'ps_hp_' . preg_replace( '/[^a-z0-9_]/', '', $form_name );
	?>
	<div class="ps-hp-wrap" aria-hidden="true">
		<label>
			<?php esc_html_e( 'Leave this field empty', 'peptide-starter' ); ?>
			<input type="text" name="<?php echo esc_attr( $field ); ?>" tabindex="-1" autocomplete="off" value="">
		</label>
	</div>
	<?php
}

/**
 * CSV-injection-safe value for fputcsv output.
 *
 * Prefixes dangerous leading characters (=, +, -, @, tab, CR) with a
 * single quote so Excel/Sheets won't interpret the cell as a formula.
 *
 * @param string $value Raw cell value.
 * @return string Safe cell value.
 */
function peptide_starter_csv_safe( $value ) {
	$value = (string) $value;
	if ( '' === $value ) {
		return $value;
	}
	$dangerous = array( '=', '+', '-', '@', "\t", "\r" );
	if ( in_array( $value[0], $dangerous, true ) ) {
		return "'" . $value;
	}
	return $value;
}
