<?php
/**
 * Verdict System Post Meta Registration & Meta Box
 *
 * Registers post meta for peptide CPT:
 * - verdict_state (required): one of 5 states
 * - verdict_text (required for launch): 3–5 sentence opinionated verdict
 * - signal_row_1/2/3 (optional): evidence signal label text
 * - signal_row_1/2/3_glyph (optional): per-row glyph; enum ✓|!|?|⊘|⚠
 * - has_partner_link (bool, default false): controls inline disclosure render
 *
 * Who calls it: loaded by functions.php via require_once.
 * Depends on: nothing — pure WP register_post_meta.
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register verdict post meta for peptide CPT.
 *
 * @return void
 */
function peptide_starter_register_verdict_meta() {
	$allowed_states  = array( 'established', 'promising', 'investigational', 'insufficient', 'cautionary' );
	$allowed_glyphs  = array( '✓', '!', '?', '⊘', '⚠' );

	// Register verdict_state meta.
	register_post_meta(
		'peptide',
		'verdict_state',
		array(
			'type'              => 'string',
						'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => '__return_true',
			'sanitize_callback' => function ( $value ) use ( $allowed_states ) {
				return in_array( $value, $allowed_states, true ) ? $value : '';
			},
		)
	);

	// Register verdict_text meta — 3–5 sentence opinionated verdict paragraph.
	register_post_meta(
		'peptide',
		'verdict_text',
		array(
			'type'              => 'string',
						'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => '__return_true',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	// Register signal row text meta (up to 3 rows).
	for ( $i = 1; $i <= 3; $i++ ) {
		register_post_meta(
			'peptide',
			'signal_row_' . $i,
			array(
				'type'              => 'string',
								'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => '__return_true',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Register per-row glyph meta.
		// Validated as one of the 5-state glyph set to keep visual vocabulary consistent.
		register_post_meta(
			'peptide',
			'signal_row_' . $i . '_glyph',
			array(
				'type'              => 'string',
								'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => '__return_true',
				'sanitize_callback' => function ( $value ) use ( $allowed_glyphs ) {
									return ( '' === $value || in_array( $value, $allowed_glyphs, true ) ) ? $value : '';
				},
			)
		);
	}

	// Register has_partner_link — gates inline affiliate disclosure rendering.
	// Default false: disclosure is suppressed unless a partner link is explicitly present.
	register_post_meta(
		'peptide',
		'has_partner_link',
		array(
			'type'          => 'boolean',
						'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => '__return_true',
			'default'       => false,
		)
	);
}
add_action( 'init', 'peptide_starter_register_verdict_meta' );

/**
 * Add verdict meta box to peptide CPT edit screen.
 *
 * @return void
 */
function peptide_starter_add_verdict_meta_box() {
	add_meta_box(
		'peptide_starter_verdict',
		esc_html__( 'Verdict System', 'peptide-starter' ),
		'peptide_starter_render_verdict_meta_box',
		'peptide',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'peptide_starter_add_verdict_meta_box' );

/**
 * Render verdict meta box HTML.
 *
 * @param WP_Post $post The post object.
 * @return void
 */
function peptide_starter_render_verdict_meta_box( $post ) {
	wp_nonce_field( 'peptide_starter_verdict_nonce', 'peptide_starter_verdict_nonce' );

	$verdict_state  = get_post_meta( $post->ID, 'verdict_state', true );
	$verdict_text   = get_post_meta( $post->ID, 'verdict_text', true );
	$allowed_states = array(
		'established'     => esc_html__( 'Established', 'peptide-starter' ),
		'promising'       => esc_html__( 'Promising', 'peptide-starter' ),
		'investigational' => esc_html__( 'Investigational', 'peptide-starter' ),
		'insufficient'    => esc_html__( 'Insufficient Evidence', 'peptide-starter' ),
		'cautionary'      => esc_html__( 'Cautionary', 'peptide-starter' ),
	);
	$allowed_glyphs = array( '✓', '!', '?', '⊘', '⚠' );
	$has_partner    = (bool) get_post_meta( $post->ID, 'has_partner_link', true );
	?>
	<div style="padding:12px 0;">

		<label for="verdict_state" style="display:block;margin-bottom:6px;">
			<strong><?php esc_html_e( 'Verdict State', 'peptide-starter' ); ?></strong>
		</label>
		<select id="verdict_state" name="verdict_state" style="width:100%;padding:8px;font-size:14px;margin-bottom:16px;">
			<option value=""><?php esc_html_e( '— Select —', 'peptide-starter' ); ?></option>
			<?php foreach ( $allowed_states as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $verdict_state, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<label for="verdict_text" style="display:block;margin-bottom:6px;">
			<strong><?php esc_html_e( 'Our Verdict (3–5 sentences)', 'peptide-starter' ); ?></strong>
			<span style="font-weight:normal;font-size:12px;color:#666;display:block;margin-top:2px;">
				<?php esc_html_e( 'The opinionated verdict paragraph shown on the card hero. Be specific — this is the brand\'s editorial spine.', 'peptide-starter' ); ?>
			</span>
		</label>
		<textarea id="verdict_text" name="verdict_text" rows="5"
			style="width:100%;padding:8px;font-size:14px;margin-bottom:16px;"
			placeholder="<?php esc_attr_e( 'e.g., Semaglutide works. The data is among the strongest of any peptide therapy...', 'peptide-starter' ); ?>"><?php echo esc_textarea( $verdict_text ); ?></textarea>

		<hr style="border:none;border-top:1px solid #ddd;margin:8px 0 16px;">
		<p style="font-weight:bold;margin-bottom:8px;"><?php esc_html_e( 'Evidence Signal Rows', 'peptide-starter' ); ?></p>

		<?php for ( $i = 1; $i <= 3; $i++ ) :
			$row_text  = get_post_meta( $post->ID, 'signal_row_' . $i, true );
			$row_glyph = get_post_meta( $post->ID, 'signal_row_' . $i . '_glyph', true );
			?>
			<div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:12px;">
				<div style="flex:0 0 80px;">
					<label for="signal_row_<?php echo esc_attr( $i ); ?>_glyph"
						style="display:block;margin-bottom:4px;font-size:12px;color:#444;">
						<?php esc_html_e( 'Glyph', 'peptide-starter' ); ?>
					</label>
					<select id="signal_row_<?php echo esc_attr( $i ); ?>_glyph"
						name="signal_row_<?php echo esc_attr( $i ); ?>_glyph"
						style="width:100%;padding:6px;font-size:16px;">
						<option value="" <?php selected( $row_glyph, '' ); ?>>—</option>
						<?php foreach ( $allowed_glyphs as $g ) : ?>
							<option value="<?php echo esc_attr( $g ); ?>" <?php selected( $row_glyph, $g ); ?>>
								<?php echo esc_html( $g ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="flex:1;">
					<label for="signal_row_<?php echo esc_attr( $i ); ?>"
						style="display:block;margin-bottom:4px;font-size:12px;color:#444;">
						<?php
						/* translators: %d is the row number */
						echo esc_html( sprintf( __( 'Row %d label', 'peptide-starter' ), $i ) );
						?>
					</label>
					<input type="text" id="signal_row_<?php echo esc_attr( $i ); ?>"
						name="signal_row_<?php echo esc_attr( $i ); ?>"
						value="<?php echo esc_attr( $row_text ); ?>"
						style="width:100%;padding:8px;font-size:14px;"
						placeholder="<?php esc_attr_e( 'Evidence signal label', 'peptide-starter' ); ?>">
				</div>
			</div>
		<?php endfor; ?>

		<hr style="border:none;border-top:1px solid #ddd;margin:8px 0 16px;">
		<label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
			<input type="checkbox" name="has_partner_link" value="1"
				<?php checked( $has_partner, true ); ?> style="width:16px;height:16px;">
			<span>
				<strong><?php esc_html_e( 'Has active partner link', 'peptide-starter' ); ?></strong>
				<span style="display:block;font-size:12px;color:#666;margin-top:2px;">
					<?php esc_html_e( 'Check this when the monograph contains an affiliate partner link. Controls the inline disclosure strip.', 'peptide-starter' ); ?>
				</span>
			</span>
		</label>

	</div>
	<?php
}

/**
 * Save verdict meta on post save.
 *
 * @param int     $post_id The post ID.
 * @param WP_Post $post    The post object.
 * @return void
 */
function peptide_starter_save_verdict_meta( $post_id, $post ) {
	// Verify nonce.
	if ( ! isset( $_POST['peptide_starter_verdict_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['peptide_starter_verdict_nonce'] ), 'peptide_starter_verdict_nonce' )
	) {
		return;
	}

	// Only run for peptide CPT.
	if ( 'peptide' !== $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed_states = array( 'established', 'promising', 'investigational', 'insufficient', 'cautionary' );
	$allowed_glyphs = array( '✓', '!', '?', '⊘', '⚠' );

	// Save verdict_state.
	if ( isset( $_POST['verdict_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['verdict_state'] ) );
		if ( in_array( $state, $allowed_states, true ) ) {
			update_post_meta( $post_id, 'verdict_state', $state );
		} else {
			delete_post_meta( $post_id, 'verdict_state' );
		}
	}

	// Save verdict_text.
	if ( isset( $_POST['verdict_text'] ) ) {
		$text = sanitize_textarea_field( wp_unslash( $_POST['verdict_text'] ) );
		if ( ! empty( $text ) ) {
			update_post_meta( $post_id, 'verdict_text', $text );
		} else {
			delete_post_meta( $post_id, 'verdict_text' );
		}
	}

	// Save signal rows and their per-row glyphs.
	for ( $i = 1; $i <= 3; $i++ ) {
		$field_name = 'signal_row_' . $i;
		if ( isset( $_POST[ $field_name ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
			if ( ! empty( $value ) ) {
				update_post_meta( $post_id, $field_name, $value );
			} else {
				delete_post_meta( $post_id, $field_name );
			}
		}

		$glyph_field = 'signal_row_' . $i . '_glyph';
		if ( isset( $_POST[ $glyph_field ] ) ) {
			$glyph = wp_unslash( $_POST[ $glyph_field ] );
			if ( in_array( $glyph, $allowed_glyphs, true ) ) {
				update_post_meta( $post_id, $glyph_field, $glyph );
			} else {
				delete_post_meta( $post_id, $glyph_field );
			}
		}
	}

	// Save has_partner_link (checkbox — present = true, absent = false).
	$has_partner = isset( $_POST['has_partner_link'] ) && '1' === $_POST['has_partner_link'];
	update_post_meta( $post_id, 'has_partner_link', $has_partner ? '1' : '0' );
}
add_action( 'save_post', 'peptide_starter_save_verdict_meta', 10, 2 );
