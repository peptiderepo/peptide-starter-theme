<?php
/**
 * The front page template
 *
 * Layout: Hero → Verdict Explainer → Featured Verdict Cards → Research Modules
 *         → PRAutoBlogger posts → Peptide News feed.
 *
 * Brand v2.0.0: Direction C "The Trusted Guide". Hero uses the 5-state verdict
 * taxonomy as the central explainer hook. Three featured verdict cards pull from
 * real monographs (semaglutide / BPC-157 / Melanotan-II) via post meta.
 *
 * @see template-parts/module-cards.php       — research modules grid
 * @see template-parts/verdict/badge.php      — verdict badge component
 * @see template-parts/verdict/card.php       — full verdict card (monograph hero)
 * @see inc/helpers.php                       — hero title / subtitle getters
 * @see assets/css/brand.css                  — verdict token definitions
 *
 * @package peptide-starter
 */

get_header();

/** @see inc/verdict-helpers.php — peptide_starter_get_verdict_taxonomy() */
$verdict_taxonomy = peptide_starter_get_verdict_taxonomy();

/**
 * Featured monograph post IDs. These are the three exemplar monographs
 * loaded during brand sprint v2.0.0 (2026-04-26).
 * Semaglutide=211 (established), BPC-157=36 (investigational), Melanotan-II=177 (cautionary).
 */
$featured_monograph_ids = array( 211, 36, 177 );
?>

