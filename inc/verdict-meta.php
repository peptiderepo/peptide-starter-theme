<?php
/**
 * Verdict System Post Meta Registration & Meta Box
 *
 * Registers post meta for pr_peptide CPT:
 * - verdict_state (required): one of 5 states
 * - signal_row_1, signal_row_2, signal_row_3 (optional): evidence signal labels
 *
 * Adds a meta box to the pr_peptide edit screen for managing these fields.
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register verdict post meta for pr_peptide CPT.
 *
 * @return void
 */
function peptide_starter_register_verdict_meta() {
	$allowed_states = array( 'established', 'promising', 'investigational', 'insufficient', 'cautionary' );

	// Register verdict_state meta.
	register_post_meta(
		'pr_peptide',
		'verdict_state',
		array(
			'type'              => 'string',
			'description'       => 'Verdict classification state',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => '__return_true',
			'sanitize_callback' => function ( $value ) use ( $allowed_states ) {
				return in_array( $value, $allowed_states, true ) ? $value : '';
			},
		)
	);

	// Register signal row meta (up to 3).
	for ( $i = 1; $i <= 3; $i++ ) {
		register_post_meta(
			'pr_peptide',
			'signal_row_' . $i,
			array(
				'type'              => 'string',
				'description'       => 'Evidence signal row ' . $i,
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => '__return_true',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}
}
add_action( 'init', 'peptide_starter_register_verdict_meta' );

/**
 * Add verdict meta box to pr_peptide edit screen.
 *
 * @return void
 */
function peptide_starter_add_verdict_meta_box() {
	add_meta_box(
		'peptide_starter_verdict',
		esc_html__( 'Verdict System', 'peptide-starter' ),
		'peptide_starter_render_verdict_meta_box',
		'pr_peptide',
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

	$verdict_state   = get_post_meta( $post->ID, 'verdict_state', true );
	$signal_row_1    = get_post_meta( $post->ID, 'signal_row_1', true );
	$signal_row_2    = get_post_meta( $post->ID, 'signal_row_2', true );
	$signal_row_3    = get_post_meta( $post->ID, 'signal_row_3', true );
	$allowed_states  = array(
		'established'      => esc_html__( 'Established', 'peptide-starter' ),
		'promising'        => esc_html__( 'Promising', 'peptide-starter' ),
		'investigational'  => esc_html__( 'Investigational', 'peptide-starter' ),
		'insufficient'     => esc_html__( 'Insufficient Evidence', 'peptide-starter' ),
		'cautionary'       => esc_html__( 'Cautionary', 'peptide-starter' ),
	);
	?>
	<div style="padding: 12px 0;">
		<label for="verdict_state" style="display: block; margin-bottom: 8px;">
			<strong><?php esc_html_e( 'Verdict State', 'peptide-starter' ); ?></strong>
		</label>
		<select id="verdict_state" name="verdict_state" style="width: 100%; padding: 8px; font-size: 14px;">
			<option value=""><?php esc_html_e( '— Select —', 'peptide-starter' ); ?></option>
			<?php foreach ( $allowed_states as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $verdict_state, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<div style="margin-top: 20px;">
			<label style="display: block; margin-bottom: 4px;">
				<strong><?php esc_html_e( 'Signal Row 1', 'peptide-starter' ); ?></strong>
			</label>
			<input type="text" name="signal_row_1" value="<?php echo esc_attr( $signal_row_1 ); ?>" placeholder="<?php esc_attr_e( 'e.g., Strong animal evidence', 'peptide-starter' ); ?>" style="width: 100%; padding: 8px; font-size: 14px; margin-bottom: 12px;">

			<label style="display: block; margin-bottom: 4px;">
				<strong><?php esc_html_e( 'Signal Row 2', 'peptide-starter' ); ?></strong>
			</label>
			<input type="text" name="signal_row_2" value="<?php echo esc_attr( $signal_row_2 ); ?>" placeholder="<?php esc_attr_e( 'e.g., Limited human data', 'peptide-starter' ); ?>" style="width: 100%; padding: 8px; font-size: 14px; margin-bottom: 12px;">

			<label style="display: block; margin-bottom: 4px;">
				<strong><?php esc_html_e( 'Signal Row 3', 'peptide-starter' ); ?></strong>
			</label>
			<input type="text" name="signal_row_3" value="<?php echo esc_attr( $signal_row_3 ); ?>" placeholder="<?php esc_attr_e( 'e.g., Regulatory status varies', 'peptide-starter' ); ?>" style="width: 100%; padding: 8px; font-size: 14px;">
		</div>
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
	if ( ! isset( $_POST['peptide_starter_verdict_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['peptide_starter_verdict_nonce'] ), 'peptide_starter_verdict_nonce' ) ) {
		return;
	}

	// Only run for pr_peptide CPT.
	if ( 'pr_peptide' !== $post->post_type ) {
		return;
	}

	// Allow only editors and admins.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed_states = array( 'established', 'promising', 'investigational', 'insufficient', 'cautionary' );

	// Save verdict_state.
	if ( isset( $_POST['verdict_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['verdict_state'] ) );
		if ( in_array( $state, $allowed_states, true ) ) {
			update_post_meta( $post_id, 'verdict_state', $state );
		} else {
			delete_post_meta( $post_id, 'verdict_state' );
		}
	}

	// Save signal rows.
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
	}
}
add_action( 'save_post', 'peptide_starter_save_verdict_meta', 10, 2 );
