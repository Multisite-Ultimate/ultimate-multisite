<?php
/**
 * Sunrise Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * General helper functions for sunrise.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Sunrise
 * @version     2.0.11
 */
function wu_should_load_sunrise() {

	return \WP_Ultimo\Sunrise::should_load_sunrise();
}

/**
 * Get a setting value, when te normal APIs are not available.
 *
 * Should only be used if we're running in sunrise.
 *
 * @since 2.0.0
 *
 * @param string $setting Setting to get.
 * @param mixed  $default_value Default value.
 * @return mixed
 */
function wu_get_setting_early($setting, $default_value = false) {

	if (did_action('wp_ultimo_load')) {
		_doing_it_wrong('wu_get_setting_early', esc_html__('Regular setting APIs are already available. You should use wu_get_setting() instead.', 'multisite-ultimate'), '2.0.0');
	}

	$settings_key = \WP_Ultimo\Settings::KEY;

	$settings = get_network_option(null, 'wp-ultimo_' . $settings_key);

	return wu_get_isset($settings, $setting, $default_value);
}

/**
 * Set a setting value, when te normal APIs are not available.
 *
 * Should only be used if we're running in sunrise.
 *
 * @since 2.0.20
 *
 * @param string $key   Setting to save.
 * @param mixed  $value Setting value.
 */
function wu_save_setting_early($key, $value) {

	if (did_action('wp_ultimo_load')) {
		_doing_it_wrong('wu_save_setting_early', esc_html__('Regular setting APIs are already available. You should use wu_save_setting() instead.', 'multisite-ultimate'), '2.0.20');
	}

	$settings_key = \WP_Ultimo\Settings::KEY;

	$settings = get_network_option(null, 'wp-ultimo_' . $settings_key);

	$settings[ $key ] = $value;

	return update_network_option(null, 'wp-ultimo_' . $settings_key, $settings);
}

/**
 * Get the security mode key used to disable security mode
 *
 * @since 2.0.20
 */
function wu_get_security_mode_key(): string {

	$hash = md5((string) get_network_option(null, 'admin_email'));

	return substr($hash, 0, 6);
}

/**
 * Early substitute for wp_kses_data before it exists.
 *
 * Sanitize content with allowed HTML KSES rules.
 *
 * This function expects unslashed data.
 *
 * @since 2.1.0
 *
 * @param string $data Content to filter, expected to not be escaped.
 * @return string Filtered content.
 */
function wu_kses_data($data) {

	return function_exists('wp_kses_data') ? wp_kses_data($data) : $data;
}
