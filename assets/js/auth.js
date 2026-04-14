/**
 * Authentication Page — Form Toggle & Validation
 * Peptide Starter Theme
 *
 * Handles:
 * - Toggle between sign-in and register forms
 * - Client-side form validation
 * - Password strength indicator
 * - AJAX form submission to avoid full page reload
 *
 * @see page-auth.php — renders the auth forms
 * @see functions.php — AJAX handlers (ps_auth_login, ps_auth_register)
 *
 * Conditionally enqueued only on pages using the Sign In / Register template.
 */

(function() {
	'use strict';

	var loginWrap    = document.getElementById('ps-auth-login');
	var registerWrap = document.getElementById('ps-auth-register');
	var showRegister = document.getElementById('ps-show-register');
	var showLogin    = document.getElementById('ps-show-login');
	var messages     = document.getElementById('ps-auth-messages');
	var loginForm    = document.getElementById('ps-login-form');
	var registerForm = document.getElementById('ps-register-form');

	if (!loginWrap || !registerWrap) {
		return;
	}

	/**
	 * Toggle between login and register views.
	 * @param {string} view - 'login' or 'register'.
	 */
	function showView(view) {
		clearMessages();
		if (view === 'register') {
			loginWrap.classList.add('ps-auth-form-wrap--hidden');
			registerWrap.classList.remove('ps-auth-form-wrap--hidden');
		} else {
			registerWrap.classList.add('ps-auth-form-wrap--hidden');
			loginWrap.classList.remove('ps-auth-form-wrap--hidden');
		}
	}

	if (showRegister) {
		showRegister.addEventListener('click', function(e) {
			e.preventDefault();
			showView('register');
		});
	}

	if (showLogin) {
		showLogin.addEventListener('click', function(e) {
			e.preventDefault();
			showView('login');
		});
	}

	// Check URL hash on load for deep linking.
	if (window.location.hash === '#register') {
		showView('register');
	}

	/**
	 * Display a message in the auth messages area.
	 * @param {string} text - Message text.
	 * @param {string} type - 'success', 'error', or 'info'.
	 */
	function showMessage(text, type) {
		if (!messages) {
			return;
		}
		var alertClass = 'ps-alert ps-alert-' + (type || 'info');
		messages.innerHTML = '<div class="' + alertClass + '"><p>' + escapeHtml(text) + '</p></div>';
	}

	function clearMessages() {
		if (messages) {
			messages.innerHTML = '';
		}
	}

	/**
	 * Escape HTML entities to prevent XSS in message display.
	 * @param {string} str - Raw string.
	 * @returns {string} Escaped string.
	 */
	function escapeHtml(str) {
		var div       = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	/**
	 * Password strength indicator.
	 * Updates the #ps-password-strength element with a rating.
	 */
	var passwordInput = document.getElementById('ps-reg-password');
	var strengthEl    = document.getElementById('ps-password-strength');

	if (passwordInput && strengthEl) {
		passwordInput.addEventListener('input', function() {
			var val    = passwordInput.value;
			var score  = 0;
			var label  = '';

			if (val.length >= 8)  { score++; }
			if (val.length >= 12) { score++; }
			if (/[a-z]/.test(val) && /[A-Z]/.test(val)) { score++; }
			if (/\d/.test(val))   { score++; }
			if (/[^a-zA-Z0-9]/.test(val)) { score++; }

			if (val.length === 0) {
				label = '';
			} else if (score <= 1) {
				label = '<span class="ps-strength-weak">Weak</span>';
			} else if (score <= 3) {
				label = '<span class="ps-strength-fair">Fair</span>';
			} else {
				label = '<span class="ps-strength-strong">Strong</span>';
			}

			strengthEl.innerHTML = label;
		});
	}

	/**
	 * AJAX form submission handler.
	 * @param {HTMLFormElement} form - The form to submit.
	 */
	function submitForm(form) {
		var formData = new FormData(form);
		var btn      = form.querySelector('button[type="submit"]');
		var origText = btn.textContent;

		btn.disabled    = true;
		btn.textContent = '...';
		clearMessages();

		var xhr = new XMLHttpRequest();
		xhr.open('POST', (typeof peptideStarterData !== 'undefined' ? peptideStarterData.ajaxUrl : '/wp-admin/admin-ajax.php'));
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

		xhr.onload = function() {
			btn.disabled    = false;
			btn.textContent = origText;

			try {
				var resp = JSON.parse(xhr.responseText);
				if (resp.success && resp.data && resp.data.redirect) {
					window.location.href = resp.data.redirect;
				} else {
					showMessage(resp.data && resp.data.message ? resp.data.message : 'An error occurred.', 'error');
				}
			} catch (e) {
				showMessage('An unexpected error occurred. Please try again.', 'error');
			}
		};

		xhr.onerror = function() {
			btn.disabled    = false;
			btn.textContent = origText;
			showMessage('Network error. Please check your connection.', 'error');
		};

		xhr.send(formData);
	}

	if (loginForm) {
		loginForm.addEventListener('submit', function(e) {
			e.preventDefault();
			if (!loginForm.checkValidity()) {
				loginForm.reportValidity();
				return;
			}
			submitForm(loginForm);
		});
	}

	if (registerForm) {
		registerForm.addEventListener('submit', function(e) {
			e.preventDefault();
			if (!registerForm.checkValidity()) {
				registerForm.reportValidity();
				return;
			}
			submitForm(registerForm);
		});
	}
})();
