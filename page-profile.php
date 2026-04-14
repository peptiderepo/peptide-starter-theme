<?php
/**
 * Template Name: User Profile
 *
 * Renders the logged-in user's profile. Shows a verify-required banner
 * when the account has ps_pending_verification=1 and an inline "verified"
 * confirmation after a successful /verify click-through.
 *
 * @see page-auth.php — authentication template
 * @see inc/email-verification.php — verification flow + resend endpoint
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect unauthenticated users to the auth page. Intentionally does not
// use peptide_starter_require_login() — that would redirect unverified
// users away from profile, but profile is where we surface the verify UI.
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/auth?redirect_to=' . rawurlencode( get_permalink() ) ) );
	exit;
}

$current_user = wp_get_current_user();
$needs_verify = ! peptide_starter_user_is_verified( $current_user->ID );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$show_verified = isset( $_GET['verified'] ) && '1' === $_GET['verified'];

get_header();
?>

<main id="main" class="site-main">
	<section class="ps-tool-page">
		<div class="ps-container">
			<?php if ( $show_verified ) : ?>
				<div class="ps-alert ps-alert-success" role="alert">
					<p><?php esc_html_e( 'Your email address has been verified. Welcome aboard.', 'peptide-starter' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $needs_verify ) : ?>
				<div class="ps-alert ps-alert-warning ps-verify-required" role="alert">
					<h3><?php esc_html_e( 'Verify your email to unlock data tools', 'peptide-starter' ); ?></h3>
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: user email */
								__( 'We sent a verification link to %s. Click it to activate Subject Log, Tracker, and Protocol Builder. Links expire after 24 hours.', 'peptide-starter' ),
								$current_user->user_email
							)
						);
						?>
					</p>
					<form class="ps-verify-resend" id="ps-verify-resend" method="post" novalidate>
						<?php wp_nonce_field( 'ps_resend_verify', 'ps_resend_nonce' ); ?>
						<input type="hidden" name="action" value="ps_resend_verify">
						<input type="hidden" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>">
						<button type="submit" class="ps-btn ps-btn-secondary">
							<?php esc_html_e( 'Resend verification email', 'peptide-starter' ); ?>
						</button>
						<span class="ps-verify-resend-status" aria-live="polite"></span>
					</form>
				</div>
			<?php endif; ?>

			<?php
			if ( shortcode_exists( 'peptide_community_profile' ) ) {
				echo do_shortcode( '[peptide_community_profile]' );
			} else {
				?>
				<div class="ps-profile-basic">
					<h1>
						<?php
						/* translators: %s: user display name */
						echo esc_html( sprintf( __( 'Welcome, %s', 'peptide-starter' ), $current_user->display_name ) );
						?>
					</h1>
					<p><?php esc_html_e( 'Your profile page is being set up. Check back soon for full profile features.', 'peptide-starter' ); ?></p>
					<div class="ps-profile-links">
						<a href="<?php echo esc_url( home_url( '/tracker' ) ); ?>" class="ps-btn ps-btn-secondary">
							<?php esc_html_e( 'Tracker', 'peptide-starter' ); ?>
						</a>
						<a href="<?php echo esc_url( home_url( '/subject-log' ) ); ?>" class="ps-btn ps-btn-secondary">
							<?php esc_html_e( 'Subject Log', 'peptide-starter' ); ?>
						</a>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="ps-btn ps-btn-tertiary">
							<?php esc_html_e( 'Sign Out', 'peptide-starter' ); ?>
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</section>
</main>

<script>
(function() {
	const form = document.getElementById('ps-verify-resend');
	if (!form) return;
	const status = form.querySelector('.ps-verify-resend-status');
	form.addEventListener('submit', function(e) {
		e.preventDefault();
		const data = new FormData(form);
		const xhr = new XMLHttpRequest();
		xhr.open('POST', '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>');
		xhr.onload = function() {
			try {
				const resp = JSON.parse(xhr.responseText);
				if (status) {
					const msg = (resp && resp.data && resp.data.message) ? resp.data.message : '';
					const div = document.createElement('span');
					div.textContent = ' ' + msg;
					status.innerHTML = '';
					status.appendChild(div);
				}
			} catch (ex) {
				if (status) { status.textContent = ''; }
			}
		};
		xhr.send(data);
	});
})();
</script>

<?php get_footer(); ?>
