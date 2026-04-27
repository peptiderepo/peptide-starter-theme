<?php
/**
 * Template Name: Subscribe
 *
 * Single-column, signup-form-centric layout for the /subscribe/ landing page.
 * Lighter than the methodology / how-we-review templates — no sticky two-column ToC.
 * This is a conversion page, not a reference page.
 *
 * Who calls it: WordPress page loader when page_template meta = page-subscribe.php
 * Dependencies: Beehiiv embed script (loaded inline below); attribution.js loaded
 *               site-wide via peptide_starter_enqueue_beehiiv_attribution() in functions.php
 *
 * @see footer.php — newsletter CTA in footer column 1
 * @see single-peptide.php — compact inline form on monograph pages
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="page-subscribe">
		<div class="page-subscribe__content">
			<?php
			while ( have_posts() ) {
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();
