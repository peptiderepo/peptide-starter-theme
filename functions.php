<?php
/**
 * Peptide Starter Theme Functions
 *
 * @package peptide-starter
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'PEPTIDE_STARTER_VERSION', '1.4.0' );
define( 'PEPTIDE_STARTER_DIR', get_template_directory() );
define( 'PEPTIDE_STARTER_URI', get_template_directory_uri() );

/**
 * Set up theme defaults and register support for various WordPress features
 */
function peptide_starter_setup() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Register navigation menus
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'peptide-starter' ),
			'footer'  => esc_html__( 'Footer Menu', 'peptide-starter' ),
		)
	);

	// Add support for wide blocks
	add_theme_support( 'align-wide' );

	// Load text domain
	load_theme_textdomain( 'peptide-starter', PEPTIDE_STARTER_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'peptide_starter_setup' );

/**
 * Register and enqueue stylesheets and scripts
 */
function peptide_starter_scripts() {
	// Register and enqueue main stylesheet
	wp_enqueue_style( 'peptide-starter-style', PEPTIDE_STARTER_URI . '/style.css', array(), PEPTIDE_STARTER_VERSION );

	// Add conditional scripts
	wp_enqueue_script( 'peptide-starter-navigation', PEPTIDE_STARTER_URI . '/assets/js/navigation.js', array(), PEPTIDE_STARTER_VERSION, true );
	wp_enqueue_script( 'peptide-starter-theme', PEPTIDE_STARTER_URI . '/assets/js/theme.js', array(), PEPTIDE_STARTER_VERSION, true );

	// Pass PHP data to JavaScript
	wp_localize_script(
		'peptide-starter-theme',
		'peptideStarterData',
		array(
			'siteUrl'     => home_url( '/' ),
			'isDarkMode'  => get_theme_mod( 'dark_mode_default', false ),
		)
	);

	// Dequeue comment reply script if comments are not open
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'peptide_starter_scripts' );

/**
 * Enqueue plugin override styles at a late priority so they load AFTER plugin stylesheets.
 * This ensures theme overrides (e.g. removing line-clamp on article excerpts) beat plugin CSS.
 */
function peptide_starter_plugin_overrides() {
	wp_add_inline_style(
		'peptide-starter-style',
		'
		/* Override Peptide News plugin line-clamp — show full summaries */
		p.pn-article-excerpt,
		.pn-article-card p.pn-article-excerpt,
		body .pn-article-excerpt {
			-webkit-line-clamp: unset !important;
			-webkit-box-orient: unset !important;
			display: block !important;
			overflow: visible !important;
		}
	'
	);
}
add_action( 'wp_enqueue_scripts', 'peptide_starter_plugin_overrides', 99 );

/**
 * Register widget areas
 */
