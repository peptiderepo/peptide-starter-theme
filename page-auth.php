<?php
/**
 * Template Name: Sign In / Register
 *
 * Branded authentication page replacing wp-login.php for the frontend.
 * Handles sign-in and registration via WordPress native auth functions.
 *
 * @see assets/js/auth.js — form toggle and client-side validation
 * @see functions.php — AJAX handler for auth form submission
 *
 * What: Renders sign-in / register forms with CSRF protection.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: WordPress core auth (wp_signon, wp_create_user).
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect already logged-in users.
if ( is_user_logged_in() ) {
	$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/profile' );
	wp_safe_redirect( $redirect );
	exit;
}

// Capture redirect target for post-auth.
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );

get_header();
?>

<main id="main" class="site-main">
	<section class="ps-tool-page ps-auth-page">
		<div class="ps-container ps-auth-container">
			<div class="ps-auth-card">
				<!-- Info Box -->
				<div class="ps-alert ps-alert-info ps-auth-info">
					<p>
						<?php esc_html_e( 'To access the Subject Logs and store assay / lab results and protocols, you must log in or register so we can secure your data.', 'peptide-starter' ); ?>
					</p>
				</div>

				<!-- Auth Status Messages -->
				<div id="ps-auth-messages" class="ps-auth-messages" aria-live="polite"></div>

				<!-- Sign In Form -->
				<div id="ps-auth-login" class="ps-auth-form-wrap">
					<h1><?php esc_html_e( 'Sign In', 'peptide-starter' ); ?></h1>

					<form id="ps-login-form" class="ps-auth-form" method="post" novalidate>
						<?php wp_nonce_field( 'ps_auth_login', 'ps_login_nonce' ); ?>
						<input type="hidden" name="action" value="ps_auth_login">
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

						<div class="ps-form-group">
							<label for="ps-login-email"><?php esc_html_e( 'Email', 'peptide-starter' ); ?></label>
							<input
								type="email"
								id="ps-login-email"
								name="email"
								placeholder="<?php esc_attr_e( 'researcher@lab.com', 'peptide-starter' ); ?>"
								required
								autocomplete="email"
							>
						</div>

						<div class="ps-form-group">
							<label for="ps-login-password"><?php esc_html_e( 'Password', 'peptide-starter' ); ?></label>
							<input
								type="password"
								id="ps-login-password"
								name="password"
								required
								autocomplete="current-password"
							>
						</div>

						<button type="submit" class="ps-btn ps-btn-primary ps-btn-full">
							<?php esc_html_e( 'Sign In', 'peptide-starter' ); ?>
						</button>

						<p class="ps-auth-link">
							<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
								<?php esc_html_e( 'Forgot password?', 'peptide-starter' ); ?>
							</a>
						</p>
					</form>

					<p class="ps-auth-toggle">
						<?php esc_html_e( "Don't have an account?", 'peptide-starter' ); ?>
						<a href="#register" id="ps-show-register"><?php esc_html_e( 'Register', 'peptide-starter' ); ?></a>
					</p>
				</div>

				<!-- Register Form (hidden by default) -->
				<div id="ps-auth-register" class="ps-auth-form-wrap ps-auth-form-wrap--hidden">
					<h1><?php esc_html_e( 'Create Account', 'peptide-starter' ); ?></h1>

					<form id="ps-register-form" class="ps-auth-form" method="post" novalidate>
						<?php wp_nonce_field( 'ps_auth_register', 'ps_register_nonce' ); ?>
						<input type="hidden" name="action" value="ps_auth_register">
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

						<div class="ps-form-group">
							<label for="ps-reg-username"><?php esc_html_e( 'Username', 'peptide-starter' ); ?></label>
							<input
								type="text"
								id="ps-reg-username"
								name="username"
								placeholder="<?php esc_attr_e( '5-15 alphanumeric characters', 'peptide-starter' ); ?>"
								required
								minlength="5"
								maxlength="15"
								pattern="[a-zA-Z0-9]+"
								autocomplete="username"
							>
							<span class="ps-form-hint">
								<?php esc_html_e( 'This becomes your public username.', 'peptide-starter' ); ?>
							</span>
						</div>

						<div class="ps-form-group">
							<label for="ps-reg-email"><?php esc_html_e( 'Email', 'peptide-starter' ); ?></label>
							<input
								type="email"
								id="ps-reg-email"
								name="email"
								required
								autocomplete="email"
							>
						</div>

						<div class="ps-form-group">
							<label for="ps-reg-password"><?php esc_html_e( 'Password', 'peptide-starter' ); ?></label>
							<input
								type="password"
								id="ps-reg-password"
								name="password"
								required
								minlength="8"
								autocomplete="new-password"
							>
							<div id="ps-password-strength" class="ps-password-strength" aria-live="polite"></div>
						</div>

						<button type="submit" class="ps-btn ps-btn-primary ps-btn-full">
							<?php esc_html_e( 'Register', 'peptide-starter' ); ?>
						</button>
					</form>

					<p class="ps-auth-toggle">
						<?php esc_html_e( 'Already have an account?', 'peptide-starter' ); ?>
						<a href="#login" id="ps-show-login"><?php esc_html_e( 'Sign In', 'peptide-starter' ); ?></a>
					</p>
				</div>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
