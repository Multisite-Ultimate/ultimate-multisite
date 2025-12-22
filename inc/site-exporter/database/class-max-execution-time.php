<?php
/**
 * Database Max Execution Time
 *
 * @package WP_Ultimo\Site_Exporter\Database
 * @since 2.5.0
 */

namespace WP_Ultimo\Site_Exporter\Database;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class Max_Execution_Time - set the service time out up to 0
 *
 * @package WP_Ultimo\Site_Exporter\Database
 */
class Max_Execution_Time {

	/**
	 * Max execution time.
	 *
	 * @var int
	 */
	private int $met = 0;

	/**
	 * Store current timelimit and set a limit
	 *
	 * @param int $time The time limit to set.
	 * @return void
	 */
	public function set(int $time = 0): void {

		if (0 === $time) {
			$this->store();
		}

		@set_time_limit($time); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Restore timelimit.
	 *
	 * @return void
	 */
	public function restore(): void {

		$this->set($this->met);
	}

	/**
	 * Fetch the max_execution_time from php.ini.
	 *
	 * @return void
	 */
	public function store(): void {

		$this->met = (int) ini_get('max_execution_time');
	}
}
