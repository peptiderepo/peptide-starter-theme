<?php
/**
 * Template Name: About & Editorial Standards
 *
 * Editorial standards, disclosure, and about page template.
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="page-about">
		<?php
		while ( have_posts() ) {
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-about' ); ?>>
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

		<!-- Affiliate Disclosure Block -->
		<section class="page-about__disclosures">
			<h2><?php esc_html_e( 'How We Make Money', 'peptide-starter' ); ?></h2>
			<?php
			get_template_part(
				'template-parts/affiliate-disclosure',
				null,
				array( 'context' => 'banner' )
			);
			?>
		</section>
	</div>
</main>

<?php get_footer();
