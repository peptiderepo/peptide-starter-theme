<?php
/**
 * Newsletter Admin Page
 *
 * Admin interface under Tools for viewing / exporting newsletter
 * subscribers. Exports apply CSV-injection safety to every cell. A
 * migration-advisory notice is emitted once the subscriber count
 * exceeds the configured threshold.
 *
 * @see functions.php — peptide_starter_handle_newsletter_signup() writes the option
 * @see template-parts/newsletter-signup.php — the frontend form
 * @see inc/config.php — newsletter_autoload_threshold
 * @see inc/helpers.php — peptide_starter_csv_safe()
 *
 * What: Admin subscriber viewer + CSV export.
 * Who calls it: WordPress admin_menu + admin_init actions.
 * Dependencies: wp_options storage, CSV-safe helper.
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
 * @return void
 */
function peptide_starter_newsletter_admin_page() {
	$emails    = get_option( 'ps_newsletter_emails', array() );
	$threshold = (int) peptide_starter_config_get( 'newsletter_autoload_threshold', 1000 );
	$count     = count( $emails );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter Subscribers', 'peptide-starter' ); ?></h1>
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of subscribers */
					_n( '%d subscriber', '%d subscribers', $count, 'peptide-starter' ),
					$count
				)
			);
			?>
		</p>

		<?php if ( $count > $threshold ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: subscriber threshold */
							__( 'Subscriber count exceeds %d — consider migrating newsletter storage from wp_options to a dedicated table for performance (tracked as ADR-0004).', 'peptide-starter' ),
							$threshold
						)
					);
					?>
				</p>
			</div>
		<?php endif; ?>

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
						<th><?php esc_html_e( 'Status', 'peptide-starter' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $emails as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['email'] ); ?></td>
							<td><?php echo esc_html( isset( $entry['date'] ) ? $entry['date'] : '' ); ?></td>
							<td>
								<?php
								$status = isset( $entry['unsubscribed'] ) && $entry['unsubscribed'] ? 'unsubscribed' : 'active';
								echo esc_html( $status );
								?>
							</td>
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
 * Every cell is routed through peptide_starter_csv_safe() to neutralise
 * formula-injection attacks (values starting with =, +, -, @, tab, CR).
 *
 * Side effects: emits CSV response and exits.
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
	fputcsv( $output, array( 'Email', 'Date Added', 'Status' ) );

	foreach ( $emails as $entry ) {
		$status = isset( $entry['unsubscribed'] ) && $entry['unsubscribed'] ? 'unsubscribed' : 'active';
		fputcsv(
			$output,
			array(
				peptide_starter_csv_safe( isset( $entry['email'] ) ? $entry['email'] : '' ),
				peptide_starter_csv_safe( isset( $entry['date'] ) ? $entry['date'] : '' ),
				peptide_starter_csv_safe( $status ),
			)
		);
	}

	fclose( $output );
	exit;
}
add_action( 'admin_init', 'peptide_starter_newsletter_export_csv' );

/**
 * Handle /newsletter-unsubscribe?token=... — token-gated, no login required.
 *
 * Side effects: flips the unsubscribed flag on the matching entry.
 *
 * @return void
 */
function peptide_starter_handle_unsubscribe() {
	if ( ! is_main_query() || ! isset( $_GET['ps_unsubscribe'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' === $token ) {
		return;
	}

	$emails  = get_option( 'ps_newsletter_emails', array() );
	$changed = false;
	foreach ( $emails as $idx => $entry ) {
		if ( isset( $entry['unsub_token'] ) && hash_equals( (string) $entry['unsub_token'], $token ) ) {
			$emails[ $idx ]['unsubscribed'] = true;
			$changed                        = true;
			break;
		}
	}

	if ( $changed ) {
		update_option( 'ps_newsletter_emails', $emails, false );
	}

	wp_safe_redirect( home_url( '/?ps_newsletter=unsubscribed' ) );
	exit;
}

/**
 * Register the /newsletter-unsubscribe rewrite.
 *
 * @return void
 */
function peptide_starter_register_unsubscribe_rewrite() {
	add_rewrite_rule( '^newsletter-unsubscribe/?$', 'index.php?ps_unsubscribe=1', 'top' );
}
add_action( 'init', 'peptide_starter_register_unsubscribe_rewrite' );

/**
 * Register the ps_unsubscribe query var.
 *
 * @param array $vars Existing query vars.
 * @return array
 */
function peptide_starter_register_unsubscribe_query_var( $vars ) {
	$vars[] = 'ps_unsubscribe';
	return $vars;
}
add_filter( 'query_vars', 'peptide_starter_register_unsubscribe_query_var' );

add_action( 'template_redirect', 'peptide_starter_handle_unsubscribe' );
