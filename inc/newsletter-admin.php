<?php
/**
 * Newsletter Admin Page
 *
 * Simple admin interface to view and export collected newsletter emails.
 * Emails are stored in wp_options as 'ps_newsletter_emails' until a
 * proper mailing service (Mailchimp, etc.) is integrated.
 *
 * @see functions.php — newsletter signup handler (stores emails)
 * @see template-parts/newsletter-signup.php — frontend form
 *
 * What: Admin page under Tools for viewing/exporting subscriber emails.
 * Who calls it: WordPress admin_menu action.
 * Dependencies: None — reads from wp_options.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the newsletter admin page under Tools.
 *
 * @return void
 */
function peptide_starter_newsletter_admin_menu() {
	add_management_page(
		__( 'Newsletter Subscribers', 'peptide-starter' ),
		__( 'Newsletter', 'peptide-starter' ),
		'manage_options',
		'ps-newsletter',
		'peptide_starter_newsletter_admin_page'
	);
}
add_action( 'admin_menu', 'peptide_starter_newsletter_admin_menu' );

/**
 * Render the newsletter admin page.
 *
 * Displays a simple table of subscriber emails with date added.
 * Includes a CSV export button.
 *
 * @return void
 */
function peptide_starter_newsletter_admin_page() {
	$emails = get_option( 'ps_newsletter_emails', array() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter Subscribers', 'peptide-starter' ); ?></h1>
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of subscribers */
					_n( '%d subscriber', '%d subscribers', count( $emails ), 'peptide-starter' ),
					count( $emails )
				)
			);
			?>
		</p>

		<?php if ( ! empty( $emails ) ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'ps_newsletter_export', 'ps_export_nonce' ); ?>
				<input type="hidden" name="ps_action" value="export_csv">
				<?php submit_button( __( 'Export CSV', 'peptide-starter' ), 'secondary', 'export', false ); ?>
			</form>

			<table class="widefat striped" style="margin-top: 16px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email', 'peptide-starter' ); ?></th>
						<th><?php esc_html_e( 'Date Added', 'peptide-starter' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $emails as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['email'] ); ?></td>
							<td><?php echo esc_html( $entry['date'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No subscribers yet.', 'peptide-starter' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Handle CSV export of newsletter subscribers.
 *
 * @return void
 */
function peptide_starter_newsletter_export_csv() {
	if ( ! isset( $_POST['ps_action'] ) || 'export_csv' !== $_POST['ps_action'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_POST['ps_export_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_export_nonce'] ) ), 'ps_newsletter_export' ) ) {
		return;
	}

	$emails = get_option( 'ps_newsletter_emails', array() );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=newsletter-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );

	$output = fopen( 'php://output', 'w' );
	fputcsv( $output, array( 'Email', 'Date Added' ) );

	foreach ( $emails as $entry ) {
		fputcsv( $output, array( $entry['email'], $entry['date'] ) );
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'peptide_starter_newsletter_export_csv' );
