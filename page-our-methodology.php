<?php
/**
 * Template Name: Our Methodology
 *
 * Two-column layout with sticky sidebar ToC for the editorial methodology page.
 * Mirrors page-how-we-review.php layout; CSS classes use page-our-methodology prefix.
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="page-our-methodology">
		<div class="page-our-methodology__content">
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

		<?php if ( ! empty( get_the_content() ) ) : ?>
			<aside class="page-our-methodology__sidebar">
				<nav class="page-toc" aria-label="<?php esc_attr_e( 'Page contents', 'peptide-starter' ); ?>">
					<h3><?php esc_html_e( 'Contents', 'peptide-starter' ); ?></h3>
					<!-- JavaScript-generated ToC will be inserted here -->
				</nav>
			</aside>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
