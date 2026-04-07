<?php
/**
 * The main template file
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="ps-container">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );
			}
			peptide_starter_pagination();
		} else {
			get_template_part( 'template-parts/content', 'none' );
		}
		?>
	</div>
</main>

<?php get_footer(); ?>
