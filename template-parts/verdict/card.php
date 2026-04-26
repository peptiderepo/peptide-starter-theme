<?php
/**
 * Verdict Card Component (Monograph Hero)
 *
 * Displays the full verdict card hero for a peptide post.
 * Reads verdict_state, verdict_text, signal_row_1/2/3, and
 * signal_row_1/2/3_glyph from post meta.
 *
 * Who calls it: single-peptide.php for posts with a verdict_state.
 * Depends on: inc/verdict-meta.php (meta registration),
 *             template-parts/verdict/badge.php,
 *             template-parts/verdict/evidence-row.php.
 *
 * @see inc/verdict-meta.php
 * @see single-peptide.php
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();

// Get verdict state; bail early if unset.
$verdict_state = get_post_meta( $post_id, 'verdict_state', true );
if ( empty( $verdict_state ) ) {
	return;
}

// Get opinionated verdict text; fall back to placeholder if not yet authored.
$verdict_text = get_post_meta( $post_id, 'verdict_text', true );
if ( empty( $verdict_text ) ) {
	$verdict_text = __( 'This peptide merits careful scientific review. Please consult with a healthcare provider before use.', 'peptide-starter' );
}

// Collect signal rows with their per-row glyphs.
// Glyph comes from the signal_row_N_glyph meta field; if unset, no glyph is
// rendered. This is intentional — a missing glyph is better than a wrong one.
$signals = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$signal_text  = get_post_meta( $post_id, 'signal_row_' . $i, true );
	$signal_glyph = get_post_meta( $post_id, 'signal_row_' . $i . '_glyph', true );
	if ( ! empty( $signal_text ) ) {
		$signals[] = array(
			'glyph' => $signal_glyph, // May be empty string; evidence-row handles that.
			'label' => $signal_text,
		);
	}
}
?>
<div class="pr-verdict-card">
	<div class="pr-verdict-card__header">
		<div>
			<?php
			get_template_part(
				'template-parts/verdict/badge',
				null,
				array(
					'verdict_state' => $verdict_state,
					'show_label'    => true,
				)
			);
			?>
		</div>
		<!-- last_reviewed_date timestamp deferred to v1.1 — WP post date != editorial review date -->
	</div>

	<h1 class="pr-verdict-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

	<p class="pr-verdict-card__summary">
		<?php
		$summary = get_the_excerpt( $post_id );
		if ( empty( $summary ) ) {
			$content = get_the_content( null, false, $post_id );
			$summary = wp_trim_words( $content, 20 );
		}
		echo wp_kses_post( $summary );
		?>
	</p>

	<?php if ( ! empty( $signals ) ) : ?>
		<?php
		get_template_part(
			'template-parts/verdict/evidence-row',
			null,
			array( 'signals' => $signals )
		);
		?>
	<?php endif; ?>

	<div class="pr-verdict-card__verdict-label"><?php esc_html_e( 'Our verdict:', 'peptide-starter' ); ?></div>
	<div class="pr-verdict-card__verdict-text">
		<?php echo wp_kses_post( nl2br( $verdict_text ) ); ?>
	</div>

	<div class="pr-verdict-card__cta-group">
		<a href="#evidence" class="pr-verdict-card__cta-link">
			<?php esc_html_e( 'Read the full evidence', 'peptide-starter' ); ?> →
		</a>
		<a href="#dosing" class="pr-verdict-card__cta-link">
			<?php esc_html_e( 'Jump to dosing', 'peptide-starter' ); ?> →
		</a>
	</div>
</div>
