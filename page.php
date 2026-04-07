<?php
/**
 * The page template file
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<!-- Page Header -->
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<header class="page-header">
				<div class="ps-container">
					<h1 class="page-title"><?php the_title(); ?></h1>
				</div>
			</header>

			<!-- Page Content -->
			<div class="page-content">
				<article class="post" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php
					the_content();

					// Display edit link
					edit_post_link(
						sprintf(
							wp_kses(
								__( 'Edit <span class="screen-reader-text">%s</span>', 'peptide-starter' ),
								array( 'span' => array( 'class' => array() ) )
							),
							get_the_title()
						),
						'<div class="edit-link">',
						'</div>'
					);
					?>
				</article>
			</div>
			<?php
		}
	} else {
		get_template_part( 'template-parts/content', 'none' );
	}
	?>
</main>

<?php get_footer(); ?>
