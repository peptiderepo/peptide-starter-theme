<?php
/**
 * Template Name: Subject Log
 *
 * Page template for the longitudinal subject tracking log.
 * Loads the Peptide Tracker plugin subject log via shortcode.
 *
 * @see page-tracker.php — related tracker template
 * @see functions.php — enqueue and page auto-creation
 *
 * What: Renders the subject log page with plugin shortcode integration.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: Peptide Tracker plugin (optional — graceful fallback).
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
