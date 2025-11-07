<?php
/**
 * Compatibility Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Filters data before it's JSON encoded to prevent memory issues.
 *
 * @since 2.0.0
 * @param mixed $data The data to be encoded.
 * @param string $context The context of the encoding.
 * @return mixed
 */
function wu_pre_json_encode($data, $context = '') {

	/**
	 * Filters data before it's JSON encoded.
	 *
	 * @since 2.0.0
	 * @param mixed $data The data to be encoded.
	 * @param string $context The context of the encoding.
	 * @return mixed
	 */
	return apply_filters('wu_pre_json_encode', $data, $context);
}

/**
 * Safe JSON encode that prevents memory issues.
 *
 * @since 2.0.0
 * @param mixed $data The data to be encoded.
 * @param int $options JSON encoding options.
 * @param string $context The context of the encoding.
 * @return string|false
 */
function wu_json_encode($data, $options = 0, $context = '') {

	// Filter the data before encoding
	$data = wu_pre_json_encode($data, $context);

	// Use WordPress's wp_json_encode function
	return wp_json_encode($data, $options);
}

/**
 * Checks if a plugin is active.
 *
 * @since 2.0.0
 * @param string $plugin_file The plugin file path relative to the plugins directory.
 * @return boolean
 */
function wu_is_plugin_active($plugin_file) {

	if (!function_exists('is_plugin_active')) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return is_plugin_active($plugin_file);
}

/**
 * Loads all compatibility classes.
 *
 * @since 2.0.0
 * @return void
 */
function wu_load_compatibility_classes() {

	// Load the generic page builder detector
	\WP_Ultimo\Compatibility\Page_Builder_Detector::get_instance()->init();

	/**
	 * Fires after compatibility classes are loaded.
	 *
	 * @since 2.0.0
	 */
	do_action('wu_compatibility_classes_loaded');
}
