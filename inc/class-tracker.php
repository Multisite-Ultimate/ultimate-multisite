<?php
/**
 * Ultimate Multisite Tracker
 *
 * Handles anonymous usage data collection and error reporting.
 * Follows WordPress.org guidelines for opt-in telemetry.
 *
 * @package WP_Ultimo
 * @subpackage Tracker
 * @since 2.5.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Tracker class for anonymous usage data collection.
 *
 * @since 2.5.0
 */
class Tracker implements \WP_Ultimo\Interfaces\Singleton {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * API endpoint URL for receiving tracking data.
	 *
	 * @var string
	 */
	const API_URL = 'https://ultimatemultisite.com/wp-json/wu-telemetry/v1/track';

	/**
	 * Option name for storing last send timestamp.
	 *
	 * @var string
	 */
	const LAST_SEND_OPTION = 'wu_tracker_last_send';


	/**
	 * Weekly send interval in seconds.
	 *
	 * @var int
	 */
	const SEND_INTERVAL = WEEK_IN_SECONDS;

	/**
	 * Error log levels that should be reported.
	 *
	 * @var array
	 */
	const ERROR_LOG_LEVELS = [
		\Psr\Log\LogLevel::ERROR,
		\Psr\Log\LogLevel::CRITICAL,
		\Psr\Log\LogLevel::ALERT,
		\Psr\Log\LogLevel::EMERGENCY,
	];

	/**
	 * Initialize the tracker.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function init(): void {

		// Create the weekly schedule
		add_action('init', [$this, 'create_weekly_schedule']);

		// Hook into weekly cron for usage data
		add_action('wu_weekly', [$this, 'maybe_send_tracking_data']);

		// Hook into Logger for error reporting (now receives log level as 3rd param)
		add_action('wu_log_add', [$this, 'maybe_send_error'], 10, 3);

		// Hook into WordPress fatal error handler
		add_filter('wp_should_handle_php_error', [$this, 'handle_fatal_error'], 10, 2);

		// Customize fatal error message for network sites
		add_filter('wp_php_error_message', [$this, 'customize_fatal_error_message'], 10, 2);

		// Send initial data when tracking is first enabled
		add_action('wu_settings_update', [$this, 'maybe_send_initial_data'], 10, 2);
	}

	/**
	 * Create the weekly schedule if it doesn't exist.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function create_weekly_schedule(): void {

		if (wu_next_scheduled_action('wu_weekly') === false) {
			$next_week = strtotime('next monday');

			wu_schedule_recurring_action($next_week, WEEK_IN_SECONDS, 'wu_weekly', [], 'wu_cron');
		}
	}

	/**
	 * Check if tracking is enabled.
	 *
	 * @since 2.5.0
	 * @return bool
	 */
	public function is_tracking_enabled(): bool {

		return (bool) wu_get_setting('enable_error_reporting', false);
	}

	/**
	 * Send tracking data if enabled and due.
	 *
	 * @since 2.5.0
	 * @return void
	 */
	public function maybe_send_tracking_data(): void {

		if ( ! $this->is_tracking_enabled()) {
			return;
		}

		$last_send = get_site_option(self::LAST_SEND_OPTION, 0);

		if (time() - $last_send < self::SEND_INTERVAL) {
			return;
		}

		$this->send_tracking_data();
	}

	/**
	 * Send initial data when tracking is first enabled.
	 *
	 * @since 2.5.0
	 * @param string $setting_id The setting being updated.
	 * @param mixed  $value The new value.
	 * @return void
	 */
	public function maybe_send_initial_data(string $setting_id, $value): void {

		if ('enable_error_reporting' !== $setting_id) {
			return;
		}

		if ( ! $value) {
			return;
		}

		// Check if we've never sent data before
		$last_send = get_site_option(self::LAST_SEND_OPTION, 0);

		if (0 === $last_send) {
			$this->send_tracking_data();
		}
	}

	/**
	 * Gather and send tracking data.
	 *
	 * @since 2.5.0
	 * @return array|\WP_Error
	 */
	public function send_tracking_data() {

		$data = $this->get_tracking_data();

		$response = $this->send_to_api($data, 'usage');

		if ( ! is_wp_error($response)) {
			update_site_option(self::LAST_SEND_OPTION, time());
		}

		return $response;
	}

