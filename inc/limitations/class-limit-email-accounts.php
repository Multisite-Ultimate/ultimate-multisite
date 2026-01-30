<?php
/**
 * Email Accounts Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.3.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email Accounts Limit Module.
 *
 * Controls how many email accounts a membership can have.
 *
 * @since 2.3.0
 */
class Limit_Email_Accounts extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $id = 'email_accounts';

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * Each module needs to implement a check method, that returns a boolean.
	 * This check can take any form the developer wants.
	 *
	 * @since 2.3.0
	 *
	 * @param mixed  $value_to_check Value to check (current count).
	 * @param mixed  $limit The limit value.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = '') {

		if ( ! $this->is_enabled()) {
			return false;
		}

		// For simple boolean limits (enabled/disabled)
		if (is_bool($limit)) {
			return $limit;
		}

		// For numeric limits
		if (is_numeric($limit)) {
			// 0 means unlimited
			if (0 === (int) $limit) {
				return true;
			}

			return (int) $value_to_check < (int) $limit;
		}

		// Default to enabled
		return true;
	}

	/**
	 * Check if more email accounts can be created.
	 *
	 * @since 2.3.0
	 *
	 * @param int $customer_id   The customer ID.
	 * @param int $membership_id The membership ID.
	 * @return bool
	 */
	public function can_create_more($customer_id, $membership_id) {

		if ( ! $this->is_enabled()) {
			return false;
		}

		$limit = $this->get_limit();

		// Boolean true means unlimited
		if (true === $limit) {
			return true;
		}

		// Boolean false means none allowed
		if (false === $limit) {
			return false;
		}

		// 0 means unlimited
		if (0 === (int) $limit) {
			return true;
		}

		$current_count = $this->get_current_account_count($customer_id, $membership_id);

		return $current_count < (int) $limit;
	}

	/**
	 * Get the current count of email accounts.
	 *
	 * @since 2.3.0
	 *
	 * @param int      $customer_id   The customer ID.
	 * @param int|null $membership_id Optional membership ID.
	 * @return int
	 */
	public function get_current_account_count($customer_id, $membership_id = null) {

		return wu_count_email_accounts($customer_id, $membership_id);
	}

	/**
	 * Get remaining email account slots.
	 *
	 * @since 2.3.0
	 *
	 * @param int $customer_id   The customer ID.
	 * @param int $membership_id The membership ID.
	 * @return int|string Returns 'unlimited' if no limit, or the number of remaining slots.
	 */
	public function get_remaining_slots($customer_id, $membership_id) {

		if ( ! $this->is_enabled()) {
			return 0;
		}

		$limit = $this->get_limit();

		// Boolean false means none allowed
		if (false === $limit) {
			return 0;
		}

		// Boolean true or numeric 0 means unlimited
		if (true === $limit || 0 === (int) $limit) {
			return 'unlimited';
		}

		$current_count = $this->get_current_account_count($customer_id, $membership_id);

		return max(0, (int) $limit - $current_count);
	}

	/**
	 * Returns a default state.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public static function default_state() {

		return [
			'enabled' => false,
			'limit'   => 0, // 0 = unlimited when enabled
		];
	}
}
