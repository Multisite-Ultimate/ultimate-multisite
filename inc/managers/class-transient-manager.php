<?php
/**
 * Transient Manager Class
 *
 * Handles processes related to transients.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Transient_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to transients.
 *
 * @since 2.0.0
 */
class Transient_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the list of transients created by WP Ultimo.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $transients = array();

	/**
	 * Initializes the Transient Manager.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		// Register hooks
		add_action('wu_delete_transients', array($this, 'delete_transients'));
		add_action('wu_delete_transient', array($this, 'delete_transient'));
		add_action('wu_set_transient', array($this, 'set_transient'), 10, 3);
		add_action('wu_get_transient', array($this, 'get_transient'));

		// Register our transients with WordPress for cleanup
		add_filter('wp_using_ext_object_cache', array($this, 'register_transients_for_cleanup'));
	}

	/**
	 * Registers a transient with the manager.
	 *
	 * @since 2.0.0
	 * @param string $transient_name The name of the transient.
	 * @param string $group The group the transient belongs to.
	 * @return void
	 */
	public function register_transient($transient_name, $group = 'general') {

		if (!isset($this->transients[$group])) {
			$this->transients[$group] = array();
		}

		if (!in_array($transient_name, $this->transients[$group])) {
			$this->transients[$group][] = $transient_name;
		}
	}

	/**
	 * Sets a transient and registers it with the manager.
	 *
	 * @since 2.0.0
	 * @param string $transient_name The name of the transient.
	 * @param mixed  $value The value to store.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
	 * @param string $group Optional. The group the transient belongs to. Default 'general'.
	 * @return bool True if the transient was set, false otherwise.
	 */
	public function set_transient($transient_name, $value, $expiration = 0, $group = 'general') {

		// Register the transient
		$this->register_transient($transient_name, $group);

		// Set the transient
		return set_site_transient($transient_name, $value, $expiration);
	}

	/**
	 * Gets a transient value.
	 *
	 * @since 2.0.0
	 * @param string $transient_name The name of the transient.
	 * @return mixed The value of the transient or false if it doesn't exist.
	 */
	public function get_transient($transient_name) {

		return get_site_transient($transient_name);
	}

	/**
	 * Deletes a transient.
	 *
	 * @since 2.0.0
	 * @param string $transient_name The name of the transient.
	 * @return bool True if the transient was deleted, false otherwise.
	 */
	public function delete_transient($transient_name) {

		return delete_site_transient($transient_name);
	}

	/**
	 * Deletes all transients in a group.
	 *
	 * @since 2.0.0
	 * @param string $group The group of transients to delete.
	 * @return bool True if all transients were deleted, false otherwise.
	 */
	public function delete_transients_by_group($group) {

		if (!isset($this->transients[$group])) {
			return false;
		}

		$success = true;

		foreach ($this->transients[$group] as $transient_name) {
			$result = $this->delete_transient($transient_name);
			$success = $success && $result;
		}

		return $success;
	}

	/**
	 * Deletes all transients.
	 *
	 * @since 2.0.0
	 * @return bool True if all transients were deleted, false otherwise.
	 */
	public function delete_transients() {

		$success = true;

		foreach ($this->transients as $group => $transients) {
			$result = $this->delete_transients_by_group($group);
			$success = $success && $result;
		}

		return $success;
	}

	/**
	 * Gets all registered transients.
	 *
	 * @since 2.0.0
	 * @return array The list of registered transients.
	 */
	public function get_registered_transients() {

		return $this->transients;
	}

	/**
	 * Gets all registered transients in a group.
	 *
	 * @since 2.0.0
	 * @param string $group The group of transients to get.
	 * @return array The list of registered transients in the group.
	 */
	public function get_registered_transients_by_group($group) {

		if (!isset($this->transients[$group])) {
			return array();
		}

		return $this->transients[$group];
	}

	/**
	 * Registers our transients with WordPress for cleanup.
	 *
	 * @since 2.0.0
	 * @param bool $external_object_cache Whether an external object cache is being used.
	 * @return bool The original value of $external_object_cache.
	 */
	public function register_transients_for_cleanup($external_object_cache) {

		// Only run this once
		static $ran = false;

		if ($ran) {
			return $external_object_cache;
		}

		$ran = true;

		// Register our transients with WordPress
		foreach ($this->transients as $group => $transients) {
			foreach ($transients as $transient_name) {
				wp_cache_add_non_persistent_groups('site-transient_' . $transient_name);
			}
		}

		return $external_object_cache;
	}

}
