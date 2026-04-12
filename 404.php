<?php
/**
 * The 404 template
 *
 * @package peptide-starter
 */

get_header();
?>

<main id="main" class="site-main">
	<div class="ps-container">
		<div class="error-404 not-found">
			<h1><?php esc_html_e( '404 - Page Not Found', 'peptide-starter' ); ?></h1>
			<p><?php esc_html_e( "Sorry, the page you're looking for doesn't exist. It might have been moved or deleted.", 'peptide-starter' ); ?></p>

			<form role="search" method="get" class="search-form ps-404-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label>
					<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'peptide-starter' ); ?></span>
					<input type="search" class="search-submit" placeholder="<?php esc_attr_e( 'Search peptides...', 'peptide-starter' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" title="<?php esc_attr_e( 'Search for:', 'peptide-starter' ); ?>" />
				</label>
			</form>

			<div class="ps-404-cta-group">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ps-btn ps-btn-primary">
					<?php esc_html_e( 'Back to Home', 'peptide-starter' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/peptides' ) ); ?>" class="ps-btn ps-btn-secondary">
					<?php esc_html_e( 'Browse Peptides', 'peptide-starter' ); ?>
				</a>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>
