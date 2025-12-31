/* global jQuery, wp, wu_password_reset */
/**
 * Password strength meter for the password reset form.
 *
 * @since 2.3.0
 */
(function($) {
	'use strict';

	var i18n = typeof wu_password_reset !== 'undefined' ? wu_password_reset : {
		enter_password: 'Enter a password',
		short: 'Very weak',
		weak: 'Weak',
		medium: 'Medium',
		strong: 'Strong',
		mismatch: 'Passwords do not match'
	};

	var isPasswordValid = false;
	var minStrength = typeof wu_password_reset !== 'undefined' ? parseInt(wu_password_reset.min_strength, 10) : 3;

	/**
	 * Check password strength and update the UI.
	 */
	function checkPasswordStrength($pass1, $pass2, $result, $submit) {
		var pass1 = $pass1.val();
		var pass2 = $pass2.val();

		// Reset classes
		$result.attr('class', 'wu-py-2 wu-px-4 wu-block wu-text-sm wu-border-solid wu-border wu-mt-2');

		if (!pass1) {
			$result.addClass('wu-bg-gray-100 wu-border-gray-200').html(i18n.enter_password);
			isPasswordValid = false;
			$submit.prop('disabled', true);
			return;
		}

		// Get disallowed list from WordPress
		var disallowedList = '';
		if (typeof wp !== 'undefined' && typeof wp.passwordStrength !== 'undefined') {
			disallowedList = typeof wp.passwordStrength.userInputDisallowedList === 'undefined'
				? wp.passwordStrength.userInputBlacklist()
				: wp.passwordStrength.userInputDisallowedList();
		}

		var strength = wp.passwordStrength.meter(pass1, disallowedList, pass2);

		isPasswordValid = false;

		switch (strength) {
			case 0:
			case 1:
				$result.addClass('wu-bg-red-200 wu-border-red-300').html(i18n.short);
				break;
			case 2:
				$result.addClass('wu-bg-red-200 wu-border-red-300').html(i18n.weak);
				break;
			case 3:
				$result.addClass('wu-bg-yellow-200 wu-border-yellow-300').html(i18n.medium);
				if (minStrength <= 3) {
					isPasswordValid = true;
				}
				break;
			case 4:
				$result.addClass('wu-bg-green-200 wu-border-green-300').html(i18n.strong);
				isPasswordValid = true;
				break;
			case 5:
				$result.addClass('wu-bg-red-200 wu-border-red-300').html(i18n.mismatch);
				break;
			default:
				$result.addClass('wu-bg-red-200 wu-border-red-300').html(i18n.short);
		}

		$submit.prop('disabled', !isPasswordValid);
	}

	/**
	 * Initialize the password strength meter.
	 */
	$(document).ready(function() {
		var $pass1 = $('#field-pass1');
		var $pass2 = $('#field-pass2');
		var $submit = $('#wp-submit');

		if (!$pass1.length) {
			return;
		}

		// Create strength meter element if it doesn't exist
		var $result = $('#pass-strength-result');
		if (!$result.length) {
			$result = $('<div id="pass-strength-result" class="wu-py-2 wu-px-4 wu-bg-gray-100 wu-block wu-text-sm wu-border-solid wu-border wu-border-gray-200 wu-mt-2">' + i18n.enter_password + '</div>');
			$pass1.after($result);
		}

		// Bind events
		$pass1.on('keyup input', function() {
			checkPasswordStrength($pass1, $pass2, $result, $submit);
		});
		$pass2.on('keyup input', function() {
			checkPasswordStrength($pass1, $pass2, $result, $submit);
		});

		// Disable submit initially
		$submit.prop('disabled', true);

		// Prevent form submission if password is too weak
		$pass1.closest('form').on('submit', function(e) {
			if (!isPasswordValid) {
				e.preventDefault();
				return false;
			}
		});

		// Initial check
		checkPasswordStrength($pass1, $pass2, $result, $submit);
	});

})(jQuery);
