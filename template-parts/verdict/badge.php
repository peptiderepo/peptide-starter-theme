<?php
/**
 * Verdict Badge Component
 *
 * Displays a verdict state badge with glyph, label, and colors.
 * Accepts $args['verdict_state'] and $args['show_label'].
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$verdict_state = isset( $args['verdict_state'] ) ? sanitize_text_field( $args['verdict_state'] ) : '';
$show_label    = isset( $args['show_label'] ) ? (bool) $args['show_label'] : true;

// State configuration.
$states = array(
	'established'      => array(
		'glyph' => '✓',
		'label' => 'Established',
	),
	'promising'        => array(
		'glyph' => '◐',
		'label' => 'Promising',
	),
	'investigational'  => array(
		'glyph' => '?',
		'label' => 'Investigational',
	),
	'insufficient'     => array(
		'glyph' => '⊘',
		'label' => 'Insufficient Evidence',
	),
	'cautionary'       => array(
		'glyph' => '⚠',
		'label' => 'Cautionary',
	),
);

// Early exit if invalid state.
if ( ! isset( $states[ $verdict_state ] ) ) {
	return;
}

$state_info = $states[ $verdict_state ];
?>
<span class="pr-verdict pr-verdict--<?php echo esc_attr( $verdict_state ); ?>" role="img" aria-label="<?php echo esc_attr( 'Verdict: ' . $state_info['label'] ); ?>">
	<span class="pr-verdict__glyph" aria-hidden="true"><?php echo $state_info['glyph']; ?></span>
	<?php if ( $show_label ) : ?>
		<span class="pr-verdict__label"><?php echo esc_html( $state_info['label'] ); ?></span>
	<?php endif; ?>
</span>
