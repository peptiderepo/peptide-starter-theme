<?php
/**
 * Auto-Create Pages on Theme Activation
 *
 * Creates the required WordPress pages (with correct templates assigned)
 * when the theme is activated, if they don't already exist.
 *
 * @see functions.php — includes this file
 * @see ARCHITECTURE.md — documents the page/template mapping
 *
 * What: Theme activation hook that auto-creates pages for all new templates.
 * Who calls it: WordPress after_switch_theme action.
 * Dependencies: None — uses WordPress core functions only.
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
 * Checks if each page slug exists; creates it if missing with the
 * correct page template assigned. Skips existing pages to avoid duplicates.
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
			// Page exists — ensure the template is assigned.
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
