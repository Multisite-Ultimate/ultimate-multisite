<?php
/**
 * Transient Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Sets a transient and registers it with the transient manager.
 *
 * @since 2.0.0
 * @param string $transient_name The name of the transient.
 * @param mixed  $value The value to store.
 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @param string $group Optional. The group the transient belongs to. Default 'general'.
 * @return bool True if the transient was set, false otherwise.
 */
function wu_set_transient($transient_name, $value, $expiration = 0, $group = 'general') {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->set_transient($transient_name, $value, $expiration, $group);
}

/**
 * Gets a transient value.
 *
 * @since 2.0.0
 * @param string $transient_name The name of the transient.
 * @return mixed The value of the transient or false if it doesn't exist.
 */
function wu_get_transient($transient_name) {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->get_transient($transient_name);
}

/**
 * Deletes a transient.
 *
 * @since 2.0.0
 * @param string $transient_name The name of the transient.
 * @return bool True if the transient was deleted, false otherwise.
 */
function wu_delete_transient($transient_name) {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->delete_transient($transient_name);
}

/**
 * Deletes all transients in a group.
 *
 * @since 2.0.0
 * @param string $group The group of transients to delete.
 * @return bool True if all transients were deleted, false otherwise.
 */
function wu_delete_transients_by_group($group) {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->delete_transients_by_group($group);
}

/**
 * Deletes all transients.
 *
 * @since 2.0.0
 * @return bool True if all transients were deleted, false otherwise.
 */
function wu_delete_transients() {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->delete_transients();
}

/**
 * Gets all registered transients.
 *
 * @since 2.0.0
 * @return array The list of registered transients.
 */
function wu_get_registered_transients() {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->get_registered_transients();
}

/**
 * Gets all registered transients in a group.
 *
 * @since 2.0.0
 * @param string $group The group of transients to get.
 * @return array The list of registered transients in the group.
 */
function wu_get_registered_transients_by_group($group) {

	return \WP_Ultimo\Managers\Transient_Manager::get_instance()->get_registered_transients_by_group($group);
}
