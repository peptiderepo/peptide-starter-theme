<?php
/**
 * wp_mail Deliverability Diagnostic
 *
 * Admin-only tool under Tools → Mail Test. Sends a test message via
 * wp_mail and reports whether delivery was accepted by the MTA,
 * timing, and any PHPMailer error. Useful every time Hostinger (or any
 * shared host) gets flaky on outbound SMTP.
 *
 * @see inc/config.php — no config dependency; uses hardcoded test body
 * @see functions.php — includes this file
 *
 * What: Admin diagnostic page that exercises wp_mail().
 * Who calls it: WordPress admin_menu + admin_post_*.
 * Dependencies: wp_mail, phpmailer_init hook capture.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Mail Test admin page under Tools.
 *
 * @return void
 */
function peptide_starter_mail_diagnostic_menu() {
	add_management_page(
		__( 'Mail Deliverability Test', 'peptide-starter' ),
		__( 'Mail Test', 'peptide-starter' ),
		'manage_options',
		'ps-mail-test',
		'peptide_starter_mail_diagnostic_page'
	);
}
add_action( 'admin_menu', 'peptide_starter_mail_diagnostic_menu' );

/**
 * Render the Mail Test admin page.
 *
 * @return void
 */
function peptide_starter_mail_diagnostic_page() {
	$result = get_transient( 'ps_mail_test_result' );
	if ( $result ) {
		delete_transient( 'ps_mail_test_result' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mail Deliverability Test', 'peptide-starter' ); ?></h1>
		<p>
			<?php esc_html_e( 'Sends a test message via wp_mail() to the address you specify and reports whether the local MTA accepted it. Use this after every host-side change to verify transactional mail (registration, verification, password reset) still works.', 'peptide-starter' ); ?>
		</p>

		<?php if ( is_array( $result ) ) : ?>
			<div class="notice <?php echo $result['sent'] ? 'notice-success' : 'notice-error'; ?>">
				<p>
					<strong>
						<?php
						echo esc_html(
							$result['sent']
								? __( 'wp_mail returned true (local MTA accepted).', 'peptide-starter' )
								: __( 'wp_mail returned false (local MTA rejected or plugin error).', 'peptide-starter' )
						);
						?>
					</strong>
				</p>
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: recipient, 2: duration in ms, 3: phpmailer error */
							__( 'Recipient: %1$s — Duration: %2$s ms — PHPMailer: %3$s', 'peptide-starter' ),
							$result['to'],
							number_format( $result['ms'] ),
							'' === $result['error'] ? '—' : $result['error']
						)
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Note: wp_mail returning true only means the outbound MTA accepted the message. Spam placement can only be verified by checking the recipient inbox.', 'peptide-starter' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ps_mail_test', 'ps_mail_test_nonce' ); ?>
			<input type="hidden" name="action" value="ps_mail_test">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="ps_mail_to"><?php esc_html_e( 'Recipient', 'peptide-starter' ); ?></label></th>
					<td>
						<input
							type="email"
							id="ps_mail_to"
							name="to"
							class="regular-text"
							value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
							required
						>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Send Test Email', 'peptide-starter' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Handle the test-email POST.
 *
 * Side effects: calls wp_mail, stores a short-lived transient with the
 * result, redirects back to the admin page.
 *
 * @return void
 */
function peptide_starter_mail_diagnostic_send() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient privileges.', 'peptide-starter' ), '', array( 'response' => 403 ) );
	}
	if ( ! isset( $_POST['ps_mail_test_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_mail_test_nonce'] ) ), 'ps_mail_test' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'peptide-starter' ), '', array( 'response' => 403 ) );
	}

	$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
	if ( ! is_email( $to ) ) {
		wp_safe_redirect( admin_url( 'tools.php?page=ps-mail-test' ) );
		exit;
	}

	$captured_error = '';
	$capture        = function ( $wp_error ) use ( &$captured_error ) {
		if ( is_wp_error( $wp_error ) ) {
			$captured_error = $wp_error->get_error_message();
		}
	};
	add_action( 'wp_mail_failed', $capture );

	$start   = microtime( true );
	$subject = sprintf(
		/* translators: %s: site name */
		__( '[%s] Mail deliverability test', 'peptide-starter' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
	);
	$body    = sprintf(
		/* translators: %s: site url */
		__( "This is a wp_mail deliverability test from %s.\n\nIf you received this, wp_mail is working.", 'peptide-starter' ),
		home_url( '/' )
	);
	$sent    = wp_mail( $to, $subject, $body );
	$elapsed = ( microtime( true ) - $start ) * 1000;

	remove_action( 'wp_mail_failed', $capture );

	set_transient(
		'ps_mail_test_result',
		array(
			'sent'  => (bool) $sent,
			'to'    => $to,
			'ms'    => (int) $elapsed,
			'error' => (string) $captured_error,
		),
		60
	);

	wp_safe_redirect( admin_url( 'tools.php?page=ps-mail-test' ) );
	exit;
}
add_action( 'admin_post_ps_mail_test', 'peptide_starter_mail_diagnostic_send' );
