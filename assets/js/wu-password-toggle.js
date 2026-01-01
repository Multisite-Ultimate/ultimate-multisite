/**
 * Password visibility toggle functionality.
 *
 * Adds show/hide functionality to password fields using the WordPress
 * core approach with dashicons.
 *
 * @since 2.4.0
 * @output assets/js/wu-password-toggle.js
 */

(function() {
	'use strict';

	var __ = wp.i18n.__;

	/**
	 * Initialize password toggle functionality.
	 */
	function init() {
		var toggleElements = document.querySelectorAll('.wu-pwd-toggle');

		toggleElements.forEach(function(toggle) {
			toggle.classList.remove('hide-if-no-js');
			toggle.addEventListener('click', togglePassword);
		});
	}

	/**
	 * Toggle password visibility.
	 *
	 * @param {Event} event Click event.
	 */
	function togglePassword(event) {
		event.preventDefault();

		var toggle = this;
		var status = toggle.getAttribute('data-toggle');
		var input = toggle.parentElement.querySelector('input[type="password"], input[type="text"]');
		var icon = toggle.querySelector('.dashicons');

		if (!input || !icon) {
			return;
		}

		if ('0' === status) {
			// Show password
			toggle.setAttribute('data-toggle', '1');
			toggle.setAttribute('aria-label', __('Hide password', 'ultimate-multisite'));
			input.setAttribute('type', 'text');
			icon.classList.remove('dashicons-visibility');
			icon.classList.add('dashicons-hidden');
		} else {
			// Hide password
			toggle.setAttribute('data-toggle', '0');
			toggle.setAttribute('aria-label', __('Show password', 'ultimate-multisite'));
			input.setAttribute('type', 'password');
			icon.classList.remove('dashicons-hidden');
			icon.classList.add('dashicons-visibility');
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
