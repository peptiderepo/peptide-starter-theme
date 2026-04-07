<?php
/**
 * Template part for displaying peptide cards in archive views
 *
 * @package peptide-starter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'peptide-card' ); ?>>
	<header class="peptide-card__header">
		<h2 class="peptide-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<?php
		$categories = get_the_terms( get_the_ID(), 'peptide-category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			echo '<div class="peptide-card__meta">';
			foreach ( array_slice( $categories, 0, 2 ) as $cat ) {
				echo '<span class="peptide-card__badge">' . esc_html( $cat->name ) . '</span>';
			}
			echo '</div>';
		}
		?>
	</header>

	<div class="peptide-card__excerpt">
		<?php
		if ( has_excerpt() ) {
			echo wp_kses_post( wp_trim_words( get_the_excerpt(), 25, '&hellip;' ) );
		} else {
			echo wp_kses_post( wp_trim_words( get_the_content(), 25, '&hellip;' ) );
		}
		?>
	</div>

	<footer class="peptide-card__footer">
		<a href="<?php the_permalink(); ?>" class="peptide-card__link">
			<?php esc_html_e( 'Learn More', 'peptide-starter' ); ?>
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path d="M3 8H13M13 8L9 4M13 8L9 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
	</footer>
</article>
