<?php
/**
 * Verdict Evidence Signal Row Component
 *
 * Displays an inset row of evidence signals with glyphs.
 * Accepts $args['signals'] as array of ['glyph' => '...', 'label' => '...']
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$signals = isset( $args['signals'] ) && is_array( $args['signals'] ) ? $args['signals'] : array();

if ( empty( $signals ) ) {
	return;
}
?>
<div class="pr-evidence-row">
	<?php foreach ( $signals as $signal ) : ?>
		<?php
		$glyph = isset( $signal['glyph'] ) ? sanitize_text_field( $signal['glyph'] ) : '';
		$label = isset( $signal['label'] ) ? sanitize_text_field( $signal['label'] ) : '';
		?>
		<?php if ( ! empty( $glyph ) && ! empty( $label ) ) : ?>
			<div class="pr-evidence-row__item">
				<span class="pr-evidence-row__glyph"><?php echo esc_html( $glyph ); ?></span>
				<span><?php echo esc_html( $label ); ?></span>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
