/* global jQuery, WU_PasswordStrength */
/**
 * Password strength meter for the password reset form.
 *
 * Uses the shared WU_PasswordStrength utility to check password strength
 * and enforces minimum strength requirements.
 *
 * @since 2.3.0
 */
(function($) {
	'use strict';

	var passwordStrength;

	/**
	 * Initialize the password strength meter.
	 */
	$(document).ready(function() {
		var $pass1 = $('#field-pass1');
		var $pass2 = $('#field-pass2');
		var $submit = $('#wp-submit');
		var $form = $pass1.closest('form');

		if (!$pass1.length || typeof WU_PasswordStrength === 'undefined') {
			return;
		}

		// Initialize the password strength checker using the shared utility.
		// minStrength defaults to value from 'wu_minimum_password_strength' filter (default: 4 = Strong)
		passwordStrength = new WU_PasswordStrength({
			pass1: $pass1,
			pass2: $pass2,
			submit: $submit
		});

		// Prevent form submission if password is too weak
		$form.on('submit', function(e) {
			if (!passwordStrength.isValid()) {
				e.preventDefault();
				return false;
			}
		});
	});

}(jQuery));
