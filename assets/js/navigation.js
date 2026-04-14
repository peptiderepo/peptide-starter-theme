/**
 * Navigation and Mobile Menu Toggle
 * Peptide Starter Theme
 *
 * Handles:
 * - Mobile hamburger menu toggle
 * - Navigation overlay click handling
 * - Menu state persistence
 */

(function () {
	'use strict';

	const menuToggle = document.querySelector('.menu-toggle');
	const primaryNav = document.querySelector('.primary-navigation');
	const navOverlay = document.querySelector('.nav-overlay');

	if (!menuToggle || !primaryNav || !navOverlay) {
		return;
	}

	let previousOverflow = '';

	function openMenu() {
		primaryNav.classList.add('active');
		navOverlay.classList.add('active');
		previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		menuToggle.setAttribute('aria-expanded', 'true');
	}

	function closeMenu() {
		primaryNav.classList.remove('active');
		navOverlay.classList.remove('active');
		document.body.style.overflow = previousOverflow;
		previousOverflow = '';
		menuToggle.setAttribute('aria-expanded', 'false');
	}

	function toggleMenu() {
		if (primaryNav.classList.contains('active')) {
			closeMenu();
		} else {
			openMenu();
		}
	}

	menuToggle.addEventListener('click', toggleMenu);
	navOverlay.addEventListener('click', closeMenu);

	const navLinks = primaryNav.querySelectorAll('a');
	navLinks.forEach(function (link) {
		link.addEventListener('click', closeMenu);
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeMenu(); }
	});

	menuToggle.setAttribute('aria-expanded', 'false');

	const mediaQuery = window.matchMedia('(min-width: 768px)');
	mediaQuery.addEventListener('change', function (e) {
		if (e.matches) { closeMenu(); }
	});
})();
