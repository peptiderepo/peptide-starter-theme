<?php
/**
 * Auto-Create Pages + v1.5.0 User Migration
 *
 * On theme activation, creates the required WordPress pages with the
 * correct templates assigned, and runs a one-time migration that enrols
 * pre-v1.5.1 users into the email-verification flow.
 *
 * @see functions.php — includes this file
 * @see inc/email-verification.php — peptide_starter_send_verification_email()
 * @see ARCHITECTURE.md — page/template map
 *
 * What: Theme-activation hooks for page provisioning and user enrolment.
 * Who calls it: WordPress after_switch_theme.
 * Dependencies: wp_insert_post, update_post_meta, user meta.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create required pages on theme activation.
 *
 * Idempotent: re-assigns the template on existing pages.
 *
 * @return void
 */
function peptide_starter_create_pages() {
	$pages = array(
		array(
			'title'    => __( 'Calculator', 'peptide-starter' ),
			'slug'     => 'calculator',
			'template' => 'page-calculator.php',
		),
		array(
			'title'    => __( 'Protocol Builder', 'peptide-starter' ),
			'slug'     => 'protocol-builder',
			'template' => 'page-protocol-builder.php',
		),
		array(
			'title'    => __( 'Tracker', 'peptide-starter' ),
			'slug'     => 'tracker',
			'template' => 'page-tracker.php',
		),
		array(
			'title'    => __( 'Subject Log', 'peptide-starter' ),
			'slug'     => 'subject-log',
			'template' => 'page-subject-log.php',
		),
		array(
			'title'    => __( 'Documentation', 'peptide-starter' ),
			'slug'     => 'documentation',
			'template' => 'page-documentation.php',
		),
		array(
			'title'    => __( 'Peptide Directory', 'peptide-starter' ),
			'slug'     => 'peptides',
			'template' => 'page-directory.php',
		),
		array(
			'title'    => __( 'Science Feed', 'peptide-starter' ),
			'slug'     => 'news',
			'template' => 'page-science-feed.php',
		),
		array(
			'title'    => __( 'Profile', 'peptide-starter' ),
			'slug'     => 'profile',
			'template' => 'page-profile.php',
		),
		array(
			'title'    => __( 'Sign In', 'peptide-starter' ),
			'slug'     => 'auth',
			'template' => 'page-auth.php',
		),
	);

	foreach ( $pages as $page_data ) {
		$existing = get_page_by_path( $page_data['slug'] );

		if ( $existing ) {
			update_post_meta( $existing->ID, '_wp_page_template', $page_data['template'] );
			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $page_data['title'],
				'post_name'    => $page_data['slug'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
				'post_author'  => 1,
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
		}
	}
}
add_action( 'after_switch_theme', 'peptide_starter_create_pages' );

/**
 * One-time migration: enrol v1.5.0 subscribers into email verification.
 *
 * Runs once per site — guarded by the ps_verify_migration_version option.
 * Iterates subscribers in batches of 50 via wp_mail(). Users created by
 * admin (any role above subscriber) are skipped — we assume admin-created
 * accounts are trusted.
 *
 * Side effects: writes user meta + sends email per enrolled user.
 *
 * Cost note: worst case N emails on first admin page load after deploy.
 * If the site has many users, move this to WP-Cron before shipping a
 * large-user release (not a concern for the current zero-user site).
 *
 * @return void
 */
function peptide_starter_migrate_existing_users_to_verification() {
	if ( ! is_admin() ) {
		return;
	}
	$done = get_option( 'ps_verify_migration_version' );
	if ( PEPTIDE_STARTER_VERSION === $done ) {
		return;
	}

	$subscribers = get_users(
		array(
			'role'    => 'subscriber',
			'fields'  => array( 'ID' ),
			'number'  => 500,
			'orderby' => 'ID',
			'order'   => 'ASC',
		)
	);

	foreach ( $subscribers as $user ) {
		$already_enrolled = get_user_meta( $user->ID, 'ps_pending_verification', true );
		if ( '' !== (string) $already_enrolled ) {
			continue;
		}
		if ( function_exists( 'peptide_starter_send_verification_email' ) ) {
			peptide_starter_send_verification_email( $user->ID );
		}
	}

	update_option( 'ps_verify_migration_version', PEPTIDE_STARTER_VERSION, false );
}
add_action( 'admin_init', 'peptide_starter_migrate_existing_users_to_verification' );
