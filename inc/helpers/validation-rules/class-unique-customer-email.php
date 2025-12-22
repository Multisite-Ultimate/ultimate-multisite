<?php
/**
 * Adds a validation rule to check if a customer with the given email already exists.
 *
 * This rule is specifically designed for the multiple accounts feature.
 * It checks if an Ultimate Multisite customer exists with the given email
 * anywhere on the network, preventing duplicate customer accounts with
 * the same email even when the enable_multiple_accounts setting is on.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.3.0
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use Rakit\Validation\Rule;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Validates that no customer exists with the given email.
 *
 * @since 2.3.0
 */
class Unique_Customer_Email extends Rule {

	/**
	 * Error message to be returned when this email is already in use by a customer.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $message = 'A customer with the same email address already exists.';

	/**
	 * Performs the actual check.
	 *
	 * Searches for all WordPress users with the given email address
	 * and checks if any of them are linked to an Ultimate Multisite customer.
	 *
	 * @since 2.3.0
	 *
	 * @param mixed $value The email address being validated.
	 * @return bool True if no customer exists with this email, false otherwise.
	 */
	public function check($value): bool {

		if (empty($value)) {
			return true;
		}

		// Set a translated error message
		$this->message = __('A customer with the same email address already exists.', 'ultimate-multisite');

		global $wpdb;

		// Query for users with this exact email address across the entire network
		// We use a direct query to ensure exact email matching (not LIKE)
		$user_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->users} WHERE user_email = %s",
				$value
			)
		);

		// No users with this email exist, so no customer can exist
		if (empty($user_ids)) {
			return true;
		}

		// Check if any of these users are linked to an Ultimate Multisite customer
		foreach ($user_ids as $user_id) {
			$customer = wu_get_customer_by_user_id($user_id);

			if ($customer) {
				// A customer with this email already exists
				return false;
			}
		}

		// No customer found with this email
		return true;
	}
}
