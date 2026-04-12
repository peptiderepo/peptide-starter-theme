<?php
/**
 * Template part for displaying content when nothing is found
 *
 * @package peptide-starter
 */

?>
<div class="ps-card">
	<header class="entry-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'peptide-starter' ); ?></h1>
	</header>

	<div class="entry-content">
		<?php
		if ( is_search() ) {
			?>
			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'peptide-starter' ); ?></p>
			<?php
			get_search_form();
		} elseif ( is_home() && current_user_can( 'publish_posts' ) ) {
			?>
			<p>
				<?php
				printf(
					wp_kses(
						__( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'peptide-starter' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
			<?php
		} elseif ( is_archive() ) {
			?>
			<p><?php esc_html_e( 'It looks like there are no posts for this archive. Try a different category or date.', 'peptide-starter' ); ?></p>
			<?php
		} else {
			?>
			<p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'peptide-starter' ); ?></p>
			<?php
			get_search_form();
		}
		?>
	</div>
</div>
