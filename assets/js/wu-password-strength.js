/* global jQuery, wp, pwsL10n */
/**
 * Shared password strength utility for WP Ultimo.
 *
 * This module provides reusable password strength checking functionality
 * that can be used across different forms (checkout, password reset, etc.)
 *
 * @since 2.3.0
 */
(function($) {
	'use strict';

	/**
	 * Password strength checker utility.
	 *
	 * @param {Object} options Configuration options
	 * @param {jQuery} options.pass1 First password field element
	 * @param {jQuery} options.pass2 Second password field element (optional)
	 * @param {jQuery} options.result Strength result display element
	 * @param {jQuery} options.submit Submit button element (optional)
	 * @param {number} options.minStrength Minimum required strength level (default: 3)
	 * @param {Function} options.onValidityChange Callback when password validity changes
	 */
	window.WU_PasswordStrength = function(options) {
		this.options = $.extend({
			pass1: null,
			pass2: null,
			result: null,
			submit: null,
			minStrength: 3,
			onValidityChange: null
		}, options);

		this.isPasswordValid = false;

		this.init();
	};

	WU_PasswordStrength.prototype = {
		/**
		 * Initialize the password strength checker.
		 */
		init: function() {
			var self = this;

			if (!this.options.pass1 || !this.options.pass1.length) {
				return;
			}

			// Create or find strength meter element
			if (!this.options.result || !this.options.result.length) {
				this.options.result = $('#pass-strength-result');

				if (!this.options.result.length) {
					this.options.result = $('<div id="pass-strength-result" class="wu-py-2 wu-px-4 wu-bg-gray-100 wu-block wu-text-sm wu-border-solid wu-border wu-border-gray-200 wu-mt-2"></div>');
					this.options.pass1.after(this.options.result);
				}
			}

			// Set initial message
			this.options.result.html(this.getStrengthLabel('empty'));

			// Bind events
			this.options.pass1.on('keyup input', function() {
				self.checkStrength();
			});

			if (this.options.pass2 && this.options.pass2.length) {
				this.options.pass2.on('keyup input', function() {
					self.checkStrength();
				});
			}

			// Disable submit initially if provided
			if (this.options.submit && this.options.submit.length) {
				this.options.submit.prop('disabled', true);
			}

			// Initial check
			this.checkStrength();
		},

		/**
		 * Check password strength and update the UI.
		 */
		checkStrength: function() {
			var pass1 = this.options.pass1.val();
			var pass2 = this.options.pass2 ? this.options.pass2.val() : '';

			// Reset classes
			this.options.result.attr('class', 'wu-py-2 wu-px-4 wu-block wu-text-sm wu-border-solid wu-border wu-mt-2');

			if (!pass1) {
				this.options.result.addClass('wu-bg-gray-100 wu-border-gray-200').html(this.getStrengthLabel('empty'));
				this.setValid(false);
				return;
			}

			// Get disallowed list from WordPress
			var disallowedList = this.getDisallowedList();

			var strength = wp.passwordStrength.meter(pass1, disallowedList, pass2);

			this.updateUI(strength);
			this.updateValidity(strength);
		},

		/**
		 * Get the disallowed list for password checking.
		 *
		 * @return {Array} The disallowed list
		 */
		getDisallowedList: function() {
			if (typeof wp === 'undefined' || typeof wp.passwordStrength === 'undefined') {
				return [];
			}

			// Support both old and new WordPress naming
			return typeof wp.passwordStrength.userInputDisallowedList === 'undefined'
				? wp.passwordStrength.userInputBlacklist()
				: wp.passwordStrength.userInputDisallowedList();
		},

		/**
		 * Get the appropriate label for a given strength level.
		 *
		 * @param {string|number} strength The strength level
		 * @return {string} The label text
		 */
		getStrengthLabel: function(strength) {
			// Use WordPress's built-in localized strings
			if (typeof pwsL10n === 'undefined') {
				// Fallback labels if pwsL10n is not available
				var fallbackLabels = {
					'empty': 'Enter a password',
					'-1': 'Unknown',
					'0': 'Very weak',
					'1': 'Very weak',
					'2': 'Weak',
					'3': 'Medium',
					'4': 'Strong',
					'5': 'Mismatch'
				};
				return fallbackLabels[strength] || fallbackLabels['0'];
			}

			switch (strength) {
				case 'empty':
					return pwsL10n.empty || 'Strength indicator';
				case -1:
					return pwsL10n.unknown || 'Unknown';
				case 0:
				case 1:
					return pwsL10n.short || 'Very weak';
				case 2:
					return pwsL10n.bad || 'Weak';
				case 3:
					return pwsL10n.good || 'Medium';
				case 4:
					return pwsL10n.strong || 'Strong';
				case 5:
					return pwsL10n.mismatch || 'Mismatch';
				default:
					return pwsL10n.short || 'Very weak';
			}
		},

		/**
		 * Update the UI based on password strength.
		 *
		 * @param {number} strength The password strength level
		 */
		updateUI: function(strength) {
			switch (strength) {
				case -1:
				case 0:
				case 1:
					this.options.result.addClass('wu-bg-red-200 wu-border-red-300').html(this.getStrengthLabel(strength));
					break;
				case 2:
					this.options.result.addClass('wu-bg-red-200 wu-border-red-300').html(this.getStrengthLabel(2));
					break;
				case 3:
					this.options.result.addClass('wu-bg-yellow-200 wu-border-yellow-300').html(this.getStrengthLabel(3));
					break;
				case 4:
					this.options.result.addClass('wu-bg-green-200 wu-border-green-300').html(this.getStrengthLabel(4));
					break;
				case 5:
					this.options.result.addClass('wu-bg-red-200 wu-border-red-300').html(this.getStrengthLabel(5));
					break;
				default:
					this.options.result.addClass('wu-bg-red-200 wu-border-red-300').html(this.getStrengthLabel(0));
			}
		},

		/**
		 * Update password validity based on strength.
		 *
		 * @param {number} strength The password strength level
		 */
		updateValidity: function(strength) {
			var isValid = false;

			if (strength >= this.options.minStrength && strength !== 5) {
				isValid = true;
			}

			this.setValid(isValid);
		},

		/**
		 * Set password validity and update submit button.
		 *
		 * @param {boolean} isValid Whether the password is valid
		 */
		setValid: function(isValid) {
			var wasValid = this.isPasswordValid;
			this.isPasswordValid = isValid;

			if (this.options.submit && this.options.submit.length) {
				this.options.submit.prop('disabled', !isValid);
			}

			// Trigger callback if validity changed
			if (wasValid !== isValid && typeof this.options.onValidityChange === 'function') {
				this.options.onValidityChange(isValid);
			}
		},

		/**
		 * Get the current password validity.
		 *
		 * @return {boolean} Whether the password is valid
		 */
		isValid: function() {
			return this.isPasswordValid;
		}
	};

})(jQuery);
