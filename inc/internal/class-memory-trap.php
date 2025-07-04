<?php
/**
 * Memory Trap
 *
 * Sets up a memory exhausted fatal error catcher,
 * that allows us to deal with it in different ways
 * and not throw the default error.
 *
 * @package WP_Ultimo\Internal
 * @since 2.0.11
 */

namespace WP_Ultimo\Internal;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Memory Trap.
 *
 * @since 2.0.11
 */
class Memory_Trap {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Memory reserve.
	 *
	 * This is required so we can free it before
	 * trying to do anything else. We just hit a
	 * memory exhaust error, so if we don't save a couple
	 * of MBs before hand, we won't be able to get
	 * our custom handler to work.
	 *
	 * @since 2.0.11
	 * @var null|string
	 */
	public $memory_reserve;

	/**
	 * The type to display the error message
	 *
	 * @since 2.0.11
	 * @var string 'json' or 'plain'.
	 */
	protected $return_type = 'plain';

	/**
	 * Set the return type.
	 *
	 * @since 2.0.11
	 *
	 * @param string $return_type 'json' or 'plain'.
	 * @return void
	 */
	public function set_return_type($return_type): void {

		$this->return_type = $return_type;
	}

	/**
	 * Setup the actual error handler.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function setup(): void {

		$this->memory_reserve = str_repeat('*', 1024 * 1024);

		!defined('WP_SANDBOX_SCRAPING') && define('WP_SANDBOX_SCRAPING', true); // phpcs:ignore

		register_shutdown_function(
			function () {

				$this->memory_reserve = null;

				$err = error_get_last();

			if ((!is_null($err)) && (!in_array($err['type'], [E_NOTICE, E_WARNING, E_DEPRECATED, E_USER_DEPRECATED]))) { // phpcs:ignore

					$this->memory_limit_error_handler($err);
				}
			}
		);
	}

	/**
	 * Send fatal error messages.
	 *
	 * @since 2.0.11
	 *
	 * @internal
	 * @param array $error The error array.
	 * @return void
	 */
	public function memory_limit_error_handler($error): void { // phpcs:ignore

		$message = sprintf(__('Your server\'s PHP and WordPress memory limits are too low to perform this check. You might need to contact your host provider and ask the PHP memory limit in particular to be raised.', 'multisite-ultimate'));

		if ('json' === $this->return_type) {
			wp_send_json_error(
				[
					'message' => $message,
				]
			);

			die;
		} else {
			echo esc_html($message);
		}

		exit;
	}
}
