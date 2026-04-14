/**
 * Documentation Page — Table of Contents & Scroll Spy
 * Peptide Starter Theme
 *
 * Handles:
 * - Auto-generates ToC from h2 headings
 * - Slugifies heading IDs from the heading text (with collision counter)
 *   so deep links survive edits that change the author's order
 * - IntersectionObserver scroll spy highlights the active section
 * - Smooth scroll with header offset on ToC click
 * - Collapses sidebar to dropdown on mobile
 *
 * @see page-documentation.php — renders the two-column layout
 */

(function () {
	'use strict';

	const docsBody = document.getElementById('ps-docs-body');
	const tocList  = document.getElementById('ps-docs-toc-list');
	const sidebar  = document.querySelector('.ps-docs-sidebar');

	if (!docsBody || !tocList) {
		return;
	}

	const headings = docsBody.querySelectorAll('h2');

	if (headings.length === 0) {
		if (sidebar) { sidebar.style.display = 'none'; }
		return;
	}

	/**
	 * Deterministic slug derived from heading text. Collisions resolved
	 * with a numeric suffix so every h2 gets a unique ID even when authors
	 * repeat titles ("Examples", "Examples", ...).
	 * @param {string} text
	 * @returns {string}
	 */
	function slugify(text) {
		return String(text)
			.toLowerCase()
			.trim()
			.replace(/[^\w\s-]/g, '')
			.replace(/\s+/g, '-')
			.replace(/-+/g, '-')
			.replace(/^-|-$/g, '') || 'section';
	}

	function buildToc() {
		const used = Object.create(null);

		headings.forEach(function (heading) {
			if (!heading.id) {
				let base = slugify(heading.textContent || '');
				let slug = base;
				let n    = 2;
				while (document.getElementById(slug) || used[slug]) {
					slug = base + '-' + n;
					n++;
				}
				heading.id = slug;
				used[slug] = true;
			}

			const li   = document.createElement('li');
			const link = document.createElement('a');

			link.href        = '#' + heading.id;
			link.textContent = heading.textContent;
			link.className   = 'ps-docs-toc-link';

			link.addEventListener('click', function (e) {
				e.preventDefault();
				smoothScrollTo(heading);
			});

			li.appendChild(link);
			tocList.appendChild(li);
		});
	}

	function smoothScrollTo(target) {
		const headerEl     = document.querySelector('.site-header');
		const headerHeight = headerEl ? headerEl.offsetHeight : 0;
		const offset       = 24;
		const top          = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - offset;

		window.scrollTo({ top: top, behavior: 'smooth' });
		history.replaceState(null, '', '#' + target.id);
	}

	function initScrollSpy() {
		const tocLinks = tocList.querySelectorAll('.ps-docs-toc-link');
		if (tocLinks.length === 0) return;

		const headerEl     = document.querySelector('.site-header');
		const headerHeight = headerEl ? headerEl.offsetHeight : 0;

		const observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				const id = entry.target.id;
				tocLinks.forEach(function (link) {
					link.classList.remove('ps-docs-toc-link--active');
					if (link.getAttribute('href') === '#' + id) {
						link.classList.add('ps-docs-toc-link--active');
					}
				});
			});
		}, {
			rootMargin: '-' + (headerHeight + 32) + 'px 0px -60% 0px',
			threshold: 0
		});

		headings.forEach(function (heading) { observer.observe(heading); });
	}

	function initMobileToggle() {
		const tocTitle = document.querySelector('.ps-docs-toc-title');
		if (!tocTitle || !sidebar) return;

		tocTitle.addEventListener('click', function () {
			if (window.innerWidth < 1024) {
				sidebar.classList.toggle('ps-docs-sidebar--open');
			}
		});
	}

	buildToc();
	initScrollSpy();
	initMobileToggle();
})();
