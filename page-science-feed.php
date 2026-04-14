<?php
/**
 * Template Name: Science Feed
 *
 * Page template for the science news feed.
 * Loads the Peptide News plugin feed via shortcode, plus newsletter signup.
 *
 * @see front-page.php — also renders [peptide_news] in a different context
 * @see template-parts/newsletter-signup.php — included at bottom
 *
 * What: Renders the science feed page with news shortcode + newsletter CTA.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: Peptide News plugin (optional — graceful fallback).
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
			if ( shortcode_exists( 'peptide_news' ) ) {
				?>
				<div class="ps-science-feed-header">
					<h1><?php esc_html_e( 'Science Feed', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'Latest research publications, clinical studies, and laboratory insights.', 'peptide-starter' ); ?></p>
				</div>
				<?php
				echo do_shortcode( '[peptide_news]' );
			} else {
				?>
				<div class="ps-tool-fallback">
					<h1><?php esc_html_e( 'Science Feed', 'peptide-starter' ); ?></h1>
					<p><?php esc_html_e( 'The science feed is currently being set up. Please check back soon.', 'peptide-starter' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ps-btn ps-btn-primary">
						<?php esc_html_e( 'Return Home', 'peptide-starter' ); ?>
					</a>
				</div>
				<?php
			}
			?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/newsletter', 'signup' ); ?>
</main>

<?php get_footer(); ?>
