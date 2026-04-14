<?php
/**
 * The footer for Peptide Starter theme
 *
 * Updated footer with disclaimer, language links, and settings panel.
 *
 * @see template-parts/settings-panel.php — slide-out support panel
 * @see template-parts/newsletter-signup.php — newsletter CTA (on front page)
 * @see functions.php — newsletter handler, contact form handler
 *
 * @package peptide-starter
 */

?>

	<!-- Newsletter Signup (front page only — science feed includes its own) -->
	<?php if ( is_front_page() ) : ?>
		<?php get_template_part( 'template-parts/newsletter', 'signup' ); ?>
	<?php endif; ?>

	<footer class="site-footer">
		<div class="footer-wrapper">
			<!-- Footer Grid -->
			<div class="footer-grid">
				<?php
				for ( $i = 1; $i <= 4; $i++ ) {
					echo '<div class="footer-section">';
					if ( is_active_sidebar( 'footer-' . $i ) ) {
						dynamic_sidebar( 'footer-' . $i );
					} else {
						// Default footer content if no widgets.
						if ( 1 === $i ) {
							echo '<h3>' . esc_html__( 'About', 'peptide-starter' ) . '</h3>';
							echo '<p>' . esc_html__( 'Peptide Repo is a comprehensive scientific database for peptide research.', 'peptide-starter' ) . '</p>';
						} elseif ( 2 === $i ) {
							echo '<h3>' . esc_html__( 'Quick Links', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/peptides' ) ) . '">' . esc_html__( 'Peptides Database', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/documentation' ) ) . '">' . esc_html__( 'Documentation', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/news' ) ) . '">' . esc_html__( 'Science Feed', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						} elseif ( 3 === $i ) {
							echo '<h3>' . esc_html__( 'Tools', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="' . esc_url( home_url( '/calculator' ) ) . '">' . esc_html__( 'Calculator', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/protocol-builder' ) ) . '">' . esc_html__( 'Protocol Builder', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/tracker' ) ) . '">' . esc_html__( 'Tracker', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						} elseif ( 4 === $i ) {
							echo '<h3>' . esc_html__( 'Connect', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="https://github.com/peptiderepo" target="_blank" rel="noopener">' . esc_html__( 'GitHub', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						}
					}
					echo '</div>';
				}
				?>
			</div>

			<!-- Footer Bottom -->
			<div class="footer-bottom">
				<p class="footer-copyright">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: current year */
							__( '&copy; %s Research Directory | Laboratory Standards', 'peptide-starter' ),
							gmdate( 'Y' )
						)
					);
					?>
				</p>

				<!-- Disclaimer -->
				<p class="footer-disclaimer">
					<?php esc_html_e( 'All data is provided for informational and educational purposes only. This platform does not provide medical advice, diagnoses, or treatment recommendations. Always consult qualified professionals.', 'peptide-starter' ); ?>
				</p>

				<!-- Language Links (placeholder) -->
				<div class="footer-languages">
					<span class="footer-languages-label"><?php esc_html_e( 'Our Blogs:', 'peptide-starter' ); ?></span>
					<a href="#" hreflang="en"><?php esc_html_e( 'English', 'peptide-starter' ); ?></a>
					<a href="#" hreflang="el"><?php esc_html_e( 'Greek', 'peptide-starter' ); ?></a>
					<a href="#" hreflang="es"><?php esc_html_e( 'Spanish', 'peptide-starter' ); ?></a>
					<a href="#" hreflang="nl"><?php esc_html_e( 'Dutch', 'peptide-starter' ); ?></a>
				</div>
			</div>
		</div>
	</footer>

	<?php
	// Settings slide-out panel (hidden by default, toggled via JS).
	get_template_part( 'template-parts/settings', 'panel' );
	?>

	<?php wp_footer(); ?>
</body>
</html>
