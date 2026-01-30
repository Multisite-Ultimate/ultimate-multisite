<?php
/**
 * External Cron Service Client
 *
 * Handles communication with the External Cron Service API.
 *
 * @package WP_Ultimo
 * @subpackage External_Cron
 * @since 2.3.0
 */

namespace WP_Ultimo\External_Cron;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * External Cron Service Client class.
 *
 * @since 2.3.0
 */
class External_Cron_Service_Client {

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	const API_BASE = 'https://ultimatemultisite.com/wp-json/cron-service/v1';

	/**
	 * API key.
	 *
	 * @var string|null
	 */
	private ?string $api_key = null;

	/**
	 * API secret.
	 *
	 * @var string|null
	 */
	private ?string $api_secret = null;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		$this->api_key    = wu_get_setting('external_cron_api_key');
		$this->api_secret = wu_get_setting('external_cron_api_secret');
	}

	/**
	 * Check if client is authenticated.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_authenticated(): bool {

		return ! empty($this->api_key) && ! empty($this->api_secret);
	}

	/**
	 * Get the API base URL.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_api_base(): string {

		return apply_filters('wu_external_cron_api_base', self::API_BASE);
	}

	/**
	 * Register a site with the service.
	 *
	 * @since 2.3.0
	 * @param array $data Registration data.
	 * @return array|\WP_Error
	 */
	public function register_site(array $data) {

		return $this->request_with_oauth('POST', '/register', $data);
	}

	/**
	 * Unregister a site from the service.
	 *
	 * @since 2.3.0
	 * @return array|\WP_Error
	 */
	public function unregister_site() {

		return $this->request('POST', '/unregister');
	}

	/**
	 * Update site schedules.
	 *
	 * @since 2.3.0
	 * @param int   $site_id   Site ID on the service.
	 * @param array $schedules Array of schedules.
	 * @return array|\WP_Error
	 */
	public function update_schedules(int $site_id, array $schedules) {

		return $this->request('POST', "/sites/{$site_id}/schedules", $schedules);
	}

	/**
	 * Get site execution logs.
	 *
	 * @since 2.3.0
	 * @param int $site_id Site ID on the service.
	 * @param int $limit   Number of logs to retrieve.
	 * @param int $offset  Offset for pagination.
	 * @return array|\WP_Error
	 */
	public function get_logs(int $site_id, int $limit = 50, int $offset = 0) {

		return $this->request(
			'GET',
			"/sites/{$site_id}/logs",
			[
				'limit'  => $limit,
				'offset' => $offset,
			]
		);
	}

	/**
	 * Send heartbeat to the service.
	 *
	 * @since 2.3.0
	 * @return array|\WP_Error
	 */
	public function heartbeat() {

		return $this->request(
			'POST',
			'/heartbeat',
			[
				'site_hash' => $this->get_site_hash(),
				'timestamp' => time(),
			]
		);
	}

	/**
	 * Make an authenticated API request.
	 *
	 * @since 2.3.0
	 * @param string $method   HTTP method.
	 * @param string $endpoint API endpoint.
	 * @param array  $data     Request data.
	 * @return array|\WP_Error
	 */
	public function request(string $method, string $endpoint, array $data = []) {

		if (! $this->is_authenticated()) {
			return new \WP_Error('not_authenticated', __('API credentials not configured.', 'ultimate-multisite'));
		}

		$url = $this->get_api_base() . $endpoint;

		$args = [
			'method'  => $method,
			'timeout' => 30,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode($this->api_key . ':' . $this->api_secret), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Used for HTTP Basic Auth
			],
		];

		if ( ! empty($data)) {
			if ('GET' === $method) {
				$url = add_query_arg($data, $url);
			} else {
				$args['body'] = wp_json_encode($data);
			}
		}

		$response = wp_remote_request($url, $args);

		return $this->handle_response($response);
	}

	/**
	 * Make an OAuth-authenticated request (for initial registration).
	 *
	 * @since 2.3.0
	 * @param string $method   HTTP method.
	 * @param string $endpoint API endpoint.
	 * @param array  $data     Request data.
	 * @return array|\WP_Error
	 */
	public function request_with_oauth(string $method, string $endpoint, array $data = []) {

		// Get OAuth token from addon repository (shares the same OAuth flow).
		$addon_repo   = \WP_Ultimo::get_instance()->get_addon_repository();
		$access_token = $addon_repo->get_access_token();

		if (empty($access_token)) {
			return new \WP_Error('no_oauth_token', __('Please connect your site first via the Addons page.', 'ultimate-multisite'));
		}

		$url = $this->get_api_base() . $endpoint;

		$args = [
			'method'  => $method,
			'timeout' => 30,
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			],
		];

		if (! empty($data)) {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		return $this->handle_response($response);
	}

	/**
	 * Handle API response.
	 *
	 * @since 2.3.0
	 * @param array|\WP_Error $response Response from wp_remote_request.
	 * @return array|\WP_Error
	 */
	private function handle_response($response) {

		if (is_wp_error($response)) {
			return $response;
		}

		$body = wp_remote_retrieve_body($response);
		$code = wp_remote_retrieve_response_code($response);

		$decoded = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return new \WP_Error('invalid_json', __('Invalid response from server.', 'ultimate-multisite'));
		}

		if ($code >= 400) {
			$message = $decoded['message'] ?? __('Unknown error.', 'ultimate-multisite');
			return new \WP_Error('api_error', $message, ['status' => $code]);
		}

		return $decoded;
	}

	/**
	 * Generate a unique site hash.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_site_hash(): string {

		return hash('sha256', network_site_url() . AUTH_KEY);
	}

	/**
	 * Set API credentials.
	 *
	 * @since 2.3.0
	 * @param string $api_key    API key.
	 * @param string $api_secret API secret.
	 */
	public function set_credentials(string $api_key, string $api_secret): void {

		$this->api_key    = $api_key;
		$this->api_secret = $api_secret;
	}
}
