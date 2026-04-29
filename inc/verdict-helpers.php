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
 * @return array[] Each element: state (string), glyph (string), label (string), description (string), abbrs (array, optional).
 */
function peptide_starter_get_verdict_taxonomy() {
	return array(
		array(
			'state'       => 'established',
			'glyph'       => '✓',
			'label'       => 'Established',
			'description' => 'Strong RCT evidence; widely prescribed or validated.',
			// abbrs: terms in description that should render as inline tap-targets.
			'abbrs'       => array(
				'RCT' => 'Randomized Controlled Trial — the gold standard for clinical evidence. Participants are randomly assigned to treatment or control groups to measure real effect.',
			),
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

/**
 * Renders a description string with optional inline abbreviation tap-targets.
 *
 * What: Builds safe HTML for a verdict card description, replacing known
 *       abbreviation terms with <abbr> elements that trigger a tooltip.
 * Who calls it: front-page.php verdict explainer loop.
 * Depends on: Nothing — pure string transformation.
 *
 * The output is intentionally built from escaped components (not from raw $text)
 * so we never expose unescaped user or data-layer content. Each substitution is:
 *   esc_html( $text ) → str_replace( literal_term, safe_abbr_html )
 * This is safe because the abbr tag, class, and data-def are all hardcoded or
 * escaped here, and the term key is a literal constant from our own data array.
 *
 * @param string $text  Plain-text description string. Will be HTML-escaped.
 * @param array  $abbrs Associative array of term => definition. Keys are literal
 *                      strings (e.g. 'RCT'). Values are plain-text definitions.
 * @return string Safe HTML string suitable for echo without further escaping.
 *
 * @see inc/verdict-helpers.php — $abbrs data lives in peptide_starter_get_verdict_taxonomy()
 * @see assets/js/abbr-tooltip.js — JS that handles tap/click to show the popover
 * @see style.css — .pr-abbr and .pr-abbr-popover styles
 */
function peptide_starter_render_desc( string $text, array $abbrs ): string {
	$html = esc_html( $text );

	foreach ( $abbrs as $term => $definition ) {
		$safe_term = esc_html( $term );
		$safe_def  = esc_attr( $definition );
		$abbr_html = sprintf(
			'<abbr class="pr-abbr" data-def="%s" tabindex="0" role="button" aria-label="%s: %s">%s</abbr>',
			$safe_def,
			$safe_term,
			$safe_def,
			$safe_term
		);
		// Replace only the first occurrence — abbreviations should not stack.
		$html = preg_replace( '/' . preg_quote( $safe_term, '/' ) . '/', $abbr_html, $html, 1 );
	}

	return $html;
}
