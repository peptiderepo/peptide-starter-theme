/**
 * Navigation and Mobile Menu Toggle
 * Peptide Starter Theme
 *
 * Handles:
 * - Mobile hamburger menu toggle
 * - Navigation overlay click handling
 * - Menu state persistence
 */

(function() {
	'use strict';

	const menuToggle = document.querySelector('.menu-toggle');
	const primaryNav = document.querySelector('.primary-navigation');
	const navOverlay = document.querySelector('.nav-overlay');

	if (!menuToggle || !primaryNav || !navOverlay) {
		return;
	}

	/**
	 * Toggle mobile menu visibility
	 */
	function toggleMenu() {
		const isActive = primaryNav.classList.contains('active');

		if (isActive) {
			closeMenu();
		} else {
			openMenu();
		}
	}

	/**
	 * Open mobile menu
	 */
	function openMenu() {
		primaryNav.classList.add('active');
		navOverlay.classList.add('active');
		document.body.style.overflow = 'hidden';
		menuToggle.setAttribute('aria-expanded', 'true');
	}

	/**
	 * Close mobile menu
	 */
	function closeMenu() {
		primaryNav.classList.remove('active');
		navOverlay.classList.remove('active');
		document.body.style.overflow = '';
		menuToggle.setAttribute('aria-expanded', 'false');
	}

	// Menu toggle button click
	menuToggle.addEventListener('click', toggleMenu);

	// Overlay click
	navOverlay.addEventListener('click', closeMenu);

	// Close menu when a nav link is clicked
	const navLinks = primaryNav.querySelectorAll('a');
	navLinks.forEach(link => {
		link.addEventListener('click', closeMenu);
	});

	// Close menu on Escape key
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			closeMenu();
		}
	});

	// Set initial aria-expanded state
	menuToggle.setAttribute('aria-expanded', 'false');

	// Close menu when window is resized above mobile breakpoint
	const mediaQuery = window.matchMedia('(min-width: 768px)');
	mediaQuery.addEventListener('change', (e) => {
		if (e.matches) {
			closeMenu();
		}
	});
})();
