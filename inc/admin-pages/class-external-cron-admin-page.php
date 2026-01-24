<?php
/**
 * External Cron Admin Page.
 *
 * Handles the admin interface for the External Cron Service integration.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.3.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * External Cron Admin Page class.
 *
 * @since 2.3.0
 */
class External_Cron_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-external-cron';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * If this is a submenu, we need a parent menu to attach this to.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * Allows us to highlight another menu page, if this page has no parent page at all.
	 *
	 * @since 2.3.0
	 * @var bool|string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title.
	 *
	 * @since 2.3.0
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $supported_panels = [
		'network_admin_menu' => 'manage_network',
	];

	/**
	 * Service URL for the External Cron Service.
	 *
	 * @var string
	 */
	const SERVICE_URL = 'https://ultimatemultisite.com';

	/**
	 * Product slug for the External Cron Service subscription.
	 *
	 * @var string
	 */
	const PRODUCT_SLUG = 'external-cron-service';

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init(): void {

		add_action('wp_ajax_wu_external_cron_connect', [$this, 'ajax_connect']);
		add_action('wp_ajax_wu_external_cron_disconnect', [$this, 'ajax_disconnect']);
		add_action('wp_ajax_wu_external_cron_sync', [$this, 'ajax_sync']);
		add_action('wp_ajax_wu_external_cron_toggle', [$this, 'ajax_toggle']);
	}

	/**
	 * Allow child classes to register scripts and styles.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_scripts(): void {

		wp_enqueue_script('wu-admin');
	}

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.3.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('External Cron Service', 'ultimate-multisite');
	}

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.3.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('External Cron', 'ultimate-multisite');
	}

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function output(): void {

		wu_get_template(
			'external-cron/dashboard',
			[
				'page'             => $this,
				'is_connected'     => $this->is_connected(),
				'is_enabled'       => $this->is_enabled(),
				'site_id'          => wu_get_setting('external_cron_site_id', ''),
				'granularity'      => wu_get_setting('external_cron_granularity', 'network'),
				'last_sync'        => get_site_option('wu_external_cron_last_sync', 0),
				'schedule_count'   => $this->get_schedule_count(),
				'recent_logs'      => $this->get_recent_logs(),
				'service_status'   => $this->get_service_status(),
				'subscription_url' => $this->get_subscription_url(),
				'nonce'            => wp_create_nonce('wu_external_cron_nonce'),
			]
		);
	}

	/**
	 * Check if connected to the External Cron Service.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_connected(): bool {

		$site_id = wu_get_setting('external_cron_site_id', '');

		return ! empty($site_id);
	}

	/**
	 * Check if External Cron Service is enabled.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_enabled(): bool {

		return (bool) wu_get_setting('external_cron_enabled', false);
	}

	/**
	 * Get count of scheduled jobs.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public function get_schedule_count(): int {

		$manager = \WP_Ultimo\External_Cron\External_Cron_Manager::get_instance();

		if (method_exists($manager, 'get_reporter')) {
			$reporter = $manager->get_reporter();
			if ($reporter && method_exists($reporter, 'get_schedule_count')) {
				return $reporter->get_schedule_count();
			}
		}

		return 0;
	}

	/**
	 * Get recent execution logs from the service.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_recent_logs(): array {

		$manager = \WP_Ultimo\External_Cron\External_Cron_Manager::get_instance();
		$client  = $manager->get_client();

		if ( ! $client || ! $this->is_connected()) {
			return [];
		}

		$logs = $client->get_logs(20);

		if (is_wp_error($logs)) {
			return [];
		}

		return $logs;
	}

	/**
	 * Get the service status.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_service_status(): array {

		if ( ! $this->is_connected()) {
			return [
				'status' => 'disconnected',
				'label'  => __('Not Connected', 'ultimate-multisite'),
				'color'  => 'red',
			];
		}

		if ( ! $this->is_enabled()) {
			return [
				'status' => 'disabled',
				'label'  => __('Connected but Disabled', 'ultimate-multisite'),
				'color'  => 'yellow',
			];
		}

		return [
			'status' => 'active',
			'label'  => __('Active', 'ultimate-multisite'),
			'color'  => 'green',
		];
	}

	/**
	 * Get the subscription purchase URL.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_subscription_url(): string {

		return self::SERVICE_URL . '/addons/' . self::PRODUCT_SLUG . '/';
	}

	/**
	 * Get the OAuth connect URL.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_connect_url(): string {

		$return_url = add_query_arg(
			[
				'page'   => $this->id,
				'action' => 'connect_callback',
			],
			network_admin_url('admin.php')
		);

		return add_query_arg(
			[
				'action'     => 'external_cron_connect',
				'site_url'   => rawurlencode(network_site_url()),
				'return_url' => rawurlencode($return_url),
			],
			self::SERVICE_URL . '/wp-json/cron-service/v1/oauth/authorize'
		);
	}

	/**
	 * AJAX handler for connecting to the service.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function ajax_connect(): void {

		check_ajax_referer('wu_external_cron_nonce', 'nonce');

		if ( ! current_user_can('manage_network')) {
			wp_send_json_error(['message' => __('Permission denied.', 'ultimate-multisite')]);
		}

		$manager      = \WP_Ultimo\External_Cron\External_Cron_Manager::get_instance();
		$registration = $manager->get_registration();

		$result = $registration->register_network();

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()]);
		}

		// Schedule sync immediately.
		if (function_exists('wu_enqueue_async_action')) {
			wu_enqueue_async_action('wu_external_cron_sync_schedules');
		}

		wp_send_json_success(
			[
				'message' => __('Network connected successfully!', 'ultimate-multisite'),
				'site_id' => $result['site_id'],
			]
		);
	}

	/**
	 * AJAX handler for disconnecting from the service.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function ajax_disconnect(): void {

		check_ajax_referer('wu_external_cron_nonce', 'nonce');

		if ( ! current_user_can('manage_network')) {
			wp_send_json_error(['message' => __('Permission denied.', 'ultimate-multisite')]);
		}

		$manager      = \WP_Ultimo\External_Cron\External_Cron_Manager::get_instance();
		$registration = $manager->get_registration();

		$result = $registration->unregister();

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()]);
		}

		wp_send_json_success(
			[
				'message' => __('Network disconnected successfully.', 'ultimate-multisite'),
			]
		);
	}

	/**
	 * AJAX handler for syncing schedules.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function ajax_sync(): void {

		check_ajax_referer('wu_external_cron_nonce', 'nonce');

		if ( ! current_user_can('manage_network')) {
			wp_send_json_error(['message' => __('Permission denied.', 'ultimate-multisite')]);
		}

		$manager = \WP_Ultimo\External_Cron\External_Cron_Manager::get_instance();
		$manager->sync_schedules();

		update_site_option('wu_external_cron_last_sync', time());

		wp_send_json_success(
			[
				'message' => __('Schedules synced successfully!', 'ultimate-multisite'),
			]
		);
	}

	/**
	 * AJAX handler for toggling the service.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function ajax_toggle(): void {

		check_ajax_referer('wu_external_cron_nonce', 'nonce');

		if ( ! current_user_can('manage_network')) {
			wp_send_json_error(['message' => __('Permission denied.', 'ultimate-multisite')]);
		}

		$enabled = (bool) wu_request('enabled', false);

		wu_save_setting('external_cron_enabled', $enabled);

		$message = $enabled
			? __('External Cron Service enabled.', 'ultimate-multisite')
			: __('External Cron Service disabled.', 'ultimate-multisite');

		wp_send_json_success(
			[
				'message' => $message,
				'enabled' => $enabled,
			]
		);
	}
}
