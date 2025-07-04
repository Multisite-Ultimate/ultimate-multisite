<?php
/**
 * Notification Manager
 *
 * Handles processes related to notifications.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Notification_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to notifications.
 *
 * @since 2.0.0
 */
class Notification_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of callbacks to keep, for backwards compatibility purposes.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	protected $backwards_compatibility_list;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('init', [$this, 'add_settings']);

		if (is_admin() && ! is_network_admin()) {
			add_action('admin_init', [$this, 'hide_notifications_subsites']);
		}
	}

	/**
	 * Hide notifications on subsites if settings was enabled.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hide_notifications_subsites(): void {

		if ( ! wu_get_setting('hide_notifications_subsites')) {
			return;
		}

		global $wp_filter;

		/*
		 * List of callbacks to keep, for backwards compatibility purposes.
		 */
		$this->backwards_compatibility_list = apply_filters(
			'wu_hide_notifications_exclude_list',
			[
				'inject_admin_head_ads',
			]
		);

		$cleaner = [$this, 'clear_callback_list'];

		if (wu_get_isset($wp_filter, 'admin_notices')) {
			$wp_filter['admin_notices']->callbacks = array_filter($wp_filter['admin_notices']->callbacks, $cleaner ?? fn($v, $k): bool => ! empty($v), null === $cleaner ? ARRAY_FILTER_USE_BOTH : 0);
		}

		if (wu_get_isset($wp_filter, 'all_admin_notices')) {
			$wp_filter['all_admin_notices']->callbacks = array_filter($wp_filter['all_admin_notices']->callbacks, $cleaner ?? fn($v, $k): bool => ! empty($v), null === $cleaner ? ARRAY_FILTER_USE_BOTH : 0);
		}
	}

	/**
	 * Keeps the allowed callbacks.
	 *
	 * @since 2.0.0
	 *
	 * @param array $callbacks The callbacks attached.
	 * @return array
	 */
	public function clear_callback_list($callbacks): bool {

		if (empty($this->backwards_compatibility_list)) {
			return false;
		}

		$keys = array_keys($callbacks);

		foreach ($keys as $key) {
			foreach ($this->backwards_compatibility_list as $key_to_keep) {
				if (str_contains($key, (string) $key_to_keep)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Filter the Multisite Ultimate settings to add Notifications Options
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings(): void {

		wu_register_settings_field(
			'sites',
			'hide_notifications_subsites',
			[
				'title'   => __('Hide Admin Notices on Sites', 'multisite-ultimate'),
				'desc'    => __('Hide all admin notices on network sites, except for Multisite Ultimate broadcasts.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
				'order'   => 25,
			]
		);
	}
}
