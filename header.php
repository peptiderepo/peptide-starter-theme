<?php
/**
 * The header for Peptide Starter theme
 *
 * Updated navigation: Home | Tools ▾ | My Data ▾ | Resources ▾ | [Sign In] ⚙ 🌐
 * Sign In / User menu is state-aware (logged in vs logged out).
 *
 * @see functions.php — nav menu registration, walker
 * @see assets/js/navigation.js — mobile menu toggle
 * @see assets/js/settings-panel.js — settings icon trigger
 * @see template-parts/settings-panel.php — settings slide-out
 *
 * @package peptide-starter
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
	<link rel="icon" type="image/x-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/brand/favicon-16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/brand/favicon-32.png">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/apple-touch-icon.png">
	<link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/site.webmanifest">
	<meta name="theme-color" content="#1B8A92">
	<?php if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) : ?>
		<meta property="og:image" content="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/brand/og-default.png' ); ?>">
	<?php endif; ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a href="#main" class="skip-link"><?php esc_html_e( 'Skip to main content', 'peptide-starter' ); ?></a>

<header class="site-header">
	<div class="site-header-wrapper">
		<!-- Logo -->
		<div class="site-logo-wrapper">
			<?php peptide_starter_the_custom_logo(); ?>
		</div>

		<!-- Primary Navigation -->
		<nav class="primary-navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'fallback_cb'     => 'peptide_starter_primary_menu_fallback',
					'walker'          => new Peptide_Starter_Nav_Walker(),
					'depth'           => 2,
					'container'       => false,
					'items_wrap'      => '<ul>%3$s</ul>',
				)
			);
			?>
		</nav>

		<!-- Navigation Overlay (for mobile) -->
		<div class="nav-overlay"></div>

		<!-- Header Icons -->
		<div class="header-icons">
			<!-- Sign In / User Menu (state-aware) -->
			<?php if ( is_user_logged_in() ) : ?>
				<?php $current_user = wp_get_current_user(); ?>
				<div class="ps-user-menu">
					<button class="ps-user-menu__toggle" aria-expanded="false" aria-haspopup="true">
						<span class="ps-user-menu__name"><?php echo esc_html( $current_user->display_name ); ?></span>
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
							<path d="M3 5L6 8L9 5"/>
						</svg>
					</button>
					<div class="ps-user-menu__dropdown" aria-label="<?php esc_attr_e( 'User menu', 'peptide-starter' ); ?>">
						<a href="<?php echo esc_url( home_url( '/profile' ) ); ?>"><?php esc_html_e( 'My Profile', 'peptide-starter' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/tracker' ) ); ?>"><?php esc_html_e( 'Tracker', 'peptide-starter' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/subject-log' ) ); ?>"><?php esc_html_e( 'Subject Log', 'peptide-starter' ); ?></a>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sign Out', 'peptide-starter' ); ?></a>
					</div>
				</div>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/auth' ) ); ?>" class="ps-btn ps-btn-primary ps-btn-sm ps-header-cta">
					<?php esc_html_e( 'Sign In', 'peptide-starter' ); ?>
				</a>
			<?php endif; ?>

			<!-- Settings Icon -->
			<button class="header-icon-btn" id="ps-settings-toggle" aria-label="<?php esc_attr_e( 'Support & settings', 'peptide-starter' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="10" cy="10" r="3"/>
					<path d="M16.474 12.32a1.26 1.26 0 00.252 1.39l.046.046a1.527 1.527 0 11-2.16 2.16l-.046-.046a1.27 1.27 0 00-2.15.9v.13a1.527 1.527 0 11-3.055 0v-.07a1.26 1.26 0 00-.825-1.152 1.26 1.26 0 00-1.39.252l-.046.046a1.527 1.527 0 11-2.16-2.16l.046-.046a1.27 1.27 0 00-.9-2.15h-.13a1.527 1.527 0 110-3.055h.07a1.26 1.26 0 001.152-.825 1.26 1.26 0 00-.252-1.39l-.046-.046a1.527 1.527 0 112.16-2.16l.046.046a1.26 1.26 0 001.39.252h.06a1.26 1.26 0 00.764-1.152v-.13a1.527 1.527 0 113.055 0v.07a1.27 1.27 0 002.15.9l.046-.046a1.527 1.527 0 112.16 2.16l-.046.046a1.26 1.26 0 00-.252 1.39v.06a1.26 1.26 0 001.152.764h.13a1.527 1.527 0 110 3.055h-.07a1.26 1.26 0 00-1.152.825z"/>
				</svg>
			</button>

			<!-- Language Placeholder -->
			<span class="ps-lang-placeholder" aria-label="<?php esc_attr_e( 'Language', 'peptide-starter' ); ?>" title="<?php esc_attr_e( 'Language selection coming soon', 'peptide-starter' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="10" cy="10" r="8"/>
					<line x1="2" y1="10" x2="18" y2="10"/>
					<path d="M10 2a14.5 14.5 0 014 8 14.5 14.5 0 01-4 8 14.5 14.5 0 01-4-8 14.5 14.5 0 014-8z"/>
				</svg>
				<span class="ps-lang-code">EN</span>
			</span>

			<!-- Search Icon -->
			<button class="header-icon-btn search-toggle" aria-label="<?php esc_attr_e( 'Search', 'peptide-starter' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<!-- Dark Mode Toggle -->
			<!-- Dark mode disabled for v1: verdict-color accessibility only validated for light mode. Re-enable in v1.1 after dark palette audit. -->
			<button class="header-icon-btn dark-mode-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'peptide-starter' ); ?>" style="display:none;" aria-hidden="true">
				<svg class="sun-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
					<circle cx="10" cy="10" r="4" stroke="currentColor" stroke-width="2"/>
					<path d="M10 1V3M10 17V19M19 10H17M3 10H1M16.657 16.657L15.243 15.243M4.757 4.757L3.343 3.343M16.657 3.343L15.243 4.757M4.757 15.243L3.343 16.657" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<svg class="moon-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M17.293 13.293A7.002 7.002 0 101.725 2.725a7 7 0 0115.568 10.568z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<!-- Mobile Menu Toggle -->
			<button class="menu-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'peptide-starter' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3 6H21M3 12H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
	</div>
</header>

<!-- Search Overlay (Peptide Search AI) -->
<div class="search-overlay" id="search-overlay" aria-hidden="true">
	<div class="search-overlay-inner">
		<button class="search-overlay-close" aria-label="<?php esc_attr_e( 'Close search', 'peptide-starter' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<div class="search-overlay-content">
			<?php
			if ( shortcode_exists( 'peptide_search' ) ) {
				echo do_shortcode( '[peptide_search placeholder="Search for a peptide (e.g., BPC-157, Thymosin Beta-4)..."]' );
			} else {
				get_search_form();
			}
			?>
		</div>
	</div>
</div>

<?php
// Nav fallback lives in inc/helpers.php as of v1.5.1 to prevent redeclaration
// if header.php is ever included more than once in a request.
?>
