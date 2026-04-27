<?php
/**
 * Peptide Starter Theme Functions
 *
 * Core theme setup: supports, menus, scripts, customizer, security modules.
 * Feature-specific logic is split across inc/ files to keep this under
 * 300 lines and to make responsibilities discoverable.
 *
 * @see inc/config.php           — security configuration
 * @see inc/rate-limiter.php     — transient-backed rate limiter
 * @see inc/email-verification.php — token-gated email verification
 * @see inc/auth-handlers.php    — AJAX login + registration
 * @see inc/contact-handler.php  — AJAX contact form
 * @see inc/newsletter-admin.php — admin subscriber viewer + unsubscribe
 * @see inc/page-setup.php       — auto-create pages + v1.5.0 user migration
 * @see inc/mail-diagnostic.php  — admin deliverability test tool
 * @see inc/helpers.php          — nav walker, menu fallback, gates, utilities
 * @see inc/perf-asset-policy.php — asset dequeue + font slim + preconnect + defer
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Theme constants.
define( 'PEPTIDE_STARTER_VERSION', '1.6.0' );
define( 'PEPTIDE_STARTER_DIR', get_template_directory() );
define( 'PEPTIDE_STARTER_URI', get_template_directory_uri() );

// Load feature modules. Order matters — config must load before anything
// that calls peptide_starter_config_*; rate limiter before handlers.
require_once PEPTIDE_STARTER_DIR . '/inc/config.php';
require_once PEPTIDE_STARTER_DIR . '/inc/helpers.php';
require_once PEPTIDE_STARTER_DIR . '/inc/cloudflare-ips.php';
require_once PEPTIDE_STARTER_DIR . '/inc/rate-limiter.php';
require_once PEPTIDE_STARTER_DIR . '/inc/email-verification.php';
require_once PEPTIDE_STARTER_DIR . '/inc/customizer.php';
require_once PEPTIDE_STARTER_DIR . '/inc/auth-handlers.php';
require_once PEPTIDE_STARTER_DIR . '/inc/contact-handler.php';
require_once PEPTIDE_STARTER_DIR . '/inc/page-setup.php';
require_once PEPTIDE_STARTER_DIR . '/inc/newsletter-admin.php';
require_once PEPTIDE_STARTER_DIR . '/inc/mail-diagnostic.php';
require_once PEPTIDE_STARTER_DIR . '/inc/perf-asset-policy.php';

/**
 * Set up theme defaults and register support for WordPress features.
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
add_action( 'after_setup_theme', 'peptide_starter_perf_asset_policy_init' );

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
 * Enqueue Beehiiv attribution.js site-wide.
 *
 * Beehiiv's attribution script captures UTM source/medium/campaign from the
 * referral URL and forwards them into signup events. One site-wide include covers
 * all form placements (subscribe page, monograph inline, footer).
 *
 * @see https://support.beehiiv.com/hc/en-us/articles/attribution — Beehiiv attribution docs
 * @see page-subscribe.php — /subscribe/ landing page
 * @see single-peptide.php — compact inline form on monograph pages
 * @see footer.php — footer signup CTA
 *
 * @return void
 */
function peptide_starter_enqueue_beehiiv_attribution() {
	wp_enqueue_script(
		'beehiiv-attribution',
		'https://subscribe-forms.beehiiv.com/attribution.js',
		array(),
		null, // Beehiiv controls versioning via their CDN.
		true  // Load in footer.
	);
}
add_action( 'wp_enqueue_scripts', 'peptide_starter_enqueue_beehiiv_attribution' );

/**
 * Register footer widget areas.
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
 * Handle newsletter signup.
 *
 * Applies: nonce, honeypot, rate limit, consent, email validity. Stores
 * entries with an unsubscribe token; collapses success + duplicate into
 * a single "ok" state server-side so the response doesn't leak whether
 * the address was already subscribed.
 *
 * Side effects: writes a wp_options array with autoload=false.
 *
 * @return void
 */
function peptide_starter_handle_newsletter_signup() {
	if ( ! isset( $_POST['ps_newsletter_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_newsletter_nonce'] ) ), 'peptide_starter_newsletter' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'peptide-starter' ), esc_html__( 'Error', 'peptide-starter' ), array( 'response' => 403 ) );
	}

	// Honeypot: fake-ok redirect.
	if ( peptide_starter_honeypot_triggered( 'ps_hp_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'ok', peptide_starter_safe_referer() ) );
		exit;
	}

	if ( ! Peptide_Starter_Rate_Limiter::check( 'newsletter', 'ip' ) ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'ok', peptide_starter_safe_referer() ) );
		exit;
	}
	Peptide_Starter_Rate_Limiter::record( 'newsletter', 'ip' );

	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$consent = ! empty( $_POST['ps_consent'] );

	if ( ! is_email( $email ) || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'ps_newsletter', 'invalid', peptide_starter_safe_referer() ) );
		exit;
	}

	$emails          = get_option( 'ps_newsletter_emails', array() );
	$existing_emails = array_column( $emails, 'email' );

	// Collapse success + duplicate into a single 'ok' state — no enumeration.
	if ( ! in_array( $email, $existing_emails, true ) ) {
		$emails[] = array(
			'email'       => $email,
			'date'        => current_time( 'Y-m-d H:i:s' ),
			'unsub_token' => wp_generate_password( 32, false, false ),
		);
		// autoload=false so a large subscriber list doesn't bloat every page load.
		update_option( 'ps_newsletter_emails', $emails, false );

		/**
		 * Fires when a new subscriber is added to the newsletter list.
		 *
		 * @param string $email Subscriber email.
		 */
		do_action( 'peptide_starter_newsletter_subscribe', $email );
	}

	wp_safe_redirect( add_query_arg( 'ps_newsletter', 'ok', peptide_starter_safe_referer() ) );
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
