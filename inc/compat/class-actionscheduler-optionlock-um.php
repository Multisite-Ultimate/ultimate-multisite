<?php
/**
 * Patched Action Scheduler option-based lock.
 *
 * Copies ActionScheduler_OptionLock but changes get_existing_lock() to return
 * null|string and updates set() to check for strict null, allowing updates when
 * the stored option value is an empty string.
 * See https://github.com/woocommerce/action-scheduler/pull/1289
 */

namespace WP_Ultimo\Compat;

defined('ABSPATH') || exit;

use ActionScheduler_Lock;

/**
 * Provide a way to set simple transient locks to block behaviour for up-to a given duration.
 */
class ActionScheduler_OptionLock_UM extends ActionScheduler_Lock {

	/**
	 * Set a lock using options for a given amount of time (60 seconds by default).
	 *
	 * Using an autoloaded option avoids running database queries or other resource intensive tasks
	 * on frequently triggered hooks, like 'init' or 'shutdown'.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @return bool True if lock value has changed, false if not or if set failed.
	 */
	public function set($lock_type) {
		global $wpdb;

		$lock_key            = $this->get_key($lock_type);
		$existing_lock_value = $this->get_existing_lock($lock_type);
		$new_lock_value      = $this->new_lock_value($lock_type);

		// The lock may not exist yet, or may have been deleted.
		if (null === $existing_lock_value) {
			return (bool) $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->options,
				array(
					'option_name'  => $lock_key,
					'option_value' => $new_lock_value,
					'autoload'     => 'no',
				)
			);
		}

		if ( $this->get_expiration_from($existing_lock_value) >= time() ) {
			return false;
		}

		// Otherwise, try to obtain the lock by replacing the existing value.
		return (bool) $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->options,
			array('option_value' => $new_lock_value),
			array(
				'option_name'  => $lock_key,
				'option_value' => $existing_lock_value,
			)
		);
	}

	/**
	 * If a lock is set, return the timestamp it was set to expiry.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @return bool|int False if no lock is set, otherwise the timestamp for when the lock is set to expire.
	 */
	public function get_expiration($lock_type) {
		return $this->get_expiration_from((string) $this->get_existing_lock($lock_type));
	}

	/**
	 * Given the lock string, derives the lock expiration timestamp (or false if it cannot be determined).
	 *
	 * @param string $lock_value String containing a timestamp, or pipe-separated combination of unique value and timestamp.
	 *
	 * @return false|int
	 */
	private function get_expiration_from($lock_value) {
		$lock_string = explode('|', $lock_value);

		// Old style lock?
		if ( count($lock_string) === 1 && is_numeric($lock_string[0]) ) {
			return (int) $lock_string[0];
		}

		// New style lock?
		if ( count($lock_string) === 2 && is_numeric($lock_string[1]) ) {
			return (int) $lock_string[1];
		}

		return false;
	}

	/**
	 * Get the key to use for storing the lock in the options table.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @return string
	 */
	protected function get_key($lock_type) {
		return sprintf('action_scheduler_lock_%s', $lock_type);
	}

	/**
	 * Supplies the existing lock value, or null if not set.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 *
	 * @return string|null
	 */
	private function get_existing_lock($lock_type) {
		global $wpdb;

		// get_val() returns null for the empty string ('') so we must use get_row().
		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s",
				$this->get_key($lock_type)
			)
		);

		if ($row) {
			return $row->option_value;
		}
		return null;
	}

	/**
	 * Supplies a lock value consisting of a unique value and the current timestamp, separated by a pipe.
	 *
	 * Example: "649de012e6b262.09774912|1688068114"
	 *
	 * @param string $lock_type A string to identify different lock types.
	 *
	 * @return string
	 */
	private function new_lock_value($lock_type) {
		return uniqid('', true) . '|' . (time() + $this->get_duration($lock_type));
	}
}