function peptide_starter_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer - Column 1', 'peptide-starter' ),
			'id'            => 'footer-1',
			'description'   => esc_html__( 'Footer widget area 1', 'peptide-starter' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer - Column 2', 'peptide-starter' ),
			'id'            => 'footer-2',
			'description'   => esc_html__( 'Footer widget area 2', 'peptide-starter' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer - Column 3', 'peptide-starter' ),
			'id'            => 'footer-3',
			'description'   => esc_html__( 'Footer widget area 3', 'peptide-starter' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer - Column 4', 'peptide-starter' ),
			'id'            => 'footer-4',
			'description'   => esc_html__( 'Footer widget area 4', 'peptide-starter' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'peptide_starter_widgets_init' );

/**
 * Register theme customizer options
 */
function peptide_starter_customize_register( $wp_customize ) {
	// Logo section
	$wp_customize->add_section(
		'peptide_starter_branding',
		array(
			'title'    => esc_html__( 'Branding', 'peptide-starter' ),
			'priority' => 30,
		)
	);

	// Hero settings section
	$wp_customize->add_section(
		'peptide_starter_hero',
		array(
			'title'    => esc_html__( 'Hero Section', 'peptide-starter' ),
			'priority' => 40,
		)
	);

	// Hero Title
	$wp_customize->add_setting(
		'hero_title',
		array(
			'default'           => get_bloginfo( 'name' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'hero_title',
		array(
			'label'       => esc_html__( 'Hero Title', 'peptide-starter' ),
			'section'     => 'peptide_starter_hero',
			'type'        => 'text',
		)
	);

	// Hero Subtitle
	$wp_customize->add_setting(
		'hero_subtitle',
		array(
			'default'           => esc_html__( 'A scientific peptide reference database', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'hero_subtitle',
		array(
			'label'       => esc_html__( 'Hero Subtitle', 'peptide-starter' ),
			'section'     => 'peptide_starter_hero',
			'type'        => 'textarea',
		)
	);

	// Hero Search Placeholder
	$wp_customize->add_setting(
		'hero_search_placeholder',
		array(
			'default'           => esc_html__( 'Search peptides, sequences, or research...', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'hero_search_placeholder',
		array(
			'label'       => esc_html__( 'Search Placeholder', 'peptide-starter' ),
			'section'     => 'peptide_starter_hero',
			'type'        => 'text',
		)
	);

	// Footer section
	$wp_customize->add_section(
		'peptide_starter_footer',
		array(
			'title'    => esc_html__( 'Footer', 'peptide-starter' ),
			'priority' => 50,
		)
	);

	// Footer Copyright Text — allows safe HTML (links, bold, em) via wp_kses_post
	$wp_customize->add_setting(
		'footer_copyright',
		array(
			'default'           => esc_html__( 'Copyright © 2026 Peptide Repo. All rights reserved.', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wp_kses_post',
		)
	);

	$wp_customize->add_control(
		'footer_copyright',
		array(
			'label'       => esc_html__( 'Copyright Text', 'peptide-starter' ),
			'section'     => 'peptide_starter_footer',
			'type'        => 'textarea',
		)
	);

	// Dark mode section
	$wp_customize->add_section(
		'peptide_starter_theme_mode',
		array(
			'title'    => esc_html__( 'Theme Mode', 'peptide-starter' ),
			'priority' => 60,
		)
	);

	// Dark mode default
	$wp_customize->add_setting(
		'dark_mode_default',
		array(
			'default'           => false,
			'transport'         => 'refresh',
			'sanitize_callback' => 'peptide_starter_sanitize_checkbox',
		)
	);

	$wp_customize->add_control(
		'dark_mode_default',
		array(
			'label'       => esc_html__( 'Enable Dark Mode by Default', 'peptide-starter' ),
			'section'     => 'peptide_starter_theme_mode',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'peptide_starter_customize_register' );

/**
 * Sanitize checkbox values for the Customizer.
 *
 * Returns true only for truthy values; everything else becomes false.
 * Used as sanitize_callback for the dark_mode_default setting.
 *
 * @param mixed $checked The raw value from the Customizer.
 * @return bool True if checked, false otherwise.
 */
function peptide_starter_sanitize_checkbox( $checked ) {
	return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Add custom logo support
 */
function peptide_starter_get_custom_logo() {
	if ( function_exists( 'get_custom_logo' ) ) {
		return get_custom_logo();
	}
	return '';
}

/**
 * Navigation Menu Walker for handling active states
 */
class Peptide_Starter_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current-menu-parent', $classes, true ) ) {
			$classes[] = 'active';
		}

		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value      = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$link = $args->before;
		$link .= '<a' . $attributes . '>';
		$link .= $args->link_before . $title . $args->link_after;
		$link .= '</a>';
		$link .= $args->after;

		$output .= apply_filters( 'nav_menu_item_title', $link, $item, $args, $depth );

		$output .= '</li>' . $n;
	}
}

/**
 * Display custom logo
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
 * Get hero title
 */
function peptide_starter_get_hero_title() {
	return get_theme_mod( 'hero_title', get_bloginfo( 'name' ) );
}

/**
 * Get hero subtitle
 */
function peptide_starter_get_hero_subtitle() {
	return get_theme_mod( 'hero_subtitle', esc_html__( 'A scientific peptide reference database', 'peptide-starter' ) );
}

/**
 * Get search placeholder
 */
function peptide_starter_get_search_placeholder() {
	return get_theme_mod( 'hero_search_placeholder', esc_html__( 'Search peptides, sequences, or research...', 'peptide-starter' ) );
}

/**
 * Get footer copyright text
 */
function peptide_starter_get_footer_copyright() {
	return get_theme_mod( 'footer_copyright', esc_html__( 'Copyright © 2026 Peptide Repo. All rights reserved.', 'peptide-starter' ) );
}

/**
 * Apply dark mode class to body if enabled
 */
function peptide_starter_dark_mode_body_class( $classes ) {
	if ( get_theme_mod( 'dark_mode_default', false ) ) {
		$classes[] = 'dark-mode-default';
	}
	return $classes;
}
add_filter( 'body_class', 'peptide_starter_dark_mode_body_class' );

/**
 * Register pagination function
 */
function peptide_starter_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$current_page = max( 1, get_query_var( 'paged' ) );
	$total_pages  = $wp_query->max_num_pages;

	echo '<nav class="ps-pagination">';
	echo paginate_links(
		array(
			'base'      => get_pagenum_link( 1 ) . '%_%',
			'format'    => 'page/%#%/',
			'current'   => $current_page,
			'total'     => $total_pages,
			'prev_text' => esc_html__( '← Previous', 'peptide-starter' ),
			'next_text' => esc_html__( 'Next →', 'peptide-starter' ),
			'type'      => 'plain',
		)
	);
	echo '</nav>';
}

/**
 * Get the excerpt
 */
function peptide_starter_get_excerpt( $post_id = 0, $length = 20 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$excerpt = get_the_excerpt( $post_id );

	if ( ! $excerpt ) {
		$excerpt = wp_trim_words( get_the_content( $post_id ), $length );
	}

	return $excerpt;
}

/**
 * Check if we should show footer newsletter signup
 */
function peptide_starter_show_newsletter_form() {
	return apply_filters( 'peptide_starter_show_newsletter', true );
}

/**
 * Handle newsletter signup form submission.
 *
 * Validates the nonce and email, then fires an action hook so plugins
 * can handle the actual subscription logic (e.g. Mailchimp, SendGrid).
 * If no plugin handles it, redirects back with a generic success message.
 *
 * @see footer.php — renders the newsletter form
 */
function peptide_starter_handle_newsletter_signup() {
	// Verify nonce — prevents CSRF attacks.
	if ( ! isset( $_POST['ps_newsletter_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_newsletter_nonce'] ) ), 'peptide_starter_newsletter' ) ) {
		wp_die(
			esc_html__( 'Security check failed. Please try again.', 'peptide-starter' ),
			esc_html__( 'Error', 'peptide-starter' ),
			array( 'response' => 403 )
		);
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'invalid', wp_get_referer() ) );
		exit;
	}

	/**
	 * Fires when a valid newsletter signup is submitted.
	 *
	 * Plugins should hook here to send the email to their mailing list provider.
	 *
	 * @param string $email The validated subscriber email address.
	 */
	do_action( 'peptide_starter_newsletter_subscribe', $email );

	wp_safe_redirect( add_query_arg( 'ps_newsletter', 'success', wp_get_referer() ) );
	exit;
}
add_action( 'admin_post_peptide_starter_newsletter_signup', 'peptide_starter_handle_newsletter_signup' );
add_action( 'admin_post_nopriv_peptide_starter_newsletter_signup', 'peptide_starter_handle_newsletter_signup' );

/**
 * Enqueue inline script for dark mode toggle (output in footer)
 */
function peptide_starter_inline_dark_mode_script() {
	$default_dark = get_theme_mod( 'dark_mode_default', false ) ? 'dark' : 'light';
	?>
	<script>
	(function() {
		const html = document.documentElement;
		const stored = localStorage.getItem('peptide-starter-theme');
		const default_theme = <?php echo wp_json_encode( $default_dark ); ?>;
		const system_dark = window.matchMedia('(prefers-color-scheme: dark)').matches;

		let theme = stored || (system_dark ? 'dark' : default_theme);
		html.setAttribute('data-theme', theme);
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'peptide_starter_inline_dark_mode_script' );

/**
 * Debug helper: check if a plugin is active
 */
function peptide_starter_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
}
