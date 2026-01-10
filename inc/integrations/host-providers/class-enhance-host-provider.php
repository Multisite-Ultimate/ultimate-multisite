<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Enhance Control Panel.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Enhance
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This class implements the Enhance Control Panel integration for SSL and domains.
 */
class Enhance_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the id of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'enhance';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Enhance Control Panel';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Enhance-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
		'autossl',
		'no-instructions',
	];

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = [
		'WU_ENHANCE_API_TOKEN',
		'WU_ENHANCE_API_URL',
		'WU_ENHANCE_ORG_ID',
		'WU_ENHANCE_WEBSITE_ID',
	];

	/**
	 * Constants that are optional on wp-config.php.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = [];

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function detect(): bool {

		return defined('WU_ENHANCE_API_TOKEN') && WU_ENHANCE_API_TOKEN;
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_ENHANCE_API_TOKEN'  => [
				'type'        => 'password',
				'html_attr'   => ['autocomplete' => 'new-password'],
				'title'       => __('Enhance API Token', 'ultimate-multisite'),
				'desc'        => sprintf(
					/* translators: %s is the link to the API token documentation */
					__('Generate an API token in your Enhance Control Panel under Settings &rarr; API Tokens. <a href="%s" target="_blank">Learn more</a>', 'ultimate-multisite'),
					'https://apidocs.enhance.com/#section/Authentication'
				),
				'placeholder' => __('Your bearer token', 'ultimate-multisite'),
			],
			'WU_ENHANCE_API_URL'    => [
				'title'       => __('Enhance API URL', 'ultimate-multisite'),
				'desc'        => __('The API URL of your Enhance Control Panel (e.g., https://your-enhance-server.com/api).', 'ultimate-multisite'),
				'placeholder' => __('e.g. https://your-enhance-server.com/api', 'ultimate-multisite'),
				'html_attr'   => [
					'id' => 'wu_enhance_api_url',
				],
			],
			'WU_ENHANCE_ORG_ID'     => [
				'title'       => __('Organization ID', 'ultimate-multisite'),
				'desc'        => __('The UUID of your organization. You can find this in your Enhance Control Panel URL when viewing the organization (e.g., /org/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).', 'ultimate-multisite'),
				'placeholder' => __('e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'ultimate-multisite'),
				'html_attr'   => [
					'id' => 'wu_enhance_org_id',
				],
			],
			'WU_ENHANCE_WEBSITE_ID' => [
				'title'       => __('Website ID', 'ultimate-multisite'),
				'placeholder' => __('e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'ultimate-multisite'),
				'desc'        => __('The UUID of the website where domains should be added. You can find this in your Enhance Control Panel URL when viewing a website (e.g., /websites/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).', 'ultimate-multisite'),
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

		wu_log_add('integration-enhance', sprintf('Adding domain: %s for site ID: %d', $domain, $site_id));

		$org_id     = defined('WU_ENHANCE_ORG_ID') ? WU_ENHANCE_ORG_ID : '';
		$website_id = defined('WU_ENHANCE_WEBSITE_ID') ? WU_ENHANCE_WEBSITE_ID : '';

		if (empty($org_id)) {
			wu_log_add('integration-enhance', 'Organization ID not configured');
			return;
		}

		if (empty($website_id)) {
			wu_log_add('integration-enhance', 'Website ID not configured');
			return;
		}

		// Add the domain to the website
		// POST /orgs/{org_id}/websites/{website_id}/domains
		$domain_data = [
			'domain' => $domain,
		];

		$domain_response = $this->send_enhance_api_request(
			'/orgs/' . $org_id . '/websites/' . $website_id . '/domains',
			'POST',
			$domain_data
		);

		// Check if domain was added successfully
		if (wu_get_isset($domain_response, 'id') || (isset($domain_response['success']) && $domain_response['success'])) {
			wu_log_add('integration-enhance', sprintf('Domain %s added successfully. SSL will be automatically provisioned via LetsEncrypt when DNS resolves.', $domain));
		} elseif (isset($domain_response['error'])) {
			wu_log_add('integration-enhance', sprintf('Failed to add domain %s. Error: %s', $domain, wp_json_encode($domain_response)));
		} else {
			wu_log_add('integration-enhance', sprintf('Domain %s may have been added, but response was unclear: %s', $domain, wp_json_encode($domain_response)));
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

		wu_log_add('integration-enhance', sprintf('Removing domain: %s for site ID: %d', $domain, $site_id));

		$org_id     = defined('WU_ENHANCE_ORG_ID') ? WU_ENHANCE_ORG_ID : '';
		$website_id = defined('WU_ENHANCE_WEBSITE_ID') ? WU_ENHANCE_WEBSITE_ID : '';

		if (empty($org_id)) {
			wu_log_add('integration-enhance', 'Organization ID not configured');
			return;
		}

		if (empty($website_id)) {
			wu_log_add('integration-enhance', 'Website ID not configured');
			return;
		}

		// First, get the domain ID by listing domains and finding a match
		// GET /orgs/{org_id}/websites/{website_id}/domains
		$domains_list = $this->send_enhance_api_request(
			'/orgs/' . $org_id . '/websites/' . $website_id . '/domains'
		);

		$domain_id = null;

		if (isset($domains_list['items']) && is_array($domains_list['items'])) {
			foreach ($domains_list['items'] as $item) {
				if (isset($item['domain']) && $item['domain'] === $domain) {
					$domain_id = $item['id'];
					break;
				}
			}
		}

		if (empty($domain_id)) {
			wu_log_add('integration-enhance', sprintf('Could not find domain ID for %s, it may have already been removed', $domain));
			return;
		}

		// Delete the domain
		// DELETE /orgs/{org_id}/websites/{website_id}/domains/{domain_id}
		$this->send_enhance_api_request(
			'/orgs/' . $org_id . '/websites/' . $website_id . '/domains/' . $domain_id,
			'DELETE'
		);

		wu_log_add('integration-enhance', sprintf('Domain %s removal request sent', $domain));
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
	public function on_add_subdomain($subdomain, $site_id): void {
		// Enhance handles subdomains similarly to domains
		$this->on_add_domain($subdomain, $site_id);
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
	public function on_remove_subdomain($subdomain, $site_id): void {
		// Enhance handles subdomains similarly to domains
		$this->on_remove_domain($subdomain, $site_id);
	}

	/**
	 * Tests the connection with the API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$org_id     = defined('WU_ENHANCE_ORG_ID') ? WU_ENHANCE_ORG_ID : '';
		$website_id = defined('WU_ENHANCE_WEBSITE_ID') ? WU_ENHANCE_WEBSITE_ID : '';

		if (empty($org_id)) {
			$error = new \WP_Error('no-org-id', __('Organization ID is not configured', 'ultimate-multisite'));
			wp_send_json_error($error);
			return;
		}

		if (empty($website_id)) {
			$error = new \WP_Error('no-website-id', __('Website ID is not configured', 'ultimate-multisite'));
			wp_send_json_error($error);
			return;
		}

		// Test by attempting to get website info.
		// GET /orgs/{org_id}/websites/{website_id}
		$response = $this->send_enhance_api_request(
			'/orgs/' . $org_id . '/websites/' . $website_id
		);

		if (isset($response['id'])) {
			wp_send_json_success(
				[
					'message' => __('Connection successful', 'ultimate-multisite'),
				]
			);
		} else {
			// Translators: %s the full error message.
			$error = new \WP_Error('connection-failed', sprintf(__('Failed to connect to Enhance API: %s', 'ultimate-multisite'), $response['error'] ?? 'Unknown error'));
			wp_send_json_error($error);
		}
	}

	/**
	 * Sends a request to the Enhance API with the configured bearer token.
	 *
	 * @since 2.0.0
	 * @param string       $endpoint API endpoint (relative to base URL).
	 * @param string       $method HTTP method (GET, POST, DELETE, etc.).
	 * @param array|string $data Request body data (for POST/PUT/PATCH).
	 * @return array|object
	 */
	public function send_enhance_api_request($endpoint, $method = 'GET', $data = []) {

		if (defined('WU_ENHANCE_API_TOKEN') === false || empty(WU_ENHANCE_API_TOKEN)) {
			wu_log_add('integration-enhance', 'WU_ENHANCE_API_TOKEN constant not defined or empty');
			return [
				'success' => false,
				'error'   => 'Enhance API Token not found.',
			];
		}

		if (defined('WU_ENHANCE_API_URL') === false || empty(WU_ENHANCE_API_URL)) {
			wu_log_add('integration-enhance', 'WU_ENHANCE_API_URL constant not defined or empty');
			return [
				'success' => false,
				'error'   => 'Enhance API URL not found.',
			];
		}

		$api_token = WU_ENHANCE_API_TOKEN;
		$api_url   = rtrim(WU_ENHANCE_API_URL, '/') . $endpoint;

		$args = [
			'method'  => $method,
			'timeout' => 45,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_token,
				'Content-Type'  => 'application/json',
				'User-Agent'    => 'WP-Ultimo-Enhance-Integration/2.0',
			],
		];

		// Add body for POST/PUT/PATCH methods
		if (in_array($method, ['POST', 'PUT', 'PATCH'], true) && ! empty($data)) {
			$args['body'] = wp_json_encode($data);
		}

		wu_log_add('integration-enhance', sprintf('Making %s request to: %s', $method, $api_url));

		if (! empty($data)) {
			wu_log_add('integration-enhance', sprintf('Request data: %s', wp_json_encode($data)));
		}

		$response = wp_remote_request($api_url, $args);

		// Log response details
		if (is_wp_error($response)) {
			wu_log_add('integration-enhance', sprintf('API request failed: %s', $response->get_error_message()));
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		wu_log_add('integration-enhance', sprintf('API response code: %d, body: %s', $response_code, $response_body));

		// Handle successful responses
		if ($response_code >= 200 && $response_code < 300) {
			if (empty($response_body)) {
				// 204 No Content is success
				return [
					'success' => true,
				];
			}

			$body = json_decode($response_body, true);

			if (json_last_error() === JSON_ERROR_NONE) {
				return $body;
			}

			wu_log_add('integration-enhance', sprintf('JSON decode error: %s', json_last_error_msg()));
			return [
				'success'    => false,
				'error'      => 'Invalid JSON response',
				'json_error' => json_last_error_msg(),
			];
		}

		// Handle error responses
		wu_log_add('integration-enhance', sprintf('HTTP error %d for endpoint %s', $response_code, $endpoint));

		$error_data = [
			'success'       => false,
			'error'         => sprintf('HTTP %d error', $response_code),
			'response_code' => $response_code,
			'response_body' => $response_body,
		];

		// Try to parse error message from response
		if (! empty($response_body)) {
			$error_body = json_decode($response_body, true);
			if (json_last_error() === JSON_ERROR_NONE && isset($error_body['message'])) {
				$error_data['error'] = $error_body['message'];
			}
		}

		return $error_data;
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Enhance is a modern control panel that provides powerful hosting automation and management capabilities.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('enhance.svg', 'img/hosts');
	}

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_explainer_lines() {

		$explainer_lines = [
			'will'     => [
				'send_domains' => __('Add domains to Enhance Control Panel whenever a new domain mapping gets created on your network', 'ultimate-multisite'),
				'autossl'      => __('SSL certificates will be automatically provisioned via LetsEncrypt when DNS resolves', 'ultimate-multisite'),
			],
			'will_not' => [],
		];

		if (is_subdomain_install()) {
			$explainer_lines['will']['send_sub_domains'] = __('Add subdomains to Enhance Control Panel whenever a new site gets created on your network', 'ultimate-multisite');
		}

		return $explainer_lines;
	}
}
