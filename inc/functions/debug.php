<?php
/**
 * Debug Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the trap for memory limit, to prevent the default fatal error.
 *
 * @since 2.0.11
 * @return void
 */
function wu_try_unlimited_server_limits() {

	// Check if we should use a more conservative approach
	if (apply_filters('wu_use_conservative_memory_limits', false)) {
		// Use a more conservative memory limit
		@ini_set('memory_limit', '256M'); // phpcs:ignore
	} else {
		// Disable memory_limit by setting it to minus 1.
		@ini_set('memory_limit', '-1'); // phpcs:ignore
	}

	// Disable the time limit by setting it to 0.
	@set_time_limit(0); // phpcs:ignore
}

/**
 * Custom error handler for memory leaks
 *
 * @since 2.0.11
 * @param string $return_type The return type to echo to the screen.
 *                            'json', to return json; 'plain' to simply echo the message.
 * @return void
 */
function wu_setup_memory_limit_trap($return_type = 'plain') {

	$trap = \WP_Ultimo\Internal\Memory_Trap::get_instance();

	$trap->set_return_type($return_type);

	$trap->setup();
}
