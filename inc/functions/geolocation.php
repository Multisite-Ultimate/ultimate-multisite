<?php
/**
 * Geolocation Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get the customers' IP address.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_ip() {
	return apply_filters('wu_get_ip', \WP_Ultimo\Geolocation::get_ip_address());
}
