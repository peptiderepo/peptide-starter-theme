<?php
/**
 * Template Part: Research Module Cards
 *
 * Renders a 5-card grid of research modules (tools/sections) for the front page.
 * Each card checks if the target plugin is active; inactive cards are dimmed.
 * Cards with 'coming_soon' => true display a "Coming soon" badge overlaid in
 * the top-right corner — layered on top of the --inactive visual treatment.
 *
 * @see front-page.php — includes this template part
 * @see style.css — .ps-module-* classes
 *
 * What: 3-column grid of research tool cards with SVG icons.
 * Who calls it: get_template_part( 'template-parts/module', 'cards' ).
 * Dependencies: None — all output is self-contained.
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Module card definitions with shortcode checks for graceful degradation.
// 'coming_soon' => true adds a "Coming soon" badge without removing existing
// --inactive styling — the badge layers on top of the dim/no-hover treatment.
$modules = array(
	array(
		'title'       => __( 'Peptides', 'peptide-starter' ),
		'description' => __( 'Read the verdicts. 20 monographs across 5 evidence states, growing weekly.', 'peptide-starter' ),
		'url'         => '/peptides',
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L12 22"/><path d="M5 12C5 8.13 8.13 5 12 5s7 3.13 7 7-3.13 7-7 7-7-3.13-7-7z"/><circle cx="12" cy="8" r="1.5" fill="currentColor" stroke="none"/><circle cx="12" cy="16" r="1.5" fill="currentColor" stroke="none"/></svg>',
		'available'   => shortcode_exists( 'peptide_directory' ) || post_type_exists( 'peptide' ),
		'coming_soon' => false,
	),
	array(
		'title'       => __( 'Calculator', 'peptide-starter' ),
		'description' => __( 'Reconstitute a peptide accurately. Enter the vial, the bacteriostatic water, the target dose; get the units.', 'peptide-starter' ),
		'url'         => '/calculator',
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="14" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="16" y2="18"/></svg>',
		'available'   => shortcode_exists( 'prc_calculator' ),
		'coming_soon' => false,
	),
	array(
		'title'       => __( 'Protocol Builder', 'peptide-starter' ),
		'description' => __( 'Build a protocol you can hand to anyone. Doses, schedule, notes, reviewable history.', 'peptide-starter' ),
		'url'         => '/protocol-builder',
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>',
		'available'   => shortcode_exists( 'peptide_tools_protocol_builder' ),
		'coming_soon' => true,
	),
	array(
		'title'       => __( 'Tracker', 'peptide-starter' ),
		'description' => __( 'Log doses and timing. For people running their own protocols, and people supervising someone else\'s.', 'peptide-starter' ),
		'url'         => '/tracker',
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="3" y1="20" x2="21" y2="20"/></svg>',
		'available'   => shortcode_exists( 'peptide_tracker' ),
		'coming_soon' => true,
	),
	array(
		'title'       => __( 'Subject Log', 'peptide-starter' ),
		'description' => __( 'Track outcomes over time. Subjective markers, lab results, protocol changes — your own data, kept private.', 'peptide-starter' ),
		'url'         => '/subject-log',
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'available'   => shortcode_exists( 'peptide_tracker_subject_log' ),
		'coming_soon' => true,
	),
);
?>

<section class="ps-modules-section">
	<div class="ps-container-wide">
		<div class="ps-modules-header">
			<h2 class="ps-modules-title"><?php esc_html_e( 'Tools', 'peptide-starter' ); ?></h2>
			<p class="ps-modules-subtitle">
				<?php esc_html_e( 'Free to use. Built for people running their own protocols, and people deciding whether they should.', 'peptide-starter' ); ?>
			</p>
		</div>

		<div class="ps-modules-grid">
			<?php foreach ( $modules as $module ) : ?>
				<a href="<?php echo esc_url( home_url( $module['url'] ) ); ?>"
				   class="ps-module-card <?php echo $module['available'] ? '' : 'ps-module-card--inactive'; ?>">
					<?php if ( ! empty( $module['coming_soon'] ) ) : ?>
						<span class="ps-module-card__badge"><?php esc_html_e( 'Coming soon', 'peptide-starter' ); ?></span>
					<?php endif; ?>
					<div class="ps-module-card__icon">
						<?php
						// SVG icons are defined in this template — safe to output directly.
						echo $module['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG
						?>
					</div>
					<h3 class="ps-module-card__title"><?php echo esc_html( $module['title'] ); ?></h3>
					<p class="ps-module-card__description"><?php echo esc_html( $module['description'] ); ?></p>
					<span class="ps-module-card__link">
						<?php esc_html_e( 'Open', 'peptide-starter' ); ?> &rarr;
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