<main id="main" class="site-main">

	<!-- HERO SECTION -->
	<section class="hero-section">
		<div class="hero-content">
			<h1 class="hero-title">
				<?php
				$hero_title = peptide_starter_get_hero_title();
				// Wrap the last word in an accent span for the wavy underline effect.
				$words = explode( ' ', $hero_title );
				if ( count( $words ) > 1 ) {
					$last_word = array_pop( $words );
					echo esc_html( implode( ' ', $words ) ) . ' <span class="ps-hero-accent">' . esc_html( $last_word ) . '</span>';
				} else {
					echo esc_html( $hero_title );
				}
				?>
			</h1>
			<p class="hero-subtitle"><?php echo esc_html( peptide_starter_get_hero_subtitle() ); ?></p>

			<!-- Hero Search (Peptide Search AI) -->
			<div class="hero-search">
				<?php
				if ( shortcode_exists( 'peptide_search' ) ) {
					echo do_shortcode( '[peptide_search placeholder="' . esc_attr( peptide_starter_get_search_placeholder() ) . '"]' );
				} else {
					?>
					<form class="ps-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input
							type="search"
							name="s"
							class="ps-search-input"
							placeholder="<?php echo esc_attr( peptide_starter_get_search_placeholder() ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
						>
					</form>
					<?php
				}
				?>
			</div>

			<!-- Hero CTAs -->
			<div class="hero-ctas">
				<a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>" class="ps-btn ps-btn-primary">
					<?php esc_html_e( 'Browse Peptides', 'peptide-starter' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/how-we-review-peptides' ) ); ?>" class="ps-btn ps-btn-secondary">
					<?php esc_html_e( 'Our method', 'peptide-starter' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- VERDICT TAXONOMY EXPLAINER -->
	<section class="hero-verdict-explainer" aria-label="<?php esc_attr_e( 'Our verdict system', 'peptide-starter' ); ?>">
		<div class="ps-container">
			<p class="hero-verdict-explainer__heading">
				<?php esc_html_e( 'Every peptide gets a verdict. Here\'s what they mean.', 'peptide-starter' ); ?>
			</p>
			<div class="hero-verdict-explainer__grid">
				<?php foreach ( $verdict_taxonomy as $v ) : ?>
				<div class="hero-verdict-explainer__item">
					<span
						class="pr-verdict pr-verdict--<?php echo esc_attr( $v['state'] ); ?>"
						role="img"
						aria-label="<?php echo esc_attr( sprintf( __( 'Verdict: %s', 'peptide-starter' ), $v['label'] ) ); ?>"
					>
						<span class="pr-verdict__glyph" aria-hidden="true"><?php echo esc_html( $v['glyph'] ); ?></span>
						<span class="pr-verdict__label"><?php echo esc_html( $v['label'] ); ?></span>
					</span>
					<p class="hero-verdict-explainer__desc"><?php echo esc_html( $v['description'] ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
			<p class="hero-verdict-explainer__cta-line">
				<a href="<?php echo esc_url( home_url( '/how-we-review-peptides' ) ); ?>" class="ps-btn ps-btn-tertiary ps-btn-sm">
					<?php esc_html_e( 'How we assign verdicts →', 'peptide-starter' ); ?>
				</a>
			</p>
		</div>
	</section>

	<!-- FEATURED VERDICT CARDS: semaglutide=211 (Established), BPC-157=36 (Investigational), Melanotan-II=177 (Cautionary) -->
	<?php
	$featured_posts = get_posts(
		array(
			'post__in'       => $featured_monograph_ids,
			'post_type'      => 'peptide',
			'posts_per_page' => 3,
			'orderby'        => 'post__in',
			'post_status'    => 'publish',
		)
	);
	if ( ! empty( $featured_posts ) ) :
		?>
	<section class="hero-featured-verdicts" aria-label="<?php esc_attr_e( 'Featured peptide verdicts', 'peptide-starter' ); ?>">
		<div class="ps-container">
			<h2 class="hero-featured-verdicts__title"><?php esc_html_e( 'Featured verdicts', 'peptide-starter' ); ?></h2>
			<p class="hero-featured-verdicts__subtitle">
				<?php esc_html_e( 'Real assessments from our database — see the system in action.', 'peptide-starter' ); ?>
			</p>
			<div class="hero-featured-verdicts__grid">
				<?php
				foreach ( $featured_posts as $featured_post ) :
					$post_id      = $featured_post->ID;
					$verdict_state = get_post_meta( $post_id, 'verdict_state', true );
					$verdict_text  = get_post_meta( $post_id, 'verdict_text', true );
					$post_url      = get_permalink( $post_id );

					// Verdict state config — @see inc/verdict-helpers.php
					$verdict_config = peptide_starter_get_verdict_config();
					$vc = isset( $verdict_config[ $verdict_state ] ) ? $verdict_config[ $verdict_state ] : null;

					// Signal rows (up to 3).
					$signal_rows = array();
					for ( $i = 1; $i <= 3; $i++ ) {
						$row_label = get_post_meta( $post_id, 'signal_row_' . $i . '_label', true );
						$row_glyph = get_post_meta( $post_id, 'signal_row_' . $i . '_glyph', true );
						if ( $row_label ) {
							$signal_rows[] = array(
								'label' => $row_label,
								'glyph' => $row_glyph ? $row_glyph : '•',
							);
						}
					}
					?>
				<article class="hero-verdict-card" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
					<?php if ( $vc ) : ?>
					<span
						class="pr-verdict pr-verdict--<?php echo esc_attr( $verdict_state ); ?> hero-verdict-card__badge"
						role="img"
						aria-label="<?php echo esc_attr( sprintf( __( 'Verdict: %s', 'peptide-starter' ), $vc['label'] ) ); ?>"
					>
						<span class="pr-verdict__glyph" aria-hidden="true"><?php echo esc_html( $vc['glyph'] ); ?></span>
						<span class="pr-verdict__label"><?php echo esc_html( $vc['label'] ); ?></span>
					</span>
					<?php endif; ?>

					<h3 class="hero-verdict-card__title">
						<a href="<?php echo esc_url( $post_url ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
					</h3>

					<?php if ( $verdict_text ) : ?>
					<p class="hero-verdict-card__summary"><?php echo esc_html( $verdict_text ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $signal_rows ) ) : ?>
					<ul class="hero-verdict-card__signals" aria-label="<?php esc_attr_e( 'Evidence signals', 'peptide-starter' ); ?>">
						<?php foreach ( $signal_rows as $row ) : ?>
						<li class="hero-verdict-card__signal-item">
							<span class="hero-verdict-card__signal-glyph" aria-hidden="true"><?php echo esc_html( $row['glyph'] ); ?></span>
							<?php echo esc_html( $row['label'] ); ?>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>

					<a href="<?php echo esc_url( $post_url ); ?>" class="hero-verdict-card__link">
						<?php esc_html_e( 'Read the full evidence →', 'peptide-starter' ); ?>
					</a>
				</article>
				<?php endforeach; ?>
			</div>

			<div class="hero-featured-verdicts__footer">
				<a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>" class="ps-btn ps-btn-secondary">
					<?php esc_html_e( 'Browse all peptides', 'peptide-starter' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<!-- Research Modules Grid (6 cards) -->
	<?php get_template_part( 'template-parts/module', 'cards' ); ?>

	<!-- PRAutoBlogger Posts Widget -->
	<?php
	if ( shortcode_exists( 'prautoblogger_posts' ) ) {
		echo do_shortcode( '[prautoblogger_posts count="6" title="' . esc_attr__( 'Latest Research & Insights', 'peptide-starter' ) . '" subtitle="' . esc_attr__( 'Evidence-based articles on peptides, protocols, and emerging research', 'peptide-starter' ) . '"]' );
	}
	?>

	<!-- News Feed Section -->
	<?php
	if ( shortcode_exists( 'peptide_news' ) ) {
		?>
		<section class="news-feed-section">
			<div class="ps-container">
				<div class="news-feed-header">
					<h2 class="news-feed-title"><?php esc_html_e( 'What\'s new', 'peptide-starter' ); ?></h2>
					<p class="news-feed-description">
						<?php esc_html_e( 'Recent peptide research, regulatory shifts, and synthesis news. New posts weekly.', 'peptide-starter' ); ?>
					</p>
				</div>
				<?php echo do_shortcode( '[peptide_news]' ); ?>
			</div>
		</section>
		<?php
	}
	?>

</main>

<?php get_footer(); ?>
