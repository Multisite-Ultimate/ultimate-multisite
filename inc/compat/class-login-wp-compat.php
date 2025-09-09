<?php
/**
 * LoginWP Compatibility
 *
 * Fixes login errors not showing with loginwp.com plugins.
 *
 * @package WP_Ultimo
 * @subpackage Compat
 * @since 2.4.3
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
use WP_Ultimo\Checkout\Checkout_Pages;

defined('ABSPATH') || exit;

/**
 * LoginWP compatibility class.
 *
 * Provides compatibility fixes for LoginWP plugins to ensure
 * proper login error handling and form display.
 */
class Login_WP_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {
		add_filter('wu_wp-ultimo/login-form_form_fields', [$this, 'add_error_field']);
	}

	/**
	 * Add error field to login form when login fails.
	 *
	 * Checks for login failure and adds an error message field
	 * to the top of the login form fields array.
	 *
	 * @since 2.0.0
	 * @param array $fields The current form fields.
	 * @return array The modified form fields with error field if needed.
	 */
	public function add_error_field(array $fields): array {
		/*
		 * Check for error messages
		 *
		 * If we have some, we add an additional field
		 * at the top of the fields array, to display the errors.
		 */
		if ('failed' === wu_request('login')) {
			$error_message_field = [
				'error_message' => [
					'type' => 'note',
					'desc' => __('<strong>Error:</strong> Incorrect username or password.', 'multisite-ultimate'),
				],
			];

			$fields = array_merge($error_message_field, $fields);
		}
		return $fields;
	}
}
