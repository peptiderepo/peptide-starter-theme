<?php
/**
 * Template Name: Protocol Builder
 *
 * Page template for the interactive protocol builder wizard.
 * Loads the Peptide Tools plugin protocol builder via shortcode.
 *
 * @see page-calculator.php — sibling tool template
 * @see functions.php — enqueue and page auto-creation
 *
 * What: Renders the protocol builder page with plugin shortcode integration.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: Peptide Tools plugin (optional — graceful fallback).
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
			if ( shortcode_exists( 'peptide_tools_protocol_builder' ) ) {
				echo do_shortcode( '[peptide_tools_protocol_builder]' );
			} else {
				?>
				<div class="ps-tool-fallback">
					<h1><?php esc_html_e( 'Protocol Builder', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'The protocol builder tool is currently being set up. Please check back soon.', 'peptide-starter' ); ?></p>
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
