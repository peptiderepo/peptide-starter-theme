/**
 * abbr-tooltip.js — Inline abbreviation popover for verdict legend screen.
 *
 * What: Opens a small popover anchored to any .pr-abbr element when tapped,
 *       clicked, or activated via keyboard. Closes on outside click or Escape.
 * Who triggers: Enqueued by functions.php on is_front_page() requests only.
 * Depends on: .pr-abbr elements in the DOM with data-def attributes; CSS in
 *             style.css (.pr-abbr-popover).
 *
 * @see inc/verdict-helpers.php  — peptide_starter_render_desc() emits .pr-abbr elements
 * @see style.css                — .pr-abbr, .pr-abbr-popover visual styles
 * @package peptide-starter
 */

( function () {
	'use strict';

	var POPOVER_CLASS  = 'pr-abbr-popover';
	var ACTIVE_CLASS   = 'pr-abbr--active';
	var activePopover  = null;
	var activeAbbr     = null;

	/**
	 * Remove the currently open popover, if any.
	 *
	 * @return {void}
	 */
	function closePopover() {
		if ( activePopover ) {
			activePopover.remove();
			activePopover = null;
		}
		if ( activeAbbr ) {
			activeAbbr.classList.remove( ACTIVE_CLASS );
			activeAbbr.removeAttribute( 'aria-expanded' );
			activeAbbr = null;
		}
	}

	/**
	 * Create and position a popover anchored to the given abbr element.
	 *
	 * @param {HTMLElement} abbr - The .pr-abbr element that was activated.
	 * @return {void}
	 */
	function openPopover( abbr ) {
		closePopover();

		var def = abbr.getAttribute( 'data-def' );
		if ( ! def ) {
			return;
		}

		var popover = document.createElement( 'div' );
		popover.className   = POPOVER_CLASS;
		popover.textContent = def;
		popover.setAttribute( 'role', 'tooltip' );
		popover.setAttribute( 'id', POPOVER_CLASS + '-' + Date.now() );

		document.body.appendChild( popover );

		// Position below the abbr, clamped to viewport width.
		var rect       = abbr.getBoundingClientRect();
		var scrollTop  = window.pageYOffset || document.documentElement.scrollTop;
		var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
		var top        = rect.bottom + scrollTop + 6;
		var left       = rect.left  + scrollLeft;
		var maxLeft    = window.innerWidth - popover.offsetWidth - 16;

		popover.style.top  = top + 'px';
		popover.style.left = Math.max( 8, Math.min( left, maxLeft ) ) + 'px';

		abbr.classList.add( ACTIVE_CLASS );
		abbr.setAttribute( 'aria-expanded', 'true' );
		abbr.setAttribute( 'aria-describedby', popover.id );

		activePopover = popover;
		activeAbbr    = abbr;
	}

	/**
	 * Toggle the popover for an abbr element (open if closed, close if open).
	 *
	 * @param {HTMLElement} abbr - The .pr-abbr element that was activated.
	 * @return {void}
	 */
	function togglePopover( abbr ) {
		if ( activeAbbr === abbr ) {
			closePopover();
		} else {
			openPopover( abbr );
		}
	}

	// -------------------------------------------------------------------------
	// Event listeners
	// -------------------------------------------------------------------------

	document.addEventListener( 'click', function ( e ) {
		var abbr = e.target.closest( '.pr-abbr' );
		if ( abbr ) {
			e.stopPropagation();
			togglePopover( abbr );
			return;
		}
		// Click outside any abbr — close.
		closePopover();
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closePopover();
			return;
		}
		if ( e.key === 'Enter' || e.key === ' ' ) {
			var abbr = e.target.closest( '.pr-abbr' );
			if ( abbr ) {
				e.preventDefault();
				togglePopover( abbr );
			}
		}
	} );

	// Close when focus leaves the popover+abbr pair entirely.
	document.addEventListener( 'focusin', function ( e ) {
		if (
			activeAbbr &&
			! activeAbbr.contains( e.target ) &&
			( ! activePopover || ! activePopover.contains( e.target ) )
		) {
			closePopover();
		}
	} );
} )();
