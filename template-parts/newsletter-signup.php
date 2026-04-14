<?php
/**
 * Template Part: Newsletter Signup
 *
 * Reusable "Monthly Research Dispatch" CTA with consent checkbox and honeypot.
 * Success and duplicate states are merged into a single "ok" message to
 * avoid leaking subscription status of any address.
 *
 * @see functions.php — peptide_starter_handle_newsletter_signup()
 * @see inc/helpers.php — peptide_starter_render_honeypot()
 *
 * What: Renders the newsletter signup form.
 * Who calls it: get_template_part( 'template-parts/newsletter', 'signup' ).
 * Dependencies: None — self-contained form.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$newsletter_status = isset( $_GET['ps_newsletter'] ) ? sanitize_text_field( wp_unslash( $_GET['ps_newsletter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$status_message    = '';
$status_class      = '';

// Unified "ok" state merges the old success + duplicate — do not leak
// whether the address was already subscribed (abuse signal).
if ( 'ok' === $newsletter_status ) {
	$status_message = __( 'Thanks — if this is your first subscription, you will receive our next dispatch.', 'peptide-starter' );
	$status_class   = 'ps-alert-success';
} elseif ( 'invalid' === $newsletter_status ) {
	$status_message = __( 'Please enter a valid email address and agree to receive updates.', 'peptide-starter' );
	$status_class   = 'ps-alert-error';
}

$privacy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : home_url( '/privacy' );
?>

<section class="ps-newsletter-section">
	<div class="ps-container">
		<div class="ps-newsletter-inner">
			<h2 class="ps-newsletter-title">
				<?php esc_html_e( 'Monthly Research Dispatch', 'peptide-starter' ); ?>
			</h2>
			<p class="ps-newsletter-subtitle">
				<?php esc_html_e( "First edition drops soon. Don't miss the data.", 'peptide-starter' ); ?>
			</p>

			<?php if ( $status_message ) : ?>
				<div class="ps-alert <?php echo esc_attr( $status_class ); ?> ps-newsletter-status" role="alert">
					<p><?php echo esc_html( $status_message ); ?></p>
				</div>
			<?php endif; ?>

			<form class="ps-newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<?php wp_nonce_field( 'peptide_starter_newsletter', 'ps_newsletter_nonce' ); ?>
				<input type="hidden" name="action" value="peptide_starter_newsletter_signup">
				<?php peptide_starter_render_honeypot( 'newsletter' ); ?>

				<div class="ps-newsletter-form-row">
					<input
						type="email"
						name="email"
						class="ps-newsletter-input"
						placeholder="<?php esc_attr_e( 'your@email.com', 'peptide-starter' ); ?>"
						required
						aria-label="<?php esc_attr_e( 'Email address', 'peptide-starter' ); ?>"
					>
					<button type="submit" class="ps-btn ps-btn-primary">
						<?php esc_html_e( 'Subscribe', 'peptide-starter' ); ?>
					</button>
				</div>

				<label class="ps-newsletter-consent">
					<input type="checkbox" name="ps_consent" value="1" required>
					<span>
						<?php esc_html_e( 'I agree to receive occasional research updates. Unsubscribe anytime.', 'peptide-starter' ); ?>
					</span>
				</label>

				<p class="ps-newsletter-privacy">
					<a href="<?php echo esc_url( $privacy_url ); ?>" rel="noopener">
						<?php esc_html_e( 'Privacy policy', 'peptide-starter' ); ?>
					</a>
				</p>
			</form>

			<p class="ps-newsletter-learn-more">
				<a href="<?php echo esc_url( home_url( '/documentation' ) ); ?>">
					<?php esc_html_e( 'Learn more about the dispatch', 'peptide-starter' ); ?> &rarr;
				</a>
			</p>
		</div>
	</div>
</section>
