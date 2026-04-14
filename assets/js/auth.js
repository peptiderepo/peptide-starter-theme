/**
 * Authentication Page — Form Toggle & AJAX
 * Peptide Starter Theme
 *
 * Handles:
 * - Toggle between sign-in and register forms
 * - Password strength indicator
 * - AJAX form submission (no redirect on register — success message only)
 * - XSS-safe message rendering
 *
 * @see page-auth.php — renders the forms
 * @see inc/auth-handlers.php — ps_auth_login / ps_auth_register
 *
 * Conditionally enqueued on pages using the Sign In / Register template.
 */

(function () {
	'use strict';

	const loginWrap    = document.getElementById('ps-auth-login');
	const registerWrap = document.getElementById('ps-auth-register');
	const showRegister = document.getElementById('ps-show-register');
	const showLogin    = document.getElementById('ps-show-login');
	const messages     = document.getElementById('ps-auth-messages');
	const loginForm    = document.getElementById('ps-login-form');
	const registerForm = document.getElementById('ps-register-form');

	if (!loginWrap || !registerWrap) {
		return;
	}

	/**
	 * Swap which form is visible. Uses a class, not inline style, so the
	 * stylesheet is the single source of truth.
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
		showRegister.addEventListener('click', function (e) {
			e.preventDefault();
			showView('register');
		});
	}

	if (showLogin) {
		showLogin.addEventListener('click', function (e) {
			e.preventDefault();
			showView('login');
		});
	}

	if (window.location.hash === '#register') {
		showView('register');
	}

	/**
	 * Render a message in the messages region. Text is escaped.
	 * @param {string} text
	 * @param {string} type - 'success' | 'error' | 'info'.
	 */
	function showMessage(text, type) {
		if (!messages) return;
		const wrap = document.createElement('div');
		wrap.className = 'ps-alert ps-alert-' + (type || 'info');
		const p = document.createElement('p');
		p.textContent = text;
		wrap.appendChild(p);
		messages.innerHTML = '';
		messages.appendChild(wrap);
	}

	function clearMessages() {
		if (messages) { messages.innerHTML = ''; }
	}

	// Password strength indicator (registration form).
	const passwordInput = document.getElementById('ps-reg-password');
	const strengthEl    = document.getElementById('ps-password-strength');

	if (passwordInput && strengthEl) {
		passwordInput.addEventListener('input', function () {
			const val = passwordInput.value;
			let score = 0;

			if (val.length >= 8)                              { score++; }
			if (val.length >= 12)                             { score++; }
			if (/[a-z]/.test(val) && /[A-Z]/.test(val))       { score++; }
			if (/\d/.test(val))                               { score++; }
			if (/[^a-zA-Z0-9]/.test(val))                     { score++; }

			let className = '';
			let label     = '';
			if (val.length === 0)   { className = ''; }
			else if (score <= 1)    { className = 'ps-strength-weak';   label = 'Weak'; }
			else if (score <= 3)    { className = 'ps-strength-fair';   label = 'Fair'; }
			else                    { className = 'ps-strength-strong'; label = 'Strong'; }

			strengthEl.innerHTML = '';
			if (className) {
				const span = document.createElement('span');
				span.className = className;
				span.textContent = label;
				strengthEl.appendChild(span);
			}
		});
	}

	/**
	 * Submit a form via AJAX to admin-ajax. Login success redirects; register
	 * success shows an inbox-check message (no redirect — email verification
	 * is the next step).
	 * @param {HTMLFormElement} form
	 * @param {string} kind - 'login' or 'register'.
	 */
	function submitForm(form, kind) {
		const formData = new FormData(form);
		const btn      = form.querySelector('button[type="submit"]');
		const origText = btn.textContent;

		btn.disabled    = true;
		btn.textContent = '...';
		clearMessages();

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
			try { resp = JSON.parse(xhr.responseText); } catch (e) {
				showMessage('An unexpected error occurred. Please try again.', 'error');
				return;
			}

			if (resp && resp.success) {
				if (kind === 'login' && resp.data && resp.data.redirect) {
					window.location.href = resp.data.redirect;
					return;
				}
				if (kind === 'register' && resp.data && resp.data.message) {
					showMessage(resp.data.message, 'success');
					form.reset();
					if (strengthEl) { strengthEl.innerHTML = ''; }
					return;
				}
				showMessage('Request completed.', 'success');
				return;
			}

			const msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'An error occurred.';
			showMessage(msg, 'error');
		};

		xhr.onerror = function () {
			btn.disabled    = false;
			btn.textContent = origText;
			showMessage('Network error. Please check your connection.', 'error');
		};

		xhr.send(formData);
	}

	if (loginForm) {
		loginForm.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!loginForm.checkValidity()) {
				loginForm.reportValidity();
				return;
			}
			submitForm(loginForm, 'login');
		});
	}

	if (registerForm) {
		registerForm.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!registerForm.checkValidity()) {
				registerForm.reportValidity();
				return;
			}
			submitForm(registerForm, 'register');
		});
	}

	// Surface /auth?verify_error=1.
	if (messages && /[?&]verify_error=1/.test(window.location.search)) {
		showMessage('That verification link is invalid or has expired. Sign in and request a new one.', 'error');
	}
})();
