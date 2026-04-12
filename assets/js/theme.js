/**
 * Theme Utilities
 * Peptide Starter Theme
 *
 * Handles:
 * - Dark mode toggle functionality
 * - Dark mode preference persistence
 * - Plugin dark mode coordination
 * - Smooth transitions
 */

(function() {
	'use strict';

	const html = document.documentElement;
	const darkModeToggle = document.querySelector('.dark-mode-toggle');
	const sunIcon = document.querySelector('.sun-icon');
	const moonIcon = document.querySelector('.moon-icon');

	if (!darkModeToggle) {
		return;
	}

	/**
	 * Get current theme preference
	 * @returns {string} 'dark' or 'light'
	 */
	function getCurrentTheme() {
		const stored = localStorage.getItem('peptide-starter-theme');
		if (stored) {
			return stored;
		}

		const currentAttr = html.getAttribute('data-theme');
		if (currentAttr) {
			return currentAttr;
		}

		const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		return systemDark ? 'dark' : 'light';
	}

	/**
	 * Set theme preference
	 * @param {string} theme - 'dark' or 'light'
	 */
	function setTheme(theme) {
		html.setAttribute('data-theme', theme);
		localStorage.setItem('peptide-starter-theme', theme);

		// Update icon visibility
		updateIcons(theme);

		// Notify plugins about theme change via custom event
		const event = new CustomEvent('themechange', {
			detail: { theme: theme }
		});
		document.dispatchEvent(event);

		// Also update body class for CSS hooks
		document.body.classList.remove('dark-mode', 'light-mode');
		document.body.classList.add(theme === 'dark' ? 'dark-mode' : 'light-mode');
	}

	/**
	 * Update icon visibility based on theme
	 * @param {string} theme - 'dark' or 'light'
	 */
	function updateIcons(theme) {
		if (theme === 'dark') {
			sunIcon.style.display = 'block';
			moonIcon.style.display = 'none';
		} else {
			sunIcon.style.display = 'none';
			moonIcon.style.display = 'block';
		}
	}

	/**
	 * Toggle between dark and light mode
	 */
	function toggleDarkMode() {
		const currentTheme = getCurrentTheme();
		const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
		setTheme(newTheme);
	}

	// Initialize theme on page load
	const initialTheme = getCurrentTheme();
	setTheme(initialTheme);

	// Dark mode toggle button click
	darkModeToggle.addEventListener('click', toggleDarkMode);

	// Listen for system preference changes
	const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
	mediaQuery.addEventListener('change', (e) => {
		const stored = localStorage.getItem('peptide-starter-theme');
		if (!stored) {
			setTheme(e.matches ? 'dark' : 'light');
		}
	});

	// Expose API for plugins to read/listen to theme changes
	window.peptideStarterTheme = {
		getCurrentTheme: getCurrentTheme,
		setTheme: setTheme,
		toggleDarkMode: toggleDarkMode,
	};

	/**
	 * Plugin Integration Example:
	 * Plugins can listen to theme changes via:
	 *
	 * 1. Custom event listener:
	 *    document.addEventListener('themechange', function(e) {
	 *      console.log('Theme changed to:', e.detail.theme);
	 *    });
	 *
	 * 2. MutationObserver on data-theme:
	 *    const observer = new MutationObserver((mutations) => {
	 *      const theme = html.getAttribute('data-theme');
	 *      console.log('Theme is now:', theme);
	 *    });
	 *    observer.observe(html, { attributes: true });
	 *
	 * 3. Direct access to CSS custom properties which theme controls
	 */

	/**
	 * Smooth scroll behavior
	 */
	function initSmoothScroll() {
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (!prefersReducedMotion) {
			document.documentElement.style.scrollBehavior = 'smooth';
		}
	}

	initSmoothScroll();

	/**
	 * Add focus-visible polyfill for older browsers
	 */
	function initFocusVisible() {
		const isKeyboardEvent = (e) => {
			return e.mozInputSource === MOZ_SOURCE_KEYBOARD ||
				   e.detail === 0 ||
				   (e.sourceCapabilities && e.sourceCapabilities.firesTouchEvents === false);
		};

		document.addEventListener('mousedown', (e) => {
			document.body.classList.remove('keyboard-focus');
		}, true);

		document.addEventListener('keydown', () => {
			document.body.classList.add('keyboard-focus');
		}, true);
	}

	// Search overlay functionality (Peptide Search AI integration)
	const searchToggle = document.querySelector('.search-toggle');
	const searchOverlay = document.getElementById('search-overlay');
	const searchOverlayClose = document.querySelector('.search-overlay-close');

	function openSearchOverlay() {
		if (!searchOverlay) return;
		searchOverlay.classList.add('active');
		searchOverlay.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		// Focus the search input inside the overlay
		const input = searchOverlay.querySelector('.psa-search-input, .ps-search-input, input[type="search"], input[type="text"]');
		if (input) {
			setTimeout(() => input.focus(), 100);
		}
	}

	function closeSearchOverlay() {
		if (!searchOverlay) return;
		searchOverlay.classList.remove('active');
		searchOverlay.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
		// Return focus to search toggle
		if (searchToggle) searchToggle.focus();
	}

	if (searchToggle) {
		searchToggle.addEventListener('click', openSearchOverlay);
	}

	if (searchOverlayClose) {
		searchOverlayClose.addEventListener('click', closeSearchOverlay);
	}

	if (searchOverlay) {
		// Close overlay when clicking the backdrop
		searchOverlay.addEventListener('click', (e) => {
			if (e.target === searchOverlay) {
				closeSearchOverlay();
			}
		});

		// Close on Escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
				closeSearchOverlay();
			}
		});
	}
})();
