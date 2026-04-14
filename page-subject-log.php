<?php
/**
 * Template Name: Subject Log
 *
 * Longitudinal subject-tracking log. Requires authenticated + email-verified
 * users — contains user-owned PII (lab / assay results).
 *
 * @see inc/helpers.php — peptide_starter_require_login()
 * @see page-tracker.php — related tracker template
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Auth gate — redirects unauthenticated users to /auth with return path,
// and unverified users to /profile with verify-required banner.
peptide_starter_require_login();

get_header();
?>

<main id="main" class="site-main">
	<section class="ps-tool-page">
		<div class="ps-container">
			<?php
			if ( shortcode_exists( 'peptide_tracker_subject_log' ) ) {
				echo do_shortcode( '[peptide_tracker_subject_log]' );
			} else {
				?>
				<div class="ps-tool-fallback">
					<h1><?php esc_html_e( 'Subject Log', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'The subject log tool is currently being set up. Please check back soon.', 'peptide-starter' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ps-btn ps-btn-primary">
						<?php esc_html_e( 'Return Home', 'peptide-starter' ); ?>
					</a>
				</div>
				<?php
			}
			?>
		</div>
	</section>
</main>

<?php get_footer(); ?>
