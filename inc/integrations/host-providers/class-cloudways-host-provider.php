<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Cloudways.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Cloudways_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;
use WP_Ultimo\Domain_Mapping\Helper;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Cloudways_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cloudways';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Cloudways';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Cloudways-Integration';

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
		'WU_CLOUDWAYS_EMAIL',
		'WU_CLOUDWAYS_API_KEY',
		'WU_CLOUDWAYS_SERVER_ID',
		'WU_CLOUDWAYS_APP_ID',
	];

	/**
	 * Constants that maybe present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = [
		'WU_CLOUDWAYS_EXTRA_DOMAINS',
	];

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function init(): void {

		parent::init();

		// Add the action to sync domains SSL.
		add_action('wu_domain_manager_dns_propagation_finished', [$this, 'request_ssl'], 10, 0);
	}

	/**
	 * Runs a request to Cloudways API to install SSL, after ensuring that the domain is already mapped.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function request_ssl(): void {
		/**
		 * If the integration is not active, bail.
		 */
		if ( ! $this->is_enabled()) {
			return;
		}

		$all_domains = $this->get_domains();

		$ssl_response = $this->send_cloudways_request(
			'/security/lets_encrypt_install',
			[
				'ssl_domains' => $this->get_valid_ssl_domains($all_domains),
			]
		);

		if (is_wp_error($ssl_response)) {
			wu_log_add('integration-cloudways', '[SSL]' . $ssl_response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-cloudways', '[SSL]' . print_r($ssl_response, true)); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {

		return str_contains(ABSPATH, 'cloudways');
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_CLOUDWAYS_EMAIL'         => [
				'title'       => __('Cloudways Account Email', 'multisite-ultimate'),
				'desc'        => __('Your Cloudways account email address.', 'multisite-ultimate'),
				'placeholder' => __('e.g. me@gmail.com', 'multisite-ultimate'),
			],
			'WU_CLOUDWAYS_API_KEY'       => [
				'title'       => __('Cloudways API Key', 'multisite-ultimate'),
				'desc'        => __('The API Key retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. eYP0Jo3Fzzm5SOZCi5nLR0Mki2lbYZ', 'multisite-ultimate'),
			],
			'WU_CLOUDWAYS_SERVER_ID'     => [
				'title'       => __('Cloudways Server ID', 'multisite-ultimate'),
				'desc'        => __('The Server ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 11667', 'multisite-ultimate'),
			],
			'WU_CLOUDWAYS_APP_ID'        => [
				'title'       => __('Cloudways App ID', 'multisite-ultimate'),
				'desc'        => __('The App ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 940288', 'multisite-ultimate'),
			],
			'WU_CLOUDWAYS_EXTRA_DOMAINS' => [
				'title'       => __('Cloudways Extra Domains', 'multisite-ultimate'),
				'tooltip'     => __('The Cloudways API is a bit strange in that it doesn’t offer a way to add or remove just one domain, only a way to update the whole domain list. That means that Multisite Ultimate will replace all domains you might have there with the list of mapped domains of the network every time a new domain is added.', 'multisite-ultimate'),
				'desc'        => __('Comma-separated list of additional domains to add to Cloudways.', 'multisite-ultimate'),
				'placeholder' => __('e.g. *.test.com, test.com', 'multisite-ultimate'),
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

		$this->sync_domains();
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

		$this->sync_domains();
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
	}

	/**
	 * Syncs the domains with the Cloudways API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function sync_domains(): void {

		$all_domains = $this->get_domains();

		$alias_response = $this->send_cloudways_request(
			'/app/manage/aliases',
			[
				'aliases' => $all_domains,
			]
		);

		if (is_wp_error($alias_response)) {
			wu_log_add('integration-cloudways', '[Alias]' . $alias_response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-cloudways', '[Alias]' . print_r($alias_response, true)); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Returns an array of valid SSL domains to be used with the Cloudways API based on a list of domains.
	 *
	 * @since 2.1.1
	 * @param array $domains List of domains to be used.
	 */
	private function get_valid_ssl_domains($domains): array {

		$ssl_domains = array_unique(
			array_map(
				function ($domain) {

					if (str_starts_with($domain, '*.')) {
						$domain = str_replace('*.', '', $domain);
					}

					return $domain;
				},
				$domains
			)
		);

		$ssl_valid_domains = $this->check_domain_dns($ssl_domains, Helper::get_network_public_ip());

		$main_domain = get_current_site()->domain;

		// Adds the main domain to the list of valid domains.
		$ssl_valid_domains[] = $main_domain;

		return array_values(array_unique(array_filter($ssl_valid_domains)));
	}

	/**
	 * Returns an array of all domains that should be added to Cloudways.
	 *
	 * @modified 2.1.2 removed support to subdomains due to Cloudways SSL generation limitations.
	 * @since 2.1.1
	 */
	private function get_domains(): array {

		$domain_list = $this->get_domain_list();

		foreach ($domain_list as $naked_domain) {
			if (! str_starts_with((string) $naked_domain, 'www.') && ! str_starts_with((string) $naked_domain, '*.')) {
				$domain_list[] = 'www.' . $naked_domain;
			}
		}

		sort($domain_list);

		return array_values(array_unique(array_filter($domain_list)));
	}

	/**
	 * Validates the DNS records for the domains.
	 * This is used to check if the domains are already pointing to the server.
	 *
	 * @since 2.1.1
	 * @param array  $domain_names Array of domain names to be checked.
	 * @param string $network_ip The IP address of the server.
	 * @return array
	 */
	private function check_domain_dns($domain_names, $network_ip) {

		$valid_domains = [];

		foreach ($domain_names as $domain_name) {
			$response = wp_remote_get('https://dns.google/resolve?name=' . $domain_name);

			if (is_wp_error($response)) {
				wu_log_add('integration-cloudways', $response->get_error_message(), LogLevel::ERROR);

				continue;
			}

			$data = json_decode(wp_remote_retrieve_body($response), true);

			if (isset($data['Answer'])) {
				foreach ($data['Answer'] as $answer) {
					if ($answer['data'] === $network_ip) {
						$valid_domains[] = $domain_name;
						break;
					}
				}
			}
		}

		return $valid_domains;
	}

	/**
	 * Tests the connection with the Cloudways API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$response = $this->send_cloudways_request('/app/manage/fpm_setting', [], 'GET');

		if (is_wp_error($response) || wu_get_isset($response, 'error')) {
			wp_send_json_error($response);
		}

		wp_send_json_success($response);
	}

	/**
	 * Returns an array of all the mapped domains currently on the network
	 *
	 * @since 1.6.0
	 * @return array
	 */
	public function get_all_mapped_domains() {

		global $wpdb;

		$final_domain_list = [];

		// Prepare the query
		$query = "SELECT domain FROM {$wpdb->base_prefix}wu_domain_mappings";

		// Suppress errors in case the table doesn't exist.
		$suppress = $wpdb->suppress_errors();

		$mappings = $wpdb->get_col($query, 0); // phpcs:ignore

		foreach ($mappings as $domain) {
			$final_domain_list[] = $domain;

			if (! str_starts_with((string) $domain, 'www.')) {
				$final_domain_list[] = "www.$domain";
			}
		}

		$wpdb->suppress_errors($suppress);

		return $final_domain_list;
	}

	/**
	 * Get extra domains for Cloudways
	 *
	 * @since 1.6.1
	 * @return array
	 */
	protected function get_domain_list() {

		$domain_list = $this->get_all_mapped_domains();

		$extra_domains = defined('WU_CLOUDWAYS_EXTRA_DOMAINS') && WU_CLOUDWAYS_EXTRA_DOMAINS;

		if ($extra_domains) {
			$extra_domains_list = array_filter(array_map('trim', explode(',', (string) WU_CLOUDWAYS_EXTRA_DOMAINS)));

			$domain_list = array_merge($domain_list, $extra_domains_list);
		}

		return $domain_list;
	}

	/**
	 * Fetches and saves a Cloudways access token.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_cloudways_access_token() {

		$token = get_site_transient('wu_cloudways_token');

		if ( ! $token) {
			$response = wp_remote_post(
				'https://api.cloudways.com/api/v1/oauth/access_token',
				[
					'blocking' => true,
					'method'   => 'POST',
					'headers'  => [
						'cache-control' => 'no-cache',
						'content-type'  => 'application/x-www-form-urlencoded',
					],
					'body'     => [
						'email'   => defined('WU_CLOUDWAYS_EMAIL') ? WU_CLOUDWAYS_EMAIL : '',
						'api_key' => defined('WU_CLOUDWAYS_API_KEY') ? WU_CLOUDWAYS_API_KEY : '',
					],
				]
			);

			if ( ! is_wp_error($response)) {
				$body = json_decode(wp_remote_retrieve_body($response), true);

				if (isset($body['access_token'])) {
					$expires_in = $body['expires_in'] ?? 50 * MINUTE_IN_SECONDS;

					set_site_transient('wu_cloudways_token', $body['access_token'], $expires_in);

					$token = $body['access_token'];
				}
			}
		}

		return $token;
	}

	/**
	 * Sends a request to the Cloudways API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param string $method The HTTP verb.
	 * @return object|\WP_Error
	 */
	protected function send_cloudways_request($endpoint, $data = [], $method = 'POST'): object {

		$token = $this->get_cloudways_access_token();

		$endpoint = '/' . ltrim($endpoint, '/');

		$endpoint_url = "https://api.cloudways.com/api/v1$endpoint";

		if ('GET' === $method) {
			$endpoint_url = add_query_arg(
				[
					'server_id' => defined('WU_CLOUDWAYS_SERVER_ID') ? WU_CLOUDWAYS_SERVER_ID : '',
					'app_id'    => defined('WU_CLOUDWAYS_APP_ID') ? WU_CLOUDWAYS_APP_ID : '',
				],
				$endpoint_url
			);
		} else {
			$data['server_id'] = defined('WU_CLOUDWAYS_SERVER_ID') ? WU_CLOUDWAYS_SERVER_ID : '';
			$data['app_id']    = defined('WU_CLOUDWAYS_APP_ID') ? WU_CLOUDWAYS_APP_ID : '';
			$data['ssl_email'] = defined('WU_CLOUDWAYS_EMAIL') ? WU_CLOUDWAYS_EMAIL : '';
			$data['wild_card'] = false;
		}

		$response = wp_remote_post(
			$endpoint_url,
			[
				'blocking' => true,
				'method'   => $method,
				'timeout'  => 45,
				'body'     => $data,
				'headers'  => [
					'cache-control' => 'no-cache',
					'content-type'  => 'application/x-www-form-urlencoded',
					'authorization' => "Bearer $token",
				],
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$response_data = wp_remote_retrieve_body($response);

		return json_decode($response_data);
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/cloudways-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Focus on your business and avoid all the web hosting hassles. Our managed hosting guarantees unmatched performance, reliability and choice with 24/7 support that acts as your extended team, making Cloudways an ultimate choice for growing agencies and e-commerce businesses.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cloudways.webp', 'img/hosts');
	}
}
