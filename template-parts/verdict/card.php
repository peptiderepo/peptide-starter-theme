<?php
/**
 * Verdict Card Component (Monograph Hero)
 *
 * Displays a full verdict card for a pr_peptide post.
 * Reads verdict_state and signal_row_1/2/3 from post meta.
 * Accepts $args['post_id'].
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();

// Get verdict state from post meta.
$verdict_state = get_post_meta( $post_id, 'verdict_state', true );

// Early exit if no verdict state is set.
if ( empty( $verdict_state ) ) {
	return;
}

// Collect signal rows.
$signals = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$signal_text = get_post_meta( $post_id, 'signal_row_' . $i, true );
	if ( ! empty( $signal_text ) ) {
		$signals[] = array(
			'glyph' => $i === 1 ? '✓' : ( $i === 2 ? '!' : '?' ),
			'label' => $signal_text,
		);
	}
}

$post = get_post( $post_id );
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
		<time class="pr-verdict-card__updated" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">
			<?php echo esc_html( 'Updated ' . get_the_date( 'M d, Y', $post_id ) ); ?>
		</time>
	</div>

	<h1 class="pr-verdict-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

	<p class="pr-verdict-card__summary">
		<?php
		// Get post excerpt or first 100 chars of content.
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
		<?php
		// Placeholder verdict text; can be extended with a custom meta field.
		echo esc_html__( 'This peptide merits careful scientific review. Please consult with a healthcare provider before use.', 'peptide-starter' );
		?>
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
