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
	 * Whether the memory trap is enabled.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	private $is_enabled = true;

	/**
	 * The memory limit to use when the trap is active.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $memory_limit = '-1';

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
	 * Enables or disables the memory trap.
	 *
	 * @since 2.0.0
	 * @param boolean $enabled Whether to enable the memory trap.
	 * @return void
	 */
	public function set_enabled($enabled): void {
		$this->is_enabled = (bool) $enabled;
	}

	/**
	 * Sets the memory limit to use when the trap is active.
	 *
	 * @since 2.0.0
	 * @param string $limit The memory limit to set.
	 * @return void
	 */
	public function set_memory_limit($limit): void {
		$this->memory_limit = $limit;
	}

	/**
	 * Setup the actual error handler.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function setup(): void {

		// Allow plugins to disable the memory trap
		if (apply_filters('wu_disable_memory_trap', false) || !$this->is_enabled) {
			return;
		}

		/**
		 * Fires before the memory trap is set up.
		 *
		 * @since 2.0.0
		 * @param \WP_Ultimo\Internal\Memory_Trap $this The Memory_Trap instance.
		 */
		do_action('wu_setup_memory_limit_trap', $this);

		$this->memory_reserve = str_repeat('*', 1024 * 1024);

		!defined('WP_SANDBOX_SCRAPING') && define('WP_SANDBOX_SCRAPING', true); // phpcs:ignore

		// Use the configured memory limit instead of hardcoding -1
		@ini_set('memory_limit', $this->memory_limit); // phpcs:ignore

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

		$message = sprintf(__('Your server\'s PHP and WordPress memory limits are too low to perform this check. You might need to contact your host provider and ask the PHP memory limit in particular to be raised.', 'ultimate-multisite'));

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
