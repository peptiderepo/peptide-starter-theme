<?php
/**
 * The footer for Peptide Starter theme
 *
 * @package peptide-starter
 */

?>
	</main><!-- #main -->

	<footer class="site-footer">
		<div class="footer-wrapper">
			<!-- Newsletter Signup -->
			<?php if ( peptide_starter_show_newsletter_form() ) { ?>
				<div class="footer-newsletter">
					<h3><?php esc_html_e( 'Stay Updated', 'peptide-starter' ); ?></h3>
					<p><?php esc_html_e( 'Subscribe to our newsletter for the latest peptide research and updates.', 'peptide-starter' ); ?></p>
					<form class="newsletter-form" action="#" method="post">
						<input
							type="email"
							name="email"
							class="newsletter-input"
							placeholder="<?php esc_attr_e( 'your@email.com', 'peptide-starter' ); ?>"
							required
						>
						<button type="submit" class="ps-btn ps-btn-primary">
							<?php esc_html_e( 'Subscribe', 'peptide-starter' ); ?>
						</button>
					</form>
				</div>
			<?php } ?>

			<!-- Footer Grid -->
			<div class="footer-grid">
				<!-- Footer Widget Areas -->
				<?php
				for ( $i = 1; $i <= 4; $i++ ) {
					echo '<div class="footer-section">';
					if ( is_active_sidebar( 'footer-' . $i ) ) {
						dynamic_sidebar( 'footer-' . $i );
					} else {
						// Default footer content if no widgets
						if ( 1 === $i ) {
							echo '<h3>' . esc_html__( 'About', 'peptide-starter' ) . '</h3>';
							echo '<p>' . esc_html__( 'Peptide Repo is a comprehensive scientific database for peptide research.', 'peptide-starter' ) . '</p>';
						} elseif ( 2 === $i ) {
							echo '<h3>' . esc_html__( 'Quick Links', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="' . esc_url( home_url( '/peptides' ) ) . '">' . esc_html__( 'Peptides Database', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						} elseif ( 3 === $i ) {
							echo '<h3>' . esc_html__( 'Resources', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="#">' . esc_html__( 'Research', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="#">' . esc_html__( 'Documentation', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						} elseif ( 4 === $i ) {
							echo '<h3>' . esc_html__( 'Connect', 'peptide-starter' ) . '</h3>';
							echo '<ul>';
							echo '<li><a href="https://github.com/peptiderepo" target="_blank" rel="noopener">' . esc_html__( 'GitHub', 'peptide-starter' ) . '</a></li>';
							echo '<li><a href="#">' . esc_html__( 'Twitter', 'peptide-starter' ) . '</a></li>';
							echo '</ul>';
						}
					}
					echo '</div>';
				}
				?>
			</div>

			<!-- Footer Bottom -->
			<div class="footer-bottom">
				<p><?php echo wp_kses_post( peptide_starter_get_footer_copyright() ); ?></p>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