	/**
	 * Get all tracking data.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	public function get_tracking_data(): array {

		return [
			'tracker_version' => '1.0.0',
			'timestamp'       => time(),
			'site_hash'       => $this->get_site_hash(),
			'environment'     => $this->get_environment_data(),
			'plugin'          => $this->get_plugin_data(),
			'network'         => $this->get_network_data(),
			'usage'           => $this->get_usage_data(),
			'gateways'        => $this->get_gateway_data(),
		];
	}

	/**
	 * Get anonymous site hash for deduplication.
	 *
	 * @since 2.5.0
	 * @return string
	 */
	protected function get_site_hash(): string {

		$site_url = get_site_url();
		$auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';

		return hash('sha256', $site_url . $auth_key);
	}

	/**
	 * Get environment data.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_environment_data(): array {

		global $wpdb;

		return [
			'php_version'        => PHP_VERSION,
			'wp_version'         => get_bloginfo('version'),
			'mysql_version'      => $wpdb->db_version(),
			'server_software'    => $this->get_server_software(),
			'max_execution_time' => (int) ini_get('max_execution_time'),
			'memory_limit'       => ini_get('memory_limit'),
			'is_ssl'             => is_ssl(),
			'is_multisite'       => is_multisite(),
			'locale'             => get_locale(),
			'timezone'           => wp_timezone_string(),
		];
	}

	/**
	 * Get server software (sanitized).
	 *
	 * @since 2.5.0
	 * @return string
	 */
	protected function get_server_software(): string {

		$software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unknown';

		// Only return server type, not version for privacy
		if (stripos($software, 'apache') !== false) {
			return 'Apache';
		} elseif (stripos($software, 'nginx') !== false) {
			return 'Nginx';
		} elseif (stripos($software, 'litespeed') !== false) {
			return 'LiteSpeed';
		} elseif (stripos($software, 'iis') !== false) {
			return 'IIS';
		}

		return 'Other';
	}

	/**
	 * Get plugin-specific data.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_plugin_data(): array {

		$active_addons = [];

		// Get active addons
		if (function_exists('WP_Ultimo')) {
			$wu_instance = \WP_Ultimo();

			if ($wu_instance && method_exists($wu_instance, 'get_addon_repository')) {
				$addon_repository = $wu_instance->get_addon_repository();

				if ($addon_repository && method_exists($addon_repository, 'get_installed_addons')) {
					foreach ($addon_repository->get_installed_addons() as $addon) {
						$active_addons[] = $addon['slug'] ?? 'unknown';
					}
				}
			}
		}

		return [
			'version'       => wu_get_version(),
			'active_addons' => $active_addons,
		];
	}

	/**
	 * Get network configuration data.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_network_data(): array {

		return [
			'is_subdomain'           => is_subdomain_install(),
			'is_subdirectory'        => ! is_subdomain_install(),
			'sunrise_installed'      => defined('SUNRISE') && SUNRISE,
			'domain_mapping_enabled' => (bool) wu_get_setting('enable_domain_mapping', false),
		];
	}

	/**
	 * Get aggregated usage statistics.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_usage_data(): array {

		global $wpdb;

		$table_prefix = $wpdb->base_prefix;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// Note: Direct queries without caching are intentional for telemetry counts.
		// Table prefix comes from $wpdb->base_prefix which is safe.

		$sites_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_sites"
		);

		$customers_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_customers"
		);

		$memberships_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_memberships"
		);

		$active_memberships_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_prefix}wu_memberships WHERE status = %s",
				'active'
			)
		);

		$products_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_products"
		);

		$payments_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_payments"
		);

		$domains_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_prefix}wu_domain_mappings"
		);

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return [
			'sites_count'              => $this->anonymize_count($sites_count),
			'customers_count'          => $this->anonymize_count($customers_count),
			'memberships_count'        => $this->anonymize_count($memberships_count),
			'active_memberships_count' => $this->anonymize_count($active_memberships_count),
			'products_count'           => $this->anonymize_count($products_count),
			'payments_count'           => $this->anonymize_count($payments_count),
			'domains_count'            => $this->anonymize_count($domains_count),
		];
	}

	/**
	 * Anonymize counts to ranges for privacy.
	 *
	 * @since 2.5.0
	 * @param int $count The actual count.
	 * @return string The anonymized range.
	 */
	protected function anonymize_count(int $count): string {

		if (0 === $count) {
			return '0';
		} elseif ($count <= 10) {
			return '1-10';
		} elseif ($count <= 50) {
			return '11-50';
		} elseif ($count <= 100) {
			return '51-100';
		} elseif ($count <= 500) {
			return '101-500';
		} elseif ($count <= 1000) {
			return '501-1000';
		} elseif ($count <= 5000) {
			return '1001-5000';
		}

		return '5000+';
	}

