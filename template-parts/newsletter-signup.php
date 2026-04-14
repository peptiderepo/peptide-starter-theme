<?php
/**
 * Template Part: Newsletter Signup
 *
 * Reusable newsletter signup section displayed on front page and science feed.
 * Stores emails in wp_options as a placeholder until Mailchimp/similar is integrated.
 *
 * @see functions.php — peptide_starter_handle_newsletter_signup() for form processing
 * @see front-page.php — includes this template part
 * @see page-science-feed.php — includes this template part
 *
 * What: Renders the "Monthly Research Dispatch" email signup form.
 * Who calls it: get_template_part( 'template-parts/newsletter', 'signup' ).
 * Dependencies: None — self-contained HTML form.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check for newsletter submission status messages.
$newsletter_status  = isset( $_GET['ps_newsletter'] ) ? sanitize_text_field( wp_unslash( $_GET['ps_newsletter'] ) ) : '';
$status_message     = '';
$status_class       = '';

if ( 'success' === $newsletter_status ) {
	$status_message = __( 'Thanks for subscribing! You will receive our next dispatch.', 'peptide-starter' );
	$status_class   = 'ps-alert-success';
} elseif ( 'invalid' === $newsletter_status ) {
	$status_message = __( 'Please enter a valid email address.', 'peptide-starter' );
	$status_class   = 'ps-alert-error';
} elseif ( 'duplicate' === $newsletter_status ) {
	$status_message = __( 'This email is already subscribed.', 'peptide-starter' );
	$status_class   = 'ps-alert-info';
}
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
			</form>

			<p class="ps-newsletter-learn-more">
				<a href="<?php echo esc_url( home_url( '/documentation' ) ); ?>">
					<?php esc_html_e( 'Learn more about the dispatch', 'peptide-starter' ); ?> &rarr;
				</a>
			</p>
		</div>
	</div>
</section>
