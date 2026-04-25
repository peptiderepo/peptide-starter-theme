<?php
/**
 * Affiliate Disclosure Component
 *
 * Displays FTC-compliant disclosure about affiliate commissions.
 * Three contexts: inline (default), banner, footer.
 * Accepts $args['context'].
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context = isset( $args['context'] ) ? sanitize_text_field( $args['context'] ) : 'inline';

// Copy variants (verbatim per CMO spec).
$inline_copy = 'If you buy through this link, the seller pays Peptide Repo a commission. We don\'t pay to feature any peptide, and our verdicts don\'t change based on who pays.';

$banner_copy = 'Peptide Repo earns a commission when readers buy through links to our fulfillment partners. We are funded by those commissions, not by the peptide brands themselves. No peptide on this site appears here because someone paid us. Our verdicts are decided before we negotiate any partnership, and we drop partners whose product or process degrades.';

$footer_copy = 'Peptide Repo earns a commission on some links. <a href="' . esc_url( home_url( '/about/' ) ) . '#disclosures">' . esc_html__( 'How we work', 'peptide-starter' ) . ' →</a>';

switch ( $context ) {
	case 'banner':
		?>
		<aside class="pr-disclosure pr-disclosure--banner" aria-label="<?php esc_attr_e( 'Affiliate disclosure', 'peptide-starter' ); ?>">
			<span class="pr-disclosure__label"><?php esc_html_e( 'A note on commerce.', 'peptide-starter' ); ?></span>
			<?php echo wp_kses_post( $banner_copy ); ?>
		</aside>
		<?php
		break;

	case 'footer':
		?>
		<p class="pr-disclosure pr-disclosure--footer">
			<?php echo wp_kses_post( $footer_copy ); ?>
		</p>
		<?php
		break;

	case 'inline':
	default:
		?>
		<aside class="pr-disclosure pr-disclosure--inline" aria-label="<?php esc_attr_e( 'Affiliate disclosure', 'peptide-starter' ); ?>">
			<span class="pr-disclosure__label"><?php esc_html_e( 'How we make money:', 'peptide-starter' ); ?></span>
			<?php echo wp_kses_post( $inline_copy ); ?>
		</aside>
		<?php
		break;
}
