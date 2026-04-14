<?php
/**
 * Template Name: Peptide Directory
 *
 * Page template for the peptide directory / browsable database.
 * Loads the Peptide Community plugin directory via shortcode.
 *
 * @see archive-peptide.php — CPT archive (different from this directory)
 * @see functions.php — enqueue and page auto-creation
 *
 * What: Renders the peptide directory page with plugin shortcode integration.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: Peptide Community plugin (optional — graceful fallback).
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
			if ( shortcode_exists( 'peptide_directory' ) ) {
				echo do_shortcode( '[peptide_directory]' );
			} else {
				?>
				<div class="ps-tool-fallback">
					<h1><?php esc_html_e( 'Peptide Directory', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'The peptide directory is currently being set up. Please check back soon.', 'peptide-starter' ); ?></p>
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
