/**
 * Settings Panel — Open/Close, Focus Trap, Contact Form AJAX
 * Peptide Starter Theme
 *
 * Handles:
 * - Open / close the slide-out panel (button, overlay click, Escape)
 * - Save and restore document.body.style.overflow so other panels
 *   opening the overlay don't stomp on the caller's original value
 * - Focus trap: Tab / Shift+Tab wrap within the panel while it's open
 * - AJAX submission of the contact form with XSS-safe status rendering
 *
 * @see template-parts/settings-panel.php — panel HTML
 * @see header.php — trigger button
 * @see inc/contact-handler.php — AJAX endpoint
 */

(function () {
	'use strict';

	const panel       = document.getElementById('ps-settings-panel');
	const overlay     = document.getElementById('ps-settings-overlay');
	const closeBtn    = document.getElementById('ps-settings-close');
	const openBtn     = document.getElementById('ps-settings-toggle');
	const contactForm = document.getElementById('ps-contact-form');
	const statusEl    = document.getElementById('ps-contact-status');

	if (!panel || !overlay) {
		return;
	}

	const FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
	let previousOverflow = '';
	let firstFocusable   = null;
	let lastFocusable    = null;

	/**
	 * Snapshot and refresh the focusable boundary elements inside the panel.
	 * Called on open so dynamically-rendered fields are included.
	 */
	function refreshFocusables() {
		const list = panel.querySelectorAll(FOCUSABLE);
		if (!list.length) {
			firstFocusable = null;
			lastFocusable  = null;
			return;
		}
		firstFocusable = list[0];
		lastFocusable  = list[list.length - 1];
	}

	function openPanel() {
		panel.classList.add('ps-settings-panel--open');
		panel.setAttribute('aria-hidden', 'false');
		overlay.classList.add('ps-settings-overlay--active');
		overlay.setAttribute('aria-hidden', 'false');
		previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		refreshFocusables();
		if (closeBtn) {
			setTimeout(function () { closeBtn.focus(); }, 100);
		}
	}

	function closePanel() {
		panel.classList.remove('ps-settings-panel--open');
		panel.setAttribute('aria-hidden', 'true');
		overlay.classList.remove('ps-settings-overlay--active');
		overlay.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = previousOverflow;
		previousOverflow = '';

		if (openBtn) {
			openBtn.focus();
		}
	}

	function trapFocus(e) {
		if (!panel.classList.contains('ps-settings-panel--open')) return;
		if (e.key !== 'Tab') return;
		if (!firstFocusable || !lastFocusable) return;

		if (e.shiftKey && document.activeElement === firstFocusable) {
			e.preventDefault();
			lastFocusable.focus();
		} else if (!e.shiftKey && document.activeElement === lastFocusable) {
			e.preventDefault();
			firstFocusable.focus();
		}
	}

	if (openBtn)  { openBtn.addEventListener('click', openPanel); }
	if (closeBtn) { closeBtn.addEventListener('click', closePanel); }
	overlay.addEventListener('click', closePanel);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && panel.classList.contains('ps-settings-panel--open')) {
			closePanel();
		}
	});
	document.addEventListener('keydown', trapFocus);

	// Contact form AJAX.
	if (contactForm) {
		contactForm.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!contactForm.checkValidity()) {
				contactForm.reportValidity();
				return;
			}

			const formData = new FormData(contactForm);
			const btn      = contactForm.querySelector('button[type="submit"]');
			const origText = btn.textContent;

			btn.disabled    = true;
			btn.textContent = '...';
			if (statusEl) { statusEl.innerHTML = ''; }

			const xhr = new XMLHttpRequest();
			const url = (typeof window.peptideStarterData !== 'undefined' && window.peptideStarterData.ajaxUrl)
				? window.peptideStarterData.ajaxUrl
				: '/wp-admin/admin-ajax.php';
			xhr.open('POST', url);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			xhr.onload = function () {
				btn.disabled    = false;
				btn.textContent = origText;

				let resp;
				try { resp = JSON.parse(xhr.responseText); } catch (ex) {
					renderStatus('An unexpected error occurred.', 'error');
					return;
				}
				if (resp && resp.success) {
					renderStatus((resp.data && resp.data.message) || 'Message sent.', 'success');
					contactForm.reset();
				} else {
					renderStatus((resp && resp.data && resp.data.message) || 'Failed to send.', 'error');
				}
			};

			xhr.onerror = function () {
				btn.disabled    = false;
				btn.textContent = origText;
				renderStatus('Network error. Please try again.', 'error');
			};

			xhr.send(formData);
		});
	}

	function renderStatus(text, type) {
		if (!statusEl) return;
		const wrap = document.createElement('div');
		wrap.className = 'ps-alert ps-alert-' + (type || 'info');
		const p = document.createElement('p');
		p.textContent = text;
		wrap.appendChild(p);
		statusEl.innerHTML = '';
		statusEl.appendChild(wrap);
	}
})();
