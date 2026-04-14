<?php
/**
 * Template Name: Calculator
 *
 * Page template for the reconstitution calculator tool.
 * Loads the Peptide Tools plugin calculator via shortcode.
 *
 * @see page-protocol-builder.php — sibling tool template
 * @see functions.php — enqueue and page auto-creation
 *
 * What: Renders the calculator page with plugin shortcode integration.
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
			if ( shortcode_exists( 'peptide_tools_calculator' ) ) {
				echo do_shortcode( '[peptide_tools_calculator]' );
			} else {
				?>
				<div class="ps-tool-fallback">
					<h1><?php esc_html_e( 'Reconstitution Calculator', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'The calculator tool is currently being set up. Please check back soon.', 'peptide-starter' ); ?></p>
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