	/**
	 * Get active gateway information.
	 *
	 * @since 2.5.0
	 * @return array
	 */
	protected function get_gateway_data(): array {

		$active_gateways = (array) wu_get_setting('active_gateways', []);

		// Only return gateway IDs, not configuration
		return [
			'active_gateways' => array_values($active_gateways),
			'gateway_count'   => count($active_gateways),
		];
	}

	/**
	 * Maybe send error data if tracking is enabled.
	 *
	 * @since 2.5.0
	 * @param string $handle The log handle.
	 * @param string $message The error message.
	 * @param string $log_level The PSR-3 log level.
	 * @return void
	 */
	public function maybe_send_error(string $handle, string $message, string $log_level = ''): void {

		if ( ! $this->is_tracking_enabled()) {
			return;
		}

		// Only send error-level messages
		if ( ! in_array($log_level, self::ERROR_LOG_LEVELS, true)) {
			return;
		}

		$error_data = $this->prepare_error_data($handle, $message, $log_level);

		// Send asynchronously to avoid blocking
		$this->send_to_api_async($error_data, 'error');
	}

	/**
	 * Handle PHP fatal errors via WordPress fatal error handler.
	 *
	 * This filter fires for all PHP fatal errors. We use it to log errors
	 * to telemetry when tracking is enabled. We always return the original
	 * value to not interfere with WordPress error handling.
	 *
	 * @since 2.5.0
	 * @param bool  $should_handle Whether WordPress should handle this error.
	 * @param array $error Error information from error_get_last().
	 * @return bool The original $should_handle value.
	 */
	public function handle_fatal_error(bool $should_handle, array $error): bool {

		if ( ! $this->is_tracking_enabled()) {
			return $should_handle;
		}

		// Check if error is related to Ultimate Multisite
		$error_file = $error['file'] ?? '';

		if (strpos($error_file, 'ultimate-multisite') === false &&
			strpos($error_file, 'wp-multisite-waas') === false &&
			strpos($error_file, 'wu-') === false) {
			return $should_handle;
		}

		$error_message = sprintf(
			'[PHP %s] %s in %s on line %d',
			$this->get_error_type_name($error['type'] ?? 0),
			$error['message'] ?? 'Unknown error',
			$error['file'] ?? 'unknown',
			$error['line'] ?? 0
		);

		$error_data = $this->prepare_error_data('fatal', $error_message, \Psr\Log\LogLevel::CRITICAL);

		// Send synchronously since we're about to die
		$this->send_to_api($error_data, 'error');

		return $should_handle;
	}

	/**
	 * Get human-readable error type name.
	 *
	 * @since 2.5.0
	 * @param int $type PHP error type constant.
	 * @return string
	 */
	protected function get_error_type_name(int $type): string {

		$types = [
			E_ERROR             => 'Fatal Error',
			E_PARSE             => 'Parse Error',
			E_CORE_ERROR        => 'Core Error',
			E_COMPILE_ERROR     => 'Compile Error',
			E_USER_ERROR        => 'User Error',
			E_RECOVERABLE_ERROR => 'Recoverable Error',
		];

		return $types[ $type ] ?? 'Error';
	}

	/**
	 * Customize the fatal error message for network sites.
	 *
	 * @since 2.5.0
	 * @param string $message The error message HTML.
	 * @param array  $error Error information from error_get_last().
	 * @return string
	 */
	public function customize_fatal_error_message(string $message, array $error): string {

		// Only customize for errors related to Ultimate Multisite
		$error_file = $error['file'] ?? '';

		if (strpos($error_file, 'ultimate-multisite') === false &&
			strpos($error_file, 'wp-multisite-waas') === false) {
			return $message;
		}

		$custom_message = __('There has been a critical error on this site.', 'ultimate-multisite');

		if (is_multisite()) {
			$custom_message .= ' ' . __('Please contact your network administrator for assistance.', 'ultimate-multisite');
		}

		// Get network admin email if available
		$admin_email = get_site_option('admin_email', '');

		if ($admin_email && is_multisite()) {
			$custom_message .= ' ' . sprintf(
				/* translators: %s is the admin email address */
				__('You can reach them at %s.', 'ultimate-multisite'),
				'<a href="mailto:' . esc_attr($admin_email) . '">' . esc_html($admin_email) . '</a>'
			);
		}

		// Link to support for super admins, main site for regular users
		if (is_super_admin()) {
			$help_url  = 'https://ultimatemultisite.com/support/';
			$help_text = __('Get support', 'ultimate-multisite');
		} else {
			$help_url  = network_home_url('/');
			$help_text = __('Return to the main site', 'ultimate-multisite');
		}

		$message = sprintf(
			'<p>%s</p><p><a href="%s">%s</a></p>',
			$custom_message,
			esc_url($help_url),
			$help_text
		);

		return $message;
	}

