<?php
/**
 * The template for displaying peptide archives
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<!-- Archive Header -->
	<header class="archive-header">
		<div class="ps-container">
			<h1 class="archive-title">
				<?php
				if ( is_tax( 'peptide-category' ) ) {
					esc_html_e( 'Peptide Category', 'peptide-starter' );
					echo ': ' . esc_html( single_term_title( '', false ) );
				} elseif ( is_post_type_archive( 'peptide' ) ) {
					esc_html_e( 'Peptide Database', 'peptide-starter' );
				} else {
					esc_html_e( 'Peptides', 'peptide-starter' );
				}
				?>
			</h1>
		</div>
	</header>

	<!-- Archive Content -->
	<div class="archive-content">
		<!-- Search/Filter Bar -->
		<?php if ( class_exists( 'WP_Widget_Search' ) ) { ?>
			<div class="archive-filters">
				<div class="archive-search">
					<?php get_search_form(); ?>
				</div>
			</div>
		<?php } ?>

		<!-- Peptides Grid -->
		<?php
		if ( have_posts() ) {
			?>
			<div class="peptides-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'peptide' );
				}
				?>
			</div>

			<!-- Pagination -->
			<?php
			peptide_starter_pagination();
		} else {
			// No peptides found
			?>
			<div class="archive-empty">
				<h2><?php esc_html_e( 'No Peptides Found', 'peptide-starter' ); ?></h2>
				<p><?php esc_html_e( 'There are no peptides matching your search. Try a different search term or browse the full database.', 'peptide-starter' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>" class="ps-btn ps-btn-primary">
					<?php esc_html_e( 'View All Peptides', 'peptide-starter' ); ?>
				</a>
			</div>
			<?php
		}
		?>
	</div>
</main>

<?php
get_footer();
