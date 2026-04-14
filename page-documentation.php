<?php
/**
 * Template Name: Documentation
 *
 * Page template for the documentation / SOP page.
 * Renders WordPress page content with a sticky sidebar ToC generated from
 * heading structure via JavaScript (documentation.js).
 *
 * @see assets/js/documentation.js — ToC generation + scroll spy
 * @see functions.php — conditional enqueue of documentation.js
 *
 * What: Two-column layout (sidebar ToC + long-form content) for documentation.
 * Who calls it: WordPress template hierarchy when a page uses this template.
 * Dependencies: None (content comes from WordPress editor).
 *
 * @package peptide-starter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main">
	<section class="ps-tool-page ps-docs-layout">
		<!-- Sidebar Table of Contents (populated by documentation.js) -->
		<aside class="ps-docs-sidebar" aria-label="<?php esc_attr_e( 'On this page', 'peptide-starter' ); ?>">
			<div class="ps-docs-sidebar-inner">
				<h2 class="ps-docs-toc-title"><?php esc_html_e( 'On This Page', 'peptide-starter' ); ?></h2>
				<nav class="ps-docs-toc" aria-label="<?php esc_attr_e( 'Table of contents', 'peptide-starter' ); ?>">
					<!-- JS populates this from h2 headings in content -->
					<ul id="ps-docs-toc-list"></ul>
				</nav>
			</div>
		</aside>

		<!-- Main Documentation Content -->
		<div class="ps-docs-content">
			<div class="ps-container">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<header class="ps-docs-header">
						<h1><?php the_title(); ?></h1>
						<?php if ( has_excerpt() ) : ?>
							<p class="ps-docs-intro"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</header>

					<div class="ps-docs-body" id="ps-docs-body">
						<?php the_content(); ?>
					</div>
					<?php
				endwhile;
				?>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
