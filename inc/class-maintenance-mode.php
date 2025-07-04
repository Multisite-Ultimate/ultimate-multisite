<?php
/**
 * Adds the Maintenance Mode.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo;

use WP_Ultimo\Managers\Cache_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Maintenance Mode.
 *
 * @since 2.0.0
 */
class Maintenance_Mode {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initializes
	 *
	 * @since 2.0.0
	 */
	public function init(): void {

		add_action('init', [$this, 'add_settings']);

		if (wu_get_setting('maintenance_mode')) {
			$this->hooks();
		}
	}

	/**
	 * Adds the additional hooks, when necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks(): void {

		add_action('wu_ajax_toggle_maintenance_mode', [$this, 'toggle_maintenance_mode']);

		if ( ! is_main_site()) {
			add_action('admin_bar_menu', [$this, 'add_notice_to_admin_bar'], 15);
		}

		if (self::check_maintenance_mode()) {
			add_filter('pre_option_blog_public', '__return_true');

			if ( ! is_admin()) {
				add_action('wp', [$this, 'render_page']);

				if (function_exists('wp_robots_no_robots')) {
					add_filter('wp_robots', 'wp_robots_no_robots'); // WordPress 5.7+

				} else {
					add_action('wp_head', 'wp_no_robots', 20);
				}
			}
		}
	}

	/**
	 * Add maintenance mode Notice to Admin Bar
	 *
	 * @since 2.0.0
	 * @param \WP_Admin_Bar $wp_admin_bar The Admin Bar class.
	 * @return void
	 */
	public function add_notice_to_admin_bar($wp_admin_bar): void {

		if ( ! current_user_can('manage_options')) {
			return;
		}

		if (is_admin() || self::check_maintenance_mode()) {
			$args = [
				'id'     => 'wu-maintenance-mode',
				'parent' => 'top-secondary',
				'title'  => __('Maintenance Mode - Active', 'multisite-ultimate'),
				'href'   => '#wp-ultimo-site-maintenance-element',
				'meta'   => [
					'class' => 'wu-maintenance-mode ' . (self::check_maintenance_mode() ? '' : 'hidden'),
					'title' => __('This means that your site is not available for visitors at the moment. Only you and other logged users have access to it. Click here to toggle this option.', 'multisite-ultimate'),
				],
			];

			$wp_admin_bar->add_node($args);
		}
	}

	/**
	 * Render page - html filtrable
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_page(): void {

		if (is_main_site() || current_user_can('read')) {
			return;
		}

		$text = apply_filters(
			'wu_maintenance_mode_text',
			__('Website under planned maintenance. Please check back later.', 'multisite-ultimate')
		);

		$title = apply_filters(
			'wu_maintenance_mode_title',
			__('Under Maintenance', 'multisite-ultimate')
		);

		wp_die(esc_html($text), esc_html($title), 503);
	}

	/**
	 * Check should display maintenance mode
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function check_maintenance_mode() {

		return get_site_meta(get_current_blog_id(), 'wu_maintenance_mode', true);
	}

	/**
	 * Callback button admin toggle maintenance mode.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function toggle_maintenance_mode() {

		if ( ! check_ajax_referer('wu_toggle_maintenance_mode', '_wpnonce', false)) {
			wp_send_json_error(
				[
					'message' => __('Request failed, please refresh and try again.', 'multisite-ultimate'),
					'value'   => false,
				]
			);
		}

		$site_id = \WP_Ultimo\Helpers\Hash::decode(wu_request('site_hash'), 'site');

		if ( ! current_user_can_for_site($site_id, 'manage_options')) {
			wp_send_json_error(
				[
					'message' => __('You do not have the necessary permissions to perform this option.', 'multisite-ultimate'),
					'value'   => false,
				]
			);
		}

		$value = wu_request('maintenance_status', false);

		$value = wu_string_to_bool($value);

		update_site_meta($site_id, 'wu_maintenance_mode', $value);

		$return = [
			'message' => __('New maintenance settings saved.', 'multisite-ultimate'),
			'value'   => $value,
		];

		// Flush the cache so the maintenance mode new status is applied immediately.
		Cache_Manager::get_instance()->flush_known_caches();

		wp_send_json_success($return);
	}

	/**
	 * Filter the Multisite Ultimate settings to add Jumper options
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_settings(): void {

		wu_register_settings_field(
			'sites',
			'maintenance_mode',
			[
				'title'   => __('Site Maintenance Mode', 'multisite-ultimate'),
				'desc'    => __('Allow your customers and super admins to quickly take sites offline via a toggle on the site dashboard.', 'multisite-ultimate'),
				'type'    => 'toggle',
				'default' => 0,
				'order'   => 23,
			]
		);
	}
}
