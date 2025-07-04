<?php
/**
 * HTTP, Request and Response Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the PHP input (php://input) as JSON.
 *
 * @since 2.0.0
 *
 * @param boolean $raw Wether to return the raw string or a decoded value.
 * @return object
 */
function wu_get_input($raw = false) {

	$body = @file_get_contents('php://input'); // phpcs:ignore

	return $raw ? $body : json_decode($body);
}

/**
 * Prevents the current page from being cached.
 *
 * @since 2.0.0
 * @return void
 */
function wu_no_cache() {

	if ( ! headers_sent()) {
		nocache_headers();

		header('Pragma: no-cache');

		/*
		 * Let's send something custom so we can
		 * easily spot when no-caching is out fault!
		 */
		wu_x_header('X-Ultimo-Cache: prevent-caching');
	}

	do_action('wu_no_cache');
}

/**
 * Maybe sends a Multisite Ultimate X Header.
 *
 * Useful for debugging purposes.
 * These headers can easily be omitted by
 * running add_filter('wu_should_send_x_headers', '__return_false');
 *
 * @since 2.0.0
 *
 * @param string $header The header to send. Example: X-Ultimo-Caching: prevent-caching.
 * @return void
 */
function wu_x_header($header) {

	if (apply_filters('wu_should_send_x_headers', defined('WP_DEBUG') && WP_DEBUG)) {
		! headers_sent() && header($header);
	}
}
