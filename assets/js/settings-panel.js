/**
 * Settings Panel — Open/Close & Contact Form
 * Peptide Starter Theme
 *
 * Handles:
 * - Toggle settings slide-out panel visibility
 * - AJAX submission of the contact form
 * - Overlay backdrop click to close
 * - Escape key to close
 * - Focus trap inside the panel for accessibility
 *
 * @see template-parts/settings-panel.php — panel HTML
 * @see header.php — settings icon trigger button
 * @see functions.php — AJAX handler for contact form
 *
 * Enqueued globally (lightweight — all listeners are passive until panel opens).
 */

(function() {
	'use strict';

	var panel       = document.getElementById('ps-settings-panel');
	var overlay     = document.getElementById('ps-settings-overlay');
	var closeBtn    = document.getElementById('ps-settings-close');
	var openBtn     = document.getElementById('ps-settings-toggle');
	var contactForm = document.getElementById('ps-contact-form');
	var statusEl    = document.getElementById('ps-contact-status');

	if (!panel || !overlay) {
		return;
	}

	/**
	 * Open the settings panel.
	 */
	function openPanel() {
		panel.classList.add('ps-settings-panel--open');
		panel.setAttribute('aria-hidden', 'false');
		overlay.classList.add('ps-settings-overlay--active');
		overlay.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';

		// Focus the close button for keyboard users.
		if (closeBtn) {
			setTimeout(function() { closeBtn.focus(); }, 100);
		}
	}

	/**
	 * Close the settings panel.
	 */
	function closePanel() {
		panel.classList.remove('ps-settings-panel--open');
		panel.setAttribute('aria-hidden', 'true');
		overlay.classList.remove('ps-settings-overlay--active');
		overlay.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';

		// Return focus to the trigger button.
		if (openBtn) {
			openBtn.focus();
		}
	}

	// Bind open trigger.
	if (openBtn) {
		openBtn.addEventListener('click', openPanel);
	}

	// Bind close button.
	if (closeBtn) {
		closeBtn.addEventListener('click', closePanel);
	}

	// Bind overlay backdrop click.
	overlay.addEventListener('click', closePanel);

	// Close on Escape key.
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && panel.classList.contains('ps-settings-panel--open')) {
			closePanel();
		}
	});

	/**
	 * Contact form AJAX submission.
	 */
	if (contactForm) {
		contactForm.addEventListener('submit', function(e) {
			e.preventDefault();

			if (!contactForm.checkValidity()) {
				contactForm.reportValidity();
				return;
			}

			var formData = new FormData(contactForm);
			var btn      = contactForm.querySelector('button[type="submit"]');
			var origText = btn.textContent;

			btn.disabled    = true;
			btn.textContent = '...';

			if (statusEl) {
				statusEl.innerHTML = '';
			}

			var xhr = new XMLHttpRequest();
			var ajaxUrl = (typeof peptideStarterData !== 'undefined' && peptideStarterData.ajaxUrl)
				? peptideStarterData.ajaxUrl
				: '/wp-admin/admin-ajax.php';

			xhr.open('POST', ajaxUrl);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

			xhr.onload = function() {
				btn.disabled    = false;
				btn.textContent = origText;

				try {
					var resp = JSON.parse(xhr.responseText);
					if (resp.success) {
						if (statusEl) {
							statusEl.innerHTML = '<div class="ps-alert ps-alert-success"><p>' +
								escapeHtml(resp.data.message || 'Message sent.') + '</p></div>';
						}
						contactForm.reset();
					} else {
						if (statusEl) {
							statusEl.innerHTML = '<div class="ps-alert ps-alert-error"><p>' +
								escapeHtml(resp.data.message || 'Failed to send.') + '</p></div>';
						}
					}
				} catch (ex) {
					if (statusEl) {
						statusEl.innerHTML = '<div class="ps-alert ps-alert-error"><p>An unexpected error occurred.</p></div>';
					}
				}
			};

			xhr.onerror = function() {
				btn.disabled    = false;
				btn.textContent = origText;
				if (statusEl) {
					statusEl.innerHTML = '<div class="ps-alert ps-alert-error"><p>Network error. Please try again.</p></div>';
				}
			};

			xhr.send(formData);
		});
	}

	/**
	 * Escape HTML for safe insertion.
	 * @param {string} str - Raw text.
	 * @returns {string} Escaped HTML.
	 */
	function escapeHtml(str) {
		var div       = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}
})();
