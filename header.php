<?php
/**
 * The header for Peptide Starter theme
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
			<!-- Browse Peptides CTA (accent button, hidden on mobile) -->
			<a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>" class="ps-btn ps-btn-primary ps-btn-sm ps-header-cta">
				<?php esc_html_e( 'Browse Peptides', 'peptide-starter' ); ?>
			</a>

			<!-- Search Icon -->
			<button class="header-icon-btn search-toggle" aria-label="<?php esc_attr_e( 'Search', 'peptide-starter' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<!-- Dark Mode Toggle -->
			<button class="header-icon-btn dark-mode-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'peptide-starter' ); ?>">
				<svg class="sun-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
					<circle cx="10" cy="10" r="4" stroke="currentColor" stroke-width="2"/>
					<path d="M10 1V3M10 17V19M19 10H17M3 10H1M16.657 16.657L15.243 15.243M4.757 4.757L3.343 3.343M16.657 3.343L15.243 4.757M4.757 15.243L3.343 16.657" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<svg class="moon-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M17.293 13.293A7.002 7.002 0 101.725 2.725a7 7 0 0115.568 10.568z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<!-- Cart Icon (WooCommerce) -->
			<?php if ( class_exists( 'WooCommerce' ) ) { ?>
				<button class="header-icon-btn cart-toggle" aria-label="<?php esc_attr_e( 'View cart', 'peptide-starter' ); ?>">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1H3.27924M3.27924 1L5.20081 13.5631C5.35072 14.4571 5.80765 15.2614 6.4543 15.8285C7.10095 16.3956 7.92274 16.6882 8.76721 16.6882H16.7257M16.7257 16.6882C17.3169 16.6882 17.7793 17.1506 17.7793 17.7418C17.7793 18.333 17.3169 18.7955 16.7257 18.7955M16.7257 16.6882C15.6515 16.6882 14.4632 16.6882 8.76721 16.6882M8.76721 16.6882C8.17601 16.6882 7.71365 17.1506 7.71365 17.7418C7.71365 18.333 8.17601 18.7955 8.76721 18.7955M5.20081 13.5631H16.7257" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			<?php } ?>

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
/**
 * Fallback menu if none is set
 */
function peptide_starter_primary_menu_fallback() {
	?>
	<ul>
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'peptide-starter' ); ?></a></li>
		<?php
		$about_page = get_page_by_path( 'about' );
		if ( $about_page ) {
			echo '<li><a href="' . esc_url( get_permalink( $about_page ) ) . '">' . esc_html__( 'About', 'peptide-starter' ) . '</a></li>';
		}
		// Display link to Peptides archive
		if ( post_type_exists( 'peptide' ) ) {
			echo '<li><a href="' . esc_url( get_post_type_archive_link( 'peptide' ) ) . '">' . esc_html__( 'Peptides', 'peptide-starter' ) . '</a></li>';
		} else {
			// Fallback to /peptides/ URL if CPT not registered yet
			echo '<li><a href="' . esc_url( home_url( '/peptides/' ) ) . '">' . esc_html__( 'Peptides', 'peptide-starter' ) . '</a></li>';
		}
		?>
	</ul>
	<?php
}
?>
