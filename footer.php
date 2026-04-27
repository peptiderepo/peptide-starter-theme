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
				for ( $i = 1; $i <= 5; $i++ ) {
					echo '<div class="footer-section">';
					if ( is_active_sidebar( 'footer-' . $i ) ) {
						dynamic_sidebar( 'footer-' . $i );
					} else {
						// Default footer content if no widgets.
						if ( 1 === $i ) {
							echo '<h3>' . esc_html__( 'About', 'peptide-starter' ) . '</h3>';
							echo '<p>' . esc_html__( 'Verdict-driven peptide guides for clinicians and informed readers. Clear about the evidence — including when there isn\'t any.', 'peptide-starter' ) . '</p>';
							echo '<p style="margin-top: var(--spacing-md); font-size: 0.9rem;"><a href="' . esc_url( home_url( '/subscribe' ) ) . '" style="color: var(--color-accent-green, #5a7c5a); font-weight: 600;">' . esc_html__( 'Get our weekly digest →', 'peptide-starter' ) . '</a></p>';
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
						} elseif ( 5 === $i ) {
							echo '<h3>' . esc_html__( 'Our Method', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="' . esc_url( home_url( '/how-we-review-peptides' ) ) . '">' . esc_html__( 'How We Review Peptides', 'peptide-starter' ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/our-methodology' ) ) . '">' . esc_html__( 'Our Methodology', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/about' ) ) . '">' . esc_html__( 'Editorial Standards', 'peptide-starter' ) . '</a></li>';
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
					<?php echo wp_kses_post( peptide_starter_get_footer_copyright() ); ?>
				</p>

				<!-- Disclosure -->
				<?php
				get_template_part(
					'template-parts/affiliate-disclosure',
					null,
					array( 'context' => 'footer' )
				);
				?>

				<!-- Disclaimer -->
				<p class="footer-disclaimer">
					<?php esc_html_e( 'All data is provided for informational and educational purposes only. This platform does not provide medical advice, diagnoses, or treatment recommendations. Always consult qualified professionals.', 'peptide-starter' ); ?>
				</p>

				<!-- Translations note — placeholder links removed in v1.5.1;
					renders only when localized blog URLs are wired up. -->
				<p class="footer-languages-note">
					<?php esc_html_e( 'Translations coming soon.', 'peptide-starter' ); ?>
				</p>
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
