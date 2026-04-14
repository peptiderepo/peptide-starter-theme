<?php
/**
 * Peptide Starter Theme Functions
 *
 * Core theme setup: supports, menus, scripts, customizer, helpers.
 * Feature-specific logic is split into inc/ files to keep this under 300 lines.
 *
 * @see inc/auth-handlers.php — AJAX login/register handlers
 * @see inc/contact-handler.php — AJAX contact form handler
 * @see inc/page-setup.php — auto-create pages on theme activation
 * @see inc/newsletter-admin.php — admin page for subscriber export
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
define( 'PEPTIDE_STARTER_VERSION', '1.5.0' );
define( 'PEPTIDE_STARTER_DIR', get_template_directory() );
define( 'PEPTIDE_STARTER_URI', get_template_directory_uri() );

// Load feature modules.
require_once PEPTIDE_STARTER_DIR . '/inc/helpers.php';
require_once PEPTIDE_STARTER_DIR . '/inc/customizer.php';
require_once PEPTIDE_STARTER_DIR . '/inc/auth-handlers.php';
require_once PEPTIDE_STARTER_DIR . '/inc/contact-handler.php';
require_once PEPTIDE_STARTER_DIR . '/inc/page-setup.php';
require_once PEPTIDE_STARTER_DIR . '/inc/newsletter-admin.php';

/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * @return void
 */
function peptide_starter_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'peptide-starter' ),
			'footer'  => esc_html__( 'Footer Menu', 'peptide-starter' ),
		)
	);

	add_theme_support( 'align-wide' );
	load_theme_textdomain( 'peptide-starter', PEPTIDE_STARTER_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'peptide_starter_setup' );

/**
 * Register and enqueue stylesheets and scripts.
 *
 * @return void
 */
function peptide_starter_scripts() {
	wp_enqueue_style( 'peptide-starter-style', PEPTIDE_STARTER_URI . '/style.css', array(), PEPTIDE_STARTER_VERSION );

	wp_enqueue_script( 'peptide-starter-navigation', PEPTIDE_STARTER_URI . '/assets/js/navigation.js', array(), PEPTIDE_STARTER_VERSION, true );
	wp_enqueue_script( 'peptide-starter-theme', PEPTIDE_STARTER_URI . '/assets/js/theme.js', array(), PEPTIDE_STARTER_VERSION, true );
	wp_enqueue_script( 'peptide-starter-settings-panel', PEPTIDE_STARTER_URI . '/assets/js/settings-panel.js', array(), PEPTIDE_STARTER_VERSION, true );

	wp_localize_script(
		'peptide-starter-theme',
		'peptideStarterData',
		array(
			'siteUrl'    => home_url( '/' ),
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'isDarkMode' => get_theme_mod( 'dark_mode_default', false ),
		)
	);

	// Conditional scripts for specific page templates.
	if ( is_page_template( 'page-documentation.php' ) ) {
		wp_enqueue_script( 'peptide-starter-docs', PEPTIDE_STARTER_URI . '/assets/js/documentation.js', array(), PEPTIDE_STARTER_VERSION, true );
	}

	if ( is_page_template( 'page-auth.php' ) ) {
		wp_enqueue_script( 'peptide-starter-auth', PEPTIDE_STARTER_URI . '/assets/js/auth.js', array(), PEPTIDE_STARTER_VERSION, true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'peptide_starter_scripts' );

/**
 * Enqueue plugin override styles at late priority.
 *
 * @return void
 */
function peptide_starter_plugin_overrides() {
	wp_add_inline_style(
		'peptide-starter-style',
		'
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
 * Register widget areas.
 *
 * @return void
 */
function peptide_starter_widgets_init() {
	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar(
			array(
				/* translators: %d: footer column number */
				'name'          => sprintf( esc_html__( 'Footer - Column %d', 'peptide-starter' ), $i ),
				'id'            => 'footer-' . $i,
				/* translators: %d: footer column number */
				'description'   => sprintf( esc_html__( 'Footer widget area %d', 'peptide-starter' ), $i ),
				'before_widget' => '<div id="%1$s" class="widget %2$s footer-widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}
}
add_action( 'widgets_init', 'peptide_starter_widgets_init' );

/**
 * Handle newsletter signup form submission.
 * Stores email in wp_options with duplicate check.
 *
 * @return void Redirects back with status parameter.
 */
function peptide_starter_handle_newsletter_signup() {
	if ( ! isset( $_POST['ps_newsletter_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_newsletter_nonce'] ) ), 'peptide_starter_newsletter' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'peptide-starter' ), esc_html__( 'Error', 'peptide-starter' ), array( 'response' => 403 ) );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'invalid', wp_get_referer() ) );
		exit;
	}

	// Store in wp_options with duplicate check.
	$emails = get_option( 'ps_newsletter_emails', array() );
	$existing_emails = array_column( $emails, 'email' );

	if ( in_array( $email, $existing_emails, true ) ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'duplicate', wp_get_referer() ) );
		exit;
	}

	$emails[] = array(
		'email' => $email,
		'date'  => current_time( 'Y-m-d H:i:s' ),
	);
	update_option( 'ps_newsletter_emails', $emails );

	do_action( 'peptide_starter_newsletter_subscribe', $email );

	wp_safe_redirect( add_query_arg( 'ps_newsletter', 'success', wp_get_referer() ) );
	exit;
}
add_action( 'admin_post_peptide_starter_newsletter_signup', 'peptide_starter_handle_newsletter_signup' );
add_action( 'admin_post_nopriv_peptide_starter_newsletter_signup', 'peptide_starter_handle_newsletter_signup' );

/**
 * Inline dark mode script in wp_head — prevents flash of wrong theme.
 *
 * @return void
 */
function peptide_starter_inline_dark_mode_script() {
	$default_dark = get_theme_mod( 'dark_mode_default', false ) ? 'dark' : 'light';
	?>
	<script>
	(function() {
		var html = document.documentElement;
		var stored = localStorage.getItem('peptide-starter-theme');
		var defaultTheme = <?php echo wp_json_encode( $default_dark ); ?>;
		var systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		var theme = stored || (systemDark ? 'dark' : defaultTheme);
		html.setAttribute('data-theme', theme);
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'peptide_starter_inline_dark_mode_script' );
