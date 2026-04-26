<?php
/**
 * Verdict helper functions for homepage and template parts.
 *
 * What: Provides data arrays for the 5-state verdict taxonomy used in the
 *       homepage explainer strip and featured verdict cards.
 * Who triggers: Required by functions.php on every page load.
 * Depends on: Nothing — pure data functions, no WP dependencies.
 *
 * @see inc/verdict-meta.php      — verdict CPT meta registration
 * @see template-parts/verdict/   — badge, card, evidence-row components
 * @see front-page.php            — homepage verdict sections
 *
 * @package peptide-starter
 */

/**
 * Returns the verdict taxonomy data array for the homepage explainer strip.
 *
 * Canonical order matches CMO brief §5.1 and the 5-state enum in verdict-meta.php.
 *
 * @return array[] Each element: state (string), glyph (string), label (string), description (string).
 */
function peptide_starter_get_verdict_taxonomy() {
	return array(
		array(
			'state'       => 'established',
			'glyph'       => '✓',
			'label'       => 'Established',
			'description' => 'Strong RCT evidence; widely prescribed or validated.',
		),
		array(
			'state'       => 'promising',
			'glyph'       => '◐',
			'label'       => 'Promising',
			'description' => 'Meaningful human data accumulating; not yet RCT-strong.',
		),
		array(
			'state'       => 'investigational',
			'glyph'       => '?',
			'label'       => 'Investigational',
			'description' => 'Mechanism plausible; human trials sparse.',
		),
		array(
			'state'       => 'insufficient',
			'glyph'       => '⊘',
			'label'       => 'Insufficient Evidence',
			'description' => 'Not enough data to say anything responsible.',
		),
		array(
			'state'       => 'cautionary',
			'glyph'       => '⚠',
			'label'       => 'Cautionary',
			'description' => 'Active safety signals or misuse patterns.',
		),
	);
}

/**
 * Returns the verdict state config map (glyph + label per state slug).
 *
 * Used by featured verdict cards on the homepage and any template that needs
 * to render a verdict badge from a state string without loading the full card.
 *
 * @return array Keyed by state slug. Each value: array{ glyph: string, label: string }.
 */
function peptide_starter_get_verdict_config() {
	return array(
		'established'     => array(
			'glyph' => '✓',
			'label' => 'Established',
		),
		'promising'       => array(
			'glyph' => '◐',
			'label' => 'Promising',
		),
		'investigational' => array(
			'glyph' => '?',
			'label' => 'Investigational',
		),
		'insufficient'    => array(
			'glyph' => '⊘',
			'label' => 'Insufficient Evidence',
		),
		'cautionary'      => array(
			'glyph' => '⚠',
			'label' => 'Cautionary',
		),
	);
}
