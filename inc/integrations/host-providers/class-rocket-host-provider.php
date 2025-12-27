<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Rocket.net.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Rocket_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Rocket_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the ID of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'rocket';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Rocket.net';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Rocket.net-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
		'autossl',
	];

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = [
		'WU_ROCKET_EMAIL',
		'WU_ROCKET_PASSWORD',
		'WU_ROCKET_SITE_ID',
	];

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {

		return str_contains(ABSPATH, 'rocket.net') || str_contains(ABSPATH, 'rocketdotnet');
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_ROCKET_EMAIL'    => [
				'title'       => __('Rocket.net Account Email', 'ultimate-multisite'),
				'desc'        => __('Your Rocket.net account email address.', 'ultimate-multisite'),
				'placeholder' => __('e.g. me@example.com', 'ultimate-multisite'),
				'type'        => 'email',
			],
			'WU_ROCKET_PASSWORD' => [
				'title'       => __('Rocket.net Password', 'ultimate-multisite'),
				'desc'        => __('Your Rocket.net account password.', 'ultimate-multisite'),
				'placeholder' => __('Enter your password', 'ultimate-multisite'),
				'type'        => 'password',
			],
			'WU_ROCKET_SITE_ID'  => [
				'title'       => __('Rocket.net Site ID', 'ultimate-multisite'),
				'desc'        => __('The Site ID from your Rocket.net control panel.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 12345', 'ultimate-multisite'),
			],
		];
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

		$response = $this->send_rocket_request(
			'domains',
			[
				'domain' => $domain,
			],
			'POST'
		);

		if (is_wp_error($response)) {
			wu_log_add('integration-rocket', sprintf('[Add Domain] %s: %s', $domain, $response->get_error_message()), LogLevel::ERROR);
		} else {
			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if (200 === $response_code || 201 === $response_code) {
				wu_log_add('integration-rocket', sprintf('[Add Domain] %s: Success - %s', $domain, $response_body));
			} else {
				wu_log_add('integration-rocket', sprintf('[Add Domain] %s: Failed (HTTP %d) - %s', $domain, $response_code, $response_body), LogLevel::ERROR);
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

		$domain_id = $this->get_rocket_domain_id($domain);

		if (! $domain_id) {
			wu_log_add('integration-rocket', sprintf('[Remove Domain] %s: Domain not found on Rocket.net', $domain), LogLevel::WARNING);

			return;
		}

		$response = $this->send_rocket_request("domains/$domain_id", [], 'DELETE');

		if (is_wp_error($response)) {
			wu_log_add('integration-rocket', sprintf('[Remove Domain] %s: %s', $domain, $response->get_error_message()), LogLevel::ERROR);
		} else {
			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);

			if (200 === $response_code || 204 === $response_code) {
				wu_log_add('integration-rocket', sprintf('[Remove Domain] %s: Success - %s', $domain, $response_body));
			} else {
				wu_log_add('integration-rocket', sprintf('[Remove Domain] %s: Failed (HTTP %d) - %s', $domain, $response_code, $response_body), LogLevel::ERROR);
			}
		}
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
	public function on_add_subdomain($subdomain, $site_id) {
		// Rocket.net manages subdomains automatically via wildcard SSL
		// No action needed
	}

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
	public function on_remove_subdomain($subdomain, $site_id) {
		// Rocket.net manages subdomains automatically via wildcard SSL
		// No action needed
	}

	/**
	 * Tests the connection with the Rocket.net API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$response = $this->send_rocket_request('domains', [], 'GET');

		if (is_wp_error($response)) {
			wp_send_json_error(
				[
					'message' => $response->get_error_message(),
				]
			);
		}

		$response_code = wp_remote_retrieve_response_code($response);

		if (200 === $response_code) {
			wp_send_json_success(
				[
					'message' => __('Successfully connected to Rocket.net API!', 'ultimate-multisite'),
					'data'    => json_decode(wp_remote_retrieve_body($response)),
				]
			);
		} else {
			wp_send_json_error(
				[
					'message' => sprintf(
						// translators: %1$d: HTTP response code.
						__('Connection failed with HTTP code %1$d: %2$s', 'ultimate-multisite'),
						$response_code,
						wp_remote_retrieve_body($response)
					),
				]
			);
		}
	}

	/**
	 * Returns the base domain API url for our calls.
	 *
	 * @since 2.0.0
	 * @param string $path Path relative to the main endpoint.
	 * @return string
	 */
	protected function get_rocket_base_url($path = ''): string {

		$site_id = defined('WU_ROCKET_SITE_ID') ? WU_ROCKET_SITE_ID : '';

		$base_url = "https://api.rocket.net/v1/sites/{$site_id}";

		if ($path) {
			$base_url .= '/' . ltrim($path, '/');
		}

		return $base_url;
	}

	/**
	 * Fetches and caches a Rocket.net JWT access token.
	 *
	 * @since 2.0.0
	 * @return string|false
	 */
	protected function get_rocket_access_token() {

		$token = get_site_transient('wu_rocket_token');

		if (! $token) {
			$response = wp_remote_post(
				'https://api.rocket.net/v1/auth/login',
				[
					'blocking' => true,
					'method'   => 'POST',
					'timeout'  => 30,
					'headers'  => [
						'Content-Type' => 'application/json',
						'Accept'       => 'application/json',
					],
					'body'     => wp_json_encode(
						[
							'email'    => defined('WU_ROCKET_EMAIL') ? WU_ROCKET_EMAIL : '',
							'password' => defined('WU_ROCKET_PASSWORD') ? WU_ROCKET_PASSWORD : '',
						]
					),
				]
			);

			if (! is_wp_error($response)) {
				$body = json_decode(wp_remote_retrieve_body($response), true);

				if (isset($body['token']) || isset($body['access_token'])) {
					// Handle both possible token field names
					$token = $body['token'] ?? $body['access_token'];

					// Cache token for 50 minutes (tokens typically last 1 hour)
					set_site_transient('wu_rocket_token', $token, 50 * MINUTE_IN_SECONDS);
				} else {
					wu_log_add('integration-rocket', '[Auth] Failed to retrieve token: ' . wp_remote_retrieve_body($response), LogLevel::ERROR);

					return false;
				}
			} else {
				wu_log_add('integration-rocket', '[Auth] ' . $response->get_error_message(), LogLevel::ERROR);

				return false;
			}
		}

		return $token;
	}

	/**
	 * Sends a request to the Rocket.net API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The API endpoint (relative to /sites/{id}/).
	 * @param array  $data The data to send.
	 * @param string $method The HTTP verb.
	 * @return array|\WP_Error
	 */
	protected function send_rocket_request($endpoint, $data = [], $method = 'POST') {

		$token = $this->get_rocket_access_token();

		if (! $token) {
			return new \WP_Error('no_token', __('Failed to authenticate with Rocket.net API', 'ultimate-multisite'));
		}

		$url = $this->get_rocket_base_url($endpoint);

		$args = [
			'blocking' => true,
			'method'   => $method,
			'timeout'  => 60,
			'headers'  => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			],
		];

		if (! empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		return $response;
	}

	/**
	 * Returns the Rocket.net domain ID for a given domain name.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name.
	 * @return int|false The domain ID or false if not found.
	 */
	protected function get_rocket_domain_id($domain) {

		$response = $this->send_rocket_request('domains', [], 'GET');

		if (is_wp_error($response)) {
			wu_log_add('integration-rocket', '[Get Domain ID] ' . $response->get_error_message(), LogLevel::ERROR);

			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		// Handle different possible response structures
		$domains = $body['data'] ?? $body['domains'] ?? $body;

		if (is_array($domains)) {
			foreach ($domains as $remote_domain) {
				$domain_name = $remote_domain['domain'] ?? $remote_domain['name'] ?? null;

				if ($domain_name === $domain) {
					return $remote_domain['id'] ?? false;
				}
			}
		}

		return false;
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/rocket-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Rocket.net is a fully API-driven managed WordPress hosting platform built for speed, security, and scalability. With edge-first private cloud infrastructure and automatic SSL management, Rocket.net makes it easy to deploy and manage WordPress sites at scale.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('rocket.svg', 'img/hosts');
	}
}
