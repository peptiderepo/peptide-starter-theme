<?php
/**
 * Template part for displaying a single post/page
 *
 * @package peptide-starter
 */

?>
<article <?php post_class( 'ps-card' ); ?> id="post-<?php the_ID(); ?>">
	<header class="entry-header" style="margin-bottom: var(--spacing-lg);">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		}

		if ( 'post' === get_post_type() ) {
			?>
			<div class="entry-meta" style="color: var(--color-text-secondary); font-size: var(--text-body-sm); margin-top: var(--spacing-sm);">
				<?php
				printf(
					esc_html__( 'Posted on %s', 'peptide-starter' ),
					'<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>'
				);
				echo ' ';
				printf(
					esc_html__( 'by %s', 'peptide-starter' ),
					'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
				);
				?>
			</div>
			<?php
		}
		?>
	</header>

	<div class="entry-content" style="margin-bottom: var(--spacing-lg);">
		<?php
		if ( has_post_thumbnail() ) {
			?>
			<div class="ps-featured-image" style="margin-bottom: var(--spacing-lg);">
				<?php the_post_thumbnail( 'full' ); ?>
			</div>
			<?php
		}

		the_content(
			sprintf(
				wp_kses(
					__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'peptide-starter' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post( get_the_title() )
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'peptide-starter' ),
				'after'  => '</div>',
			)
		);
		?>
	</div>

	<?php if ( 'post' === get_post_type() ) { ?>
		<footer class="entry-footer" style="color: var(--color-text-secondary); font-size: var(--text-body-sm); border-top: 1px solid var(--color-border-default); padding-top: var(--spacing-md);">
			<?php
			$categories = get_the_category();
			if ( $categories ) {
				echo '<div style="margin-bottom: var(--spacing-sm);">';
				echo esc_html__( 'Categories: ', 'peptide-starter' );
				foreach ( $categories as $category ) {
					echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" class="ps-badge">' . esc_html( $category->name ) . '</a> ';
				}
				echo '</div>';
			}

			$tags = get_the_tags();
			if ( $tags ) {
				echo '<div>';
				echo esc_html__( 'Tags: ', 'peptide-starter' );
				foreach ( $tags as $tag ) {
					echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="ps-badge">' . esc_html( $tag->name ) . '</a> ';
				}
				echo '</div>';
			}
			?>
		</footer>
	<?php } ?>
</article>
