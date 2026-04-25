<?php
/**
 * Template Name: How We Review Peptides
 *
 * Two-column layout with sticky sidebar ToC for the verdict system explainer.
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="page-how-we-review">
		<div class="page-how-we-review__content">
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
			<aside class="page-how-we-review__sidebar">
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
