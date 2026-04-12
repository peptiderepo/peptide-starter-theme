<?php
/**
 * The front page template
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<!-- Hero Section -->
	<section class="hero-section">
		<div class="hero-content">
			<h1 class="hero-title"><?php echo esc_html( peptide_starter_get_hero_title() ); ?></h1>
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
				<a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="ps-btn ps-btn-secondary">
					<?php esc_html_e( 'Learn More', 'peptide-starter' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- PRAutoBlogger Posts Widget -->
	<?php
	if ( shortcode_exists( 'prautoblogger_posts' ) ) {
		echo do_shortcode( '[prautoblogger_posts count="6" title="' . esc_attr__( 'Latest Research & Insights', 'peptide-starter' ) . '" subtitle="' . esc_attr__( 'Evidence-based articles on peptides, protocols, and emerging research', 'peptide-starter' ) . '"]' );
	}
	?>

	<!-- News Feed Section -->
	<?php
	// Display the Peptide News plugin shortcode if available
	if ( shortcode_exists( 'peptide_news' ) ) {
		?>
		<section class="news-feed-section">
			<div class="ps-container">
				<div class="news-feed-header">
					<h2 class="news-feed-title"><?php esc_html_e( 'Latest Peptide Research', 'peptide-starter' ); ?></h2>
					<p class="news-feed-description">
						<?php esc_html_e( 'Stay updated with the latest developments in peptide science and research.', 'peptide-starter' ); ?>
					</p>
				</div>
				<?php echo do_shortcode( '[peptide_news]' ); ?>
			</div>
		</section>
		<?php
	}
	?>

	<?php
	// Note: The front page layout is handled entirely by this template.
	// Old page content (e.g. from Elementor) is intentionally NOT rendered here
	// to avoid duplicate hero sections and empty space.
	?>
</main>

<?php get_footer(); ?>
