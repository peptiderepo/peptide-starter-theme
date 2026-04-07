<?php
/**
 * The single peptide template
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<!-- Page Header -->
			<header class="page-header">
				<div class="ps-container">
					<h1 class="page-title"><?php the_title(); ?></h1>
				</div>
			</header>

			<!-- Single Peptide Content -->
			<div class="page-content">
				<article class="peptide-single" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<!-- Featured Image -->
					<?php if ( has_post_thumbnail() ) { ?>
						<div class="peptide-featured-image" style="margin-bottom: var(--spacing-2xl);">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					<?php } ?>

					<!-- Post Content -->
					<div class="peptide-content">
						<?php the_content(); ?>
					</div>

					<!-- Meta Information -->
					<div class="peptide-meta" style="margin-top: var(--spacing-3xl); padding-top: var(--spacing-2xl); border-top: 1px solid var(--color-border-default);">
						<dl>
							<?php if ( $formula = get_post_meta( get_the_ID(), 'peptide_formula', true ) ) { ?>
								<dt><strong><?php esc_html_e( 'Molecular Formula:', 'peptide-starter' ); ?></strong></dt>
								<dd><code><?php echo esc_html( $formula ); ?></code></dd>
							<?php } ?>

							<?php if ( $sequence = get_post_meta( get_the_ID(), 'peptide_sequence', true ) ) { ?>
								<dt><strong><?php esc_html_e( 'Sequence:', 'peptide-starter' ); ?></strong></dt>
								<dd><code><?php echo esc_html( $sequence ); ?></code></dd>
							<?php } ?>

							<?php if ( $molecular_weight = get_post_meta( get_the_ID(), 'peptide_molecular_weight', true ) ) { ?>
								<dt><strong><?php esc_html_e( 'Molecular Weight:', 'peptide-starter' ); ?></strong></dt>
								<dd><?php echo esc_html( $molecular_weight ); ?> Da</dd>
							<?php } ?>

							<?php if ( $therapeutic_use = get_post_meta( get_the_ID(), 'peptide_therapeutic_use', true ) ) { ?>
								<dt><strong><?php esc_html_e( 'Therapeutic Use:', 'peptide-starter' ); ?></strong></dt>
								<dd><?php echo esc_html( $therapeutic_use ); ?></dd>
							<?php } ?>
						</dl>
					</div>

					<!-- Edit Link -->
					<?php
					edit_post_link(
						sprintf(
							wp_kses(
								__( 'Edit <span class="screen-reader-text">%s</span>', 'peptide-starter' ),
								array( 'span' => array( 'class' => array() ) )
							),
							get_the_title()
						),
						'<div class="edit-link" style="margin-top: var(--spacing-2xl);">',
						'</div>'
					);
					?>
				</article>

				<!-- Navigation -->
				<nav class="post-navigation" style="margin-top: var(--spacing-3xl); padding-top: var(--spacing-2xl); border-top: 1px solid var(--color-border-default);">
					<div style="display: flex; justify-content: space-between; gap: var(--spacing-lg);">
						<div>
							<?php previous_post_link( '%link', '← %title' ); ?>
						</div>
						<div>
							<?php next_post_link( '%link', '%title →' ); ?>
						</div>
					</div>
				</nav>
			</div>
			<?php
		}
	} else {
		get_template_part( 'template-parts/content', 'none' );
	}
	?>
</main>

<?php get_footer(); ?>
