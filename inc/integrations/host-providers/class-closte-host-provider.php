<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Closte.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Closte
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Closte_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'closte';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Closte';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Closte-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
		'autossl',
		'no-instructions',
		'no-config',
	];

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = [
		'CLOSTE_CLIENT_API_KEY',
	];

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function detect() {

		return defined('CLOSTE_CLIENT_API_KEY') && CLOSTE_CLIENT_API_KEY;
	}

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id): void {

		wu_log_add('integration-closte', sprintf('Adding domain: %s for site ID: %d', $domain, $site_id));

		// First add the domain alias
		$domain_response = $this->send_closte_api_request(
			'/adddomainalias',
			[
				'domain'   => $domain,
				'wildcard' => str_starts_with($domain, '*.'),
			]
		);

		// Check if domain was added successfully, then request SSL
		if (wu_get_isset($domain_response, 'success', false)) {
			wu_log_add('integration-closte', sprintf('Domain %s added successfully, requesting SSL certificate', $domain));
			$this->request_ssl_certificate($domain);
		} elseif (isset($domain_response['error']) && $domain_response['error'] === 'Invalid or empty domain: ' . $domain) {
			wu_log_add('integration-closte', sprintf('Domain %s rejected by Closte API as invalid. This may be expected for Closte subdomains or internal domains.', $domain));
		} else {
			wu_log_add('integration-closte', sprintf('Failed to add domain %s. Response: %s', $domain, wp_json_encode($domain_response)));

			// Only try SSL if it's not a domain validation error
			if (! isset($domain_response['error']) || ! str_contains($domain_response['error'], 'Invalid or empty domain')) {
				wu_log_add('integration-closte', sprintf('Attempting SSL certificate request for %s despite domain addition failure', $domain));
				$this->request_ssl_certificate($domain);
			}
		}
	}

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_domain($domain, $site_id): void {

		$this->send_closte_api_request(
			'/deletedomainalias',
			[
				'domain'   => $domain,
				'wildcard' => str_starts_with($domain, '*.'),
			]
		);
	}

	/**
	 * This method gets called when a new subdomain is being added.
	 *
	 * This happens every time a new site is added to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being added to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_subdomain($subdomain, $site_id) {}

	/**
	 * This method gets called when a new subdomain is being removed.
	 *
	 * This happens every time a new site is removed to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being removed to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_subdomain($subdomain, $site_id) {}

	/**
	 * Requests an SSL certificate for a domain.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain to request SSL certificate for.
	 * @return array|object
	 */
	private function request_ssl_certificate($domain) {

		wu_log_add('integration-closte', sprintf('Requesting SSL certificate for domain: %s', $domain));

		// Try different possible SSL endpoints
		$ssl_endpoints = [
			'/ssl/install',
			'/installssl',
			'/ssl',
			'/certificate/install',
		];

		$ssl_response = null;

		foreach ($ssl_endpoints as $endpoint) {
			wu_log_add('integration-closte', sprintf('Trying SSL endpoint: %s', $endpoint));

			$ssl_response = $this->send_closte_api_request(
				$endpoint,
				[
					'domain' => $domain,
					'type'   => 'letsencrypt',
				]
			);

			// If we get something other than 400/404, we found a working endpoint
			if (! isset($ssl_response['error']) || ! preg_match('/HTTP [45]\d\d/', $ssl_response['error'])) {
				wu_log_add('integration-closte', sprintf('SSL endpoint %s responded, stopping search', $endpoint));
				break;
			}
		}

		if (wu_get_isset($ssl_response, 'success', false)) {
			wu_log_add('integration-closte', sprintf('SSL certificate request successful for domain: %s', $domain));
		} else {
			wu_log_add('integration-closte', sprintf('SSL certificate request failed for domain: %s. Response: %s', $domain, wp_json_encode($ssl_response)));

			// Note: Closte might handle SSL automatically when domain is added
			wu_log_add('integration-closte', 'Note: Closte may handle SSL automatically when domains are added via /adddomainalias');
		}

		return $ssl_response;
	}

	/**
	 * Checks the SSL certificate status for a domain.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain to check SSL status for.
	 * @return array|object
	 */
	public function check_ssl_status($domain) {

		wu_log_add('integration-closte', sprintf('Checking SSL status for domain: %s', $domain));

		$ssl_status = $this->send_closte_api_request(
			'/ssl/status',
			['domain' => $domain]
		);

		wu_log_add('integration-closte', sprintf('SSL status for domain %s: %s', $domain, wp_json_encode($ssl_status)));

		return $ssl_status;
	}

	/**
	 * Tests the connection with the API.
	 *
	 * Needs to be implemented by integrations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$response = $this->send_closte_api_request('/adddomainalias', []);

		if (wu_get_isset($response, 'error') === 'Invalid or empty domain: ') {
			wp_send_json_success(
				[
					'message' => __('Access Authorized', 'ultimate-multisite'),
				]
			);
		}

		$error = new \WP_Error('not-auth', __('Something went wrong', 'ultimate-multisite'));

		wp_send_json_error($error);
	}

	/**
	 * Sends a request to Closte, with the right API key.
	 *
	 * @since  1.7.3
	 * @param  string $endpoint Endpoint to send the call to.
	 * @param  array  $data     Array containing the params to the call.
	 * @return object
	 */
	public function send_closte_api_request($endpoint, $data) {

		if (defined('CLOSTE_CLIENT_API_KEY') === false) {
			wu_log_add('integration-closte', 'CLOSTE_CLIENT_API_KEY constant not defined');
			return (object) [
				'success' => false,
				'error'   => 'Closte API Key not found.',
			];
		}

		if (empty(CLOSTE_CLIENT_API_KEY)) {
			wu_log_add('integration-closte', 'CLOSTE_CLIENT_API_KEY is empty');
			return (object) [
				'success' => false,
				'error'   => 'Closte API Key is empty.',
			];
		}

		// Try different authentication methods
		$api_key = CLOSTE_CLIENT_API_KEY;

		$post_fields = [
			'blocking' => true,
			'timeout'  => 45,
			'method'   => 'POST',
			'headers'  => [
				'Content-Type' => 'application/x-www-form-urlencoded',
				'User-Agent'   => 'WP-Ultimo-Closte-Integration/2.0',
			],
			'body'     => array_merge(
				[
					'apikey' => $api_key,
				],
				$data
			),
		];

		wu_log_add('integration-closte', sprintf('Using API key (first 10 chars): %s...', substr($api_key, 0, 10)));

		$api_url = 'https://app.closte.com/api/client' . $endpoint;
		wu_log_add('integration-closte', sprintf('Making API request to: %s with data: %s', $api_url, wp_json_encode($data)));

		$response = wp_remote_post($api_url, $post_fields);

		// Log response details
		if (is_wp_error($response)) {
			wu_log_add('integration-closte', sprintf('API request failed: %s', $response->get_error_message()));
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		wu_log_add('integration-closte', sprintf('API response code: %d, body: %s', $response_code, $response_body));

		// Check for HTTP errors
		if ($response_code >= 400) {
			wu_log_add('integration-closte', sprintf('HTTP error %d for endpoint %s', $response_code, $endpoint));
			return (object) [
				'success'       => false,
				'error'         => sprintf('HTTP %d error', $response_code),
				'response_body' => $response_body,
			];
		}

		if ($response_body) {
			$body = json_decode($response_body, true);

			if (json_last_error() === JSON_ERROR_NONE) {
				// Log success/failure for SSL-specific endpoints
				if (strpos($endpoint, 'ssl') !== false) {
					wu_log_add(
						'integration-closte-ssl',
						sprintf('SSL request for %s: %s', $endpoint, wp_json_encode($body))
					);
				}
				return $body;
			}

			wu_log_add('integration-closte', sprintf('JSON decode error: %s', json_last_error_msg()));
			return (object) [
				'success'    => false,
				'error'      => 'Invalid JSON response',
				'json_error' => json_last_error_msg(),
			];
		}

		wu_log_add('integration-closte', 'Empty response body');
		return (object) [
			'success' => false,
			'error'   => 'Empty response',
		];
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Closte is not just another web hosting who advertise their services as a cloud hosting while still provides fixed plans like in 1995.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('closte.svg', 'img/hosts');
	}
}
