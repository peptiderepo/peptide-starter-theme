<?php
/**
 * The single peptide template
 *
 * Displays a single peptide with back link, content box,
 * and meta information in a card grid layout.
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
					<a href="<?php echo esc_url( get_post_type_archive_link( 'peptide' ) ); ?>" class="ps-back-link">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M13 8H3M3 8L7 4M3 8L7 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php esc_html_e( 'Back to Peptides', 'peptide-starter' ); ?>
					</a>

					<?php
					// Show featured badge if the peptide is marked as featured
					if ( is_sticky() || get_post_meta( get_the_ID(), 'peptide_featured', true ) ) {
						?>
						<span class="ps-featured-badge">&#9733; <?php esc_html_e( 'Featured', 'peptide-starter' ); ?></span>
						<?php
					}
					?>

					<h1 class="page-title"><?php the_title(); ?></h1>

					<?php
					// Category tag pills
					$categories = get_the_terms( get_the_ID(), 'peptide-category' );
					if ( $categories && ! is_wp_error( $categories ) ) {
						echo '<div class="peptide-card__meta" style="margin-top: var(--spacing-sm);">';
						foreach ( $categories as $cat ) {
							echo '<span class="peptide-card__badge">' . esc_html( $cat->name ) . '</span>';
						}
						echo '</div>';
					}
					?>
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

					<!-- Post Content in a styled box -->
					<div class="peptide-content-box">
						<div class="peptide-content">
							<?php the_content(); ?>
						</div>
					</div>

					<!-- Meta Information as card grid -->
					<?php
					$formula          = get_post_meta( get_the_ID(), 'peptide_formula', true );
					$sequence         = get_post_meta( get_the_ID(), 'peptide_sequence', true );
					$molecular_weight = get_post_meta( get_the_ID(), 'peptide_molecular_weight', true );
					$therapeutic_use  = get_post_meta( get_the_ID(), 'peptide_therapeutic_use', true );

					if ( $formula || $sequence || $molecular_weight || $therapeutic_use ) {
						?>
						<h2 class="ps-section-heading">
							<span class="ps-section-icon" style="background: var(--color-accent-sky);">&#128300;</span>
							<?php esc_html_e( 'Properties', 'peptide-starter' ); ?>
						</h2>
						<div class="peptide-meta-grid">
							<?php if ( $formula ) { ?>
								<div class="peptide-meta-card">
									<dl>
										<dt><?php esc_html_e( 'Molecular Formula', 'peptide-starter' ); ?></dt>
										<dd><code><?php echo esc_html( $formula ); ?></code></dd>
									</dl>
								</div>
							<?php } ?>

							<?php if ( $sequence ) { ?>
								<div class="peptide-meta-card">
									<dl>
										<dt><?php esc_html_e( 'Sequence', 'peptide-starter' ); ?></dt>
										<dd><code><?php echo esc_html( $sequence ); ?></code></dd>
									</dl>
								</div>
							<?php } ?>

							<?php if ( $molecular_weight ) { ?>
								<div class="peptide-meta-card">
									<dl>
										<dt><?php esc_html_e( 'Molecular Weight', 'peptide-starter' ); ?></dt>
										<dd><?php echo esc_html( $molecular_weight ); ?> Da</dd>
									</dl>
								</div>
							<?php } ?>

							<?php if ( $therapeutic_use ) { ?>
								<div class="peptide-meta-card">
									<dl>
										<dt><?php esc_html_e( 'Therapeutic Use', 'peptide-starter' ); ?></dt>
										<dd><?php echo esc_html( $therapeutic_use ); ?></dd>
									</dl>
								</div>
							<?php } ?>
						</div>
						<?php
					}
					?>

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

				<!-- Newsletter Signup — compact inline form below monograph content -->
				<div class="peptide-newsletter-cta" style="margin-top: var(--spacing-3xl); padding: var(--spacing-2xl); background: var(--color-surface-subtle, #f8f8f6); border-radius: 8px; border: 1px solid var(--color-border-default);">
					<p style="margin: 0 0 var(--spacing-md); font-size: 0.95rem; color: var(--color-text-secondary);">
						<?php esc_html_e( 'Want updates on monographs like this? One email a week, no spam.', 'peptide-starter' ); ?>
					</p>
					<div class="beehiiv-embed-wrap" style="max-width: 100%; overflow: hidden;">
						<script async src="https://subscribe-forms.beehiiv.com/embed.js"></script>
						<iframe
							src="https://subscribe-forms.beehiiv.com/6edcd482-cbea-4767-8777-97ac17d6d609"
							class="beehiiv-embed"
							data-test-id="beehiiv-embed"
							frameborder="0"
							scrolling="no"
							style="width: 100%; max-width: 100%; height: 180px; margin: 0; border-radius: 4px; background-color: transparent; box-shadow: 0 0 #0000;"
							title="<?php esc_attr_e( 'Subscribe to Repo Weekly', 'peptide-starter' ); ?>"
						> </iframe>
					</div>
				</div>

				<!-- Navigation -->
				<nav class="post-navigation" style="margin-top: var(--spacing-3xl); padding-top: var(--spacing-2xl); border-top: 1px solid var(--color-border-default);">
					<div style="display: flex; justify-content: space-between; gap: var(--spacing-lg);">
						<div>
							<?php previous_post_link( '%link', '&larr; %title' ); ?>
						</div>
						<div>
							<?php next_post_link( '%link', '%title &rarr;' ); ?>
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
