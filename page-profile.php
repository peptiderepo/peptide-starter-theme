<?php
/**
 * Template Name: User Profile
 *
 * Page template for the user profile page.
 * Loads the Peptide Community plugin profile via shortcode.
 *
 * @see page-auth.php — authentication template
 * @see functions.php — enqueue and page auto-creation
 *
 * What: Renders the user profile page with plugin shortcode integration.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: Peptide Community plugin (optional — graceful fallback).
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect unauthenticated users to the auth page.
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/auth?redirect_to=' . rawurlencode( get_permalink() ) ) );
	exit;
}

get_header();
?>

<main id="main" class="site-main">
	<section class="ps-tool-page">
		<div class="ps-container">
			<?php
			if ( shortcode_exists( 'peptide_community_profile' ) ) {
				echo do_shortcode( '[peptide_community_profile]' );
			} else {
				$current_user = wp_get_current_user();
				?>
				<div class="ps-profile-basic">
					<h1><?php echo esc_html( sprintf( __( 'Welcome, %s', 'peptide-starter' ), $current_user->display_name ) ); ?></h1>
					<p><?php esc_html_e( 'Your profile page is being set up. Check back soon for full profile features.', 'peptide-starter' ); ?></p>
					<div class="ps-profile-links">
						<a href="<?php echo esc_url( home_url( '/tracker' ) ); ?>" class="ps-btn ps-btn-secondary">
							<?php esc_html_e( 'Tracker', 'peptide-starter' ); ?>
						</a>
						<a href="<?php echo esc_url( home_url( '/subject-log' ) ); ?>" class="ps-btn ps-btn-secondary">
							<?php esc_html_e( 'Subject Log', 'peptide-starter' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ps-btn ps-btn-tertiary">
							<?php esc_html_e( 'Sign Out', 'peptide-starter' ); ?>
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</section>
</main>

<?php get_footer(); ?>
