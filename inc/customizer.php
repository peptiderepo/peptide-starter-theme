<?php
/**
 * Theme Customizer Settings
 *
 * Registers all Customizer sections, settings, and controls for
 * the theme: branding, hero, footer, and dark mode.
 *
 * @see functions.php — includes this file
 * @see front-page.php — reads hero settings
 * @see footer.php — reads footer copyright
 *
 * What: Customizer API registration for theme options.
 * Who calls it: WordPress customize_register action.
 * Dependencies: WordPress Customizer API.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme customizer options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 * @return void
 */
function peptide_starter_customize_register( $wp_customize ) {
	// Branding section.
	$wp_customize->add_section(
		'peptide_starter_branding',
		array(
			'title'    => esc_html__( 'Branding', 'peptide-starter' ),
			'priority' => 30,
		)
	);

	// Hero settings section.
	$wp_customize->add_section(
		'peptide_starter_hero',
		array(
			'title'    => esc_html__( 'Hero Section', 'peptide-starter' ),
			'priority' => 40,
		)
	);

	// Hero Title.
	$wp_customize->add_setting(
		'hero_title',
		array(
			'default'           => get_bloginfo( 'name' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'hero_title',
		array(
			'label'   => esc_html__( 'Hero Title', 'peptide-starter' ),
			'section' => 'peptide_starter_hero',
			'type'    => 'text',
		)
	);

	// Hero Subtitle.
	$wp_customize->add_setting(
		'hero_subtitle',
		array(
			'default'           => esc_html__( 'A scientific peptide reference database', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'hero_subtitle',
		array(
			'label'   => esc_html__( 'Hero Subtitle', 'peptide-starter' ),
			'section' => 'peptide_starter_hero',
			'type'    => 'textarea',
		)
	);

	// Hero Search Placeholder.
	$wp_customize->add_setting(
		'hero_search_placeholder',
		array(
			'default'           => esc_html__( 'Search peptides, sequences, or research...', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'hero_search_placeholder',
		array(
			'label'   => esc_html__( 'Search Placeholder', 'peptide-starter' ),
			'section' => 'peptide_starter_hero',
			'type'    => 'text',
		)
	);

	// Footer section.
	$wp_customize->add_section(
		'peptide_starter_footer',
		array(
			'title'    => esc_html__( 'Footer', 'peptide-starter' ),
			'priority' => 50,
		)
	);

	// Footer Copyright.
	$wp_customize->add_setting(
		'footer_copyright',
		array(
			'default'           => esc_html__( 'Copyright © 2026 Peptide Repo. All rights reserved.', 'peptide-starter' ),
			'transport'         => 'postMessage',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'footer_copyright',
		array(
			'label'   => esc_html__( 'Copyright Text', 'peptide-starter' ),
			'section' => 'peptide_starter_footer',
			'type'    => 'textarea',
		)
	);

	// Dark mode section.
	$wp_customize->add_section(
		'peptide_starter_theme_mode',
		array(
			'title'    => esc_html__( 'Theme Mode', 'peptide-starter' ),
			'priority' => 60,
		)
	);

	// Dark mode default.
	$wp_customize->add_setting(
		'dark_mode_default',
		array(
			'default'           => false,
			'transport'         => 'refresh',
			'sanitize_callback' => 'peptide_starter_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'dark_mode_default',
		array(
			'label'   => esc_html__( 'Enable Dark Mode by Default', 'peptide-starter' ),
			'section' => 'peptide_starter_theme_mode',
			'type'    => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'peptide_starter_customize_register' );

/**
 * Sanitize checkbox values for the Customizer.
 *
 * @param mixed $checked The raw value.
 * @return bool True if checked, false otherwise.
 */
function peptide_starter_sanitize_checkbox( $checked ) {
	return ( isset( $checked ) && true === $checked ) ? true : false;
}

/**
 * Add dark mode body class if enabled.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function peptide_starter_dark_mode_body_class( $classes ) {
	if ( get_theme_mod( 'dark_mode_default', false ) ) {
		$classes[] = 'dark-mode-default';
	}
	return $classes;
}
add_filter( 'body_class', 'peptide_starter_dark_mode_body_class' );
