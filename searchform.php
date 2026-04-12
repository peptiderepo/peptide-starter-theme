<?php
/**
 * The search form template
 *
 * @package peptide-starter
 */

?>
<form role="search" method="get" class="ps-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="search" class="sr-only"><?php esc_html_e( 'Search', 'peptide-starter' ); ?></label>
	<div style="position: relative;">
		<input
			type="search"
			id="search"
			name="s"
			class="ps-search-input"
			placeholder="<?php esc_attr_e( 'Search...', 'peptide-starter' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
		>
		<button type="submit" class="ps-btn ps-btn-primary" style="position: absolute; right: 0; top: 0; border-radius: 0 var(--radius-lg) var(--radius-lg) 0;">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 17C13.4183 17 17 13.4183 17 9C17 4.58172 13.4183 1 9 1C4.58172 1 1 4.58172 1 9C1 13.4183 4.58172 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19 19L14.65 14.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
	</div>
</form>