	/**
	 * Prepare error data for sending.
	 *
	 * @since 2.5.0
	 * @param string $handle The log handle.
	 * @param string $message The error message.
	 * @param string $log_level The PSR-3 log level.
	 * @return array
	 */
	protected function prepare_error_data(string $handle, string $message, string $log_level = ''): array {

		return [
			'tracker_version' => '1.0.0',
			'timestamp'       => time(),
			'site_hash'       => $this->get_site_hash(),
			'type'            => 'error',
			'log_level'       => $log_level,
			'handle'          => $this->sanitize_log_handle($handle),
			'message'         => $this->sanitize_error_message($message),
			'environment'     => [
				'php_version'    => PHP_VERSION,
				'wp_version'     => get_bloginfo('version'),
				'plugin_version' => wu_get_version(),
				'is_subdomain'   => is_subdomain_install(),
			],
		];
	}

	/**
	 * Sanitize log handle for sending.
	 *
	 * @since 2.5.0
	 * @param string $handle The log handle.
	 * @return string
	 */
	protected function sanitize_log_handle(string $handle): string {

		return sanitize_key($handle);
	}

	/**
	 * Sanitize error message to remove sensitive data.
	 *
	 * @since 2.5.0
	 * @param string $message The error message.
	 * @return string
	 */
	protected function sanitize_error_message(string $message): string {

		// Remove file paths (Unix and Windows)
		$message = preg_replace('/\/[^\s\'"]+/', '[path]', $message);
		$message = preg_replace('/[A-Z]:\\\\[^\s\'"]+/', '[path]', $message);

		// Remove potential domain names
		$message = preg_replace('/https?:\/\/[^\s\'"]+/', '[url]', $message);
		$message = preg_replace('/[a-zA-Z0-9][a-zA-Z0-9\-]*\.[a-zA-Z]{2,}/', '[domain]', $message);

		// Remove potential email addresses
		$message = preg_replace('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', '[email]', $message);

		// Remove potential IP addresses
		$message = preg_replace('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', '[ip]', $message);

		// Limit message length
		return substr($message, 0, 1000);
	}

	/**
	 * Send data to the API endpoint.
	 *
	 * @since 2.5.0
	 * @param array  $data The data to send.
	 * @param string $type The type of data (usage|error).
	 * @return array|\WP_Error
	 */
	protected function send_to_api(array $data, string $type) {

		$url = add_query_arg('type', $type, self::API_URL);

		$response = wp_safe_remote_post(
			$url,
			[
				'method'      => 'POST',
				'timeout'     => 15,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers'     => [
					'Content-Type' => 'application/json',
					'User-Agent'   => 'UltimateMultisite/' . wu_get_version(),
				],
				'body'        => wp_json_encode($data),
			]
		);

		if (is_wp_error($response)) {
			Logger::add('tracker', 'Failed to send tracking data: ' . $response->get_error_message());
		}

		return $response;
	}

	/**
	 * Send data to the API asynchronously.
	 *
	 * @since 2.5.0
	 * @param array  $data The data to send.
	 * @param string $type The type of data.
	 * @return void
	 */
	protected function send_to_api_async(array $data, string $type): void {

		$url = add_query_arg('type', $type, self::API_URL);

		wp_safe_remote_post(
			$url,
			[
				'method'      => 'POST',
				'timeout'     => 0.01,  // Non-blocking
				'redirection' => 0,
				'httpversion' => '1.1',
				'blocking'    => false,
				'headers'     => [
					'Content-Type' => 'application/json',
					'User-Agent'   => 'UltimateMultisite/' . wu_get_version(),
				],
				'body'        => wp_json_encode($data),
			]
		);
	}
}
