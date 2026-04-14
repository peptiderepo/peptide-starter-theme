/**
 * Documentation Page — Table of Contents & Scroll Spy
 * Peptide Starter Theme
 *
 * Handles:
 * - Auto-generates ToC from h2 headings in the documentation body
 * - Scroll spy highlights the active section in the sidebar
 * - Smooth scrolling on ToC link click
 * - Collapses sidebar to dropdown on mobile
 *
 * @see page-documentation.php — renders the two-column layout
 * @see style.css — .ps-docs-* classes
 *
 * Conditionally enqueued only on pages using the Documentation template.
 */

(function() {
	'use strict';

	const docsBody   = document.getElementById('ps-docs-body');
	const tocList    = document.getElementById('ps-docs-toc-list');
	const sidebar    = document.querySelector('.ps-docs-sidebar');

	if (!docsBody || !tocList) {
		return;
	}

	const headings = docsBody.querySelectorAll('h2');

	if (headings.length === 0) {
		// Hide the sidebar if there are no headings to navigate.
		if (sidebar) {
			sidebar.style.display = 'none';
		}
		return;
	}

	/**
	 * Build the Table of Contents from h2 headings.
	 * Assigns IDs to headings that lack one for anchor linking.
	 */
	function buildToc() {
		headings.forEach(function(heading, index) {
			// Ensure every heading has an ID for anchor links.
			if (!heading.id) {
				heading.id = 'section-' + (index + 1);
			}

			var li   = document.createElement('li');
			var link = document.createElement('a');

			link.href        = '#' + heading.id;
			link.textContent = heading.textContent;
			link.className   = 'ps-docs-toc-link';

			link.addEventListener('click', function(e) {
				e.preventDefault();
				smoothScrollTo(heading);
			});

			li.appendChild(link);
			tocList.appendChild(li);
		});
	}

	/**
	 * Smooth scroll to a target element, accounting for the sticky header.
	 * @param {HTMLElement} target The element to scroll to.
	 */
	function smoothScrollTo(target) {
		var headerHeight = document.querySelector('.site-header')
			? document.querySelector('.site-header').offsetHeight
			: 0;
		var offset = 24; // Extra breathing room below header.
		var top    = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - offset;

		window.scrollTo({
			top: top,
			behavior: 'smooth'
		});

		// Update URL hash without jumping.
		history.replaceState(null, '', '#' + target.id);
	}

	/**
	 * Scroll spy — highlights the ToC link corresponding to the
	 * currently visible section. Uses IntersectionObserver for performance.
	 */
	function initScrollSpy() {
		var tocLinks = tocList.querySelectorAll('.ps-docs-toc-link');

		if (tocLinks.length === 0) {
			return;
		}

		var headerHeight = document.querySelector('.site-header')
			? document.querySelector('.site-header').offsetHeight
			: 0;

		var observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					var id = entry.target.id;
					tocLinks.forEach(function(link) {
						link.classList.remove('ps-docs-toc-link--active');
						if (link.getAttribute('href') === '#' + id) {
							link.classList.add('ps-docs-toc-link--active');
						}
					});
				}
			});
		}, {
			rootMargin: '-' + (headerHeight + 32) + 'px 0px -60% 0px',
			threshold: 0
		});

		headings.forEach(function(heading) {
			observer.observe(heading);
		});
	}

	/**
	 * Mobile dropdown toggle for the sidebar ToC.
	 */
	function initMobileToggle() {
		var tocTitle = document.querySelector('.ps-docs-toc-title');

		if (!tocTitle) {
			return;
		}

		tocTitle.addEventListener('click', function() {
			// Only toggle on mobile (sidebar is not sticky).
			if (window.innerWidth < 1024) {
				sidebar.classList.toggle('ps-docs-sidebar--open');
			}
		});
	}

	// Initialize.
	buildToc();
	initScrollSpy();
	initMobileToggle();
})();
