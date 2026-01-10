<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Laravel Forge.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Laravel_Forge_Host_Provider
 * @since 2.3.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;
use WP_Error;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Laravel Forge hosting provider integration for domain mapping and SSL.
 *
 * This integration supports:
 * - Single server setups
 * - Multi-server setups with load balancer
 * - Auto SSL via Let's Encrypt
 * - Custom deploy commands
 */
class Laravel_Forge_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Integration identifier.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id = 'laravel-forge';

	/**
	 * Integration display title.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title = 'Laravel Forge';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Laravel-Forge-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $supports = [
		'autossl',
	];

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $constants = [
		'WU_FORGE_API_TOKEN',
		'WU_FORGE_SERVER_ID',
		'WU_FORGE_SITE_ID',
	];

	/**
	 * Optional constants that may be present on wp-config.php.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $optional_constants = [
		'WU_FORGE_LOAD_BALANCER_SERVER_ID',
		'WU_FORGE_LOAD_BALANCER_SITE_ID',
		'WU_FORGE_ADDITIONAL_SERVER_IDS',
		'WU_FORGE_DEPLOY_COMMAND',
		'WU_FORGE_SYMLINK_TARGET',
	];

	/**
	 * Laravel Forge API base URL.
	 *
	 * @var string
	 */
	private const API_BASE_URL = 'https://forge.laravel.com/api/v1';

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function detect(): bool {

		return str_contains(ABSPATH, 'forge');
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields(): array {

		return [
			'WU_FORGE_API_TOKEN'               => [
				'title'       => __('Laravel Forge API Token', 'ultimate-multisite'),
				'desc'        => __('Create an API token in your Forge account under Account Settings > API.', 'ultimate-multisite'),
				'placeholder' => __('e.g. eyJ0eXAiOiJKV1QiLCJhbGci...', 'ultimate-multisite'),
				'type'        => 'password',
			],
			'WU_FORGE_SERVER_ID'               => [
				'title'       => __('Primary Server ID', 'ultimate-multisite'),
				'desc'        => __('The ID of your primary Forge server. Find this in the URL when viewing your server.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 847175', 'ultimate-multisite'),
			],
			'WU_FORGE_SITE_ID'                 => [
				'title'       => __('Primary Site ID', 'ultimate-multisite'),
				'desc'        => __('The ID of your WordPress site on Forge. Find this in the URL when viewing your site.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 12345678', 'ultimate-multisite'),
			],
			'WU_FORGE_LOAD_BALANCER_SERVER_ID' => [
				'title'       => __('Load Balancer Server ID (Optional)', 'ultimate-multisite'),
				'desc'        => __('If using a load balancer, enter its server ID.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 847175', 'ultimate-multisite'),
			],
			'WU_FORGE_LOAD_BALANCER_SITE_ID'   => [
				'title'       => __('Load Balancer Site ID (Optional)', 'ultimate-multisite'),
				'desc'        => __('If using a load balancer, enter the site ID on the load balancer.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 12345678', 'ultimate-multisite'),
			],
			'WU_FORGE_ADDITIONAL_SERVER_IDS'   => [
				'title'       => __('Additional Server IDs (Optional)', 'ultimate-multisite'),
				'desc'        => __('Comma-separated list of additional server IDs for multi-server setups.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 847176,847177', 'ultimate-multisite'),
			],
			'WU_FORGE_DEPLOY_COMMAND'          => [
				'title'       => __('Deploy Command (Optional)', 'ultimate-multisite'),
				'desc'        => __('Command to run after domain is added. Use {domain} as placeholder.', 'ultimate-multisite'),
				'placeholder' => __('e.g. cd /home/forge/{domain} && git pull', 'ultimate-multisite'),
			],
			'WU_FORGE_SYMLINK_TARGET'          => [
				'title'       => __('Symlink Target (Optional)', 'ultimate-multisite'),
				'desc'        => __('Path to symlink new domain sites to. Use {domain} as placeholder for the new domain.', 'ultimate-multisite'),
				'placeholder' => __('e.g. /home/forge/main-site.com/public', 'ultimate-multisite'),
			],
		];
	}

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id): void {

		$servers = $this->get_server_list();

		if (empty($servers)) {
			wu_log_add('integration-forge', 'No servers configured', LogLevel::ERROR);

			return;
		}

		$load_balancer_server_id = $this->get_load_balancer_server_id();
		$load_balancer_site_id   = null;
		$created_sites           = [];

		foreach ($servers as $server_id) {
			$is_load_balancer = $load_balancer_server_id && (string) $server_id === (string) $load_balancer_server_id;

			$result = $this->create_site_on_server($server_id, $domain);

			if (is_wp_error($result)) {
				wu_log_add(
					'integration-forge',
					sprintf('Failed to create site on server %s: %s', $server_id, $result->get_error_message()),
					LogLevel::ERROR
				);

				continue;
			}

			if (isset($result['site']['id'])) {
				$forge_site_id = $result['site']['id'];

				wu_log_add(
					'integration-forge',
					sprintf('Site created on server %s with ID %s', $server_id, $forge_site_id)
				);

				if ($is_load_balancer) {
					$load_balancer_site_id = $forge_site_id;
				} else {
					$created_sites[] = [
						'server_id' => $server_id,
						'site_id'   => $forge_site_id,
					];
				}
			}
		}

		// Setup SSL on load balancer or primary server.
		$ssl_server_id = $load_balancer_server_id ?: $this->get_primary_server_id();
		$ssl_site_id   = $load_balancer_site_id ?: $this->get_primary_site_id();

		if ($ssl_server_id && $ssl_site_id) {
			$this->install_ssl_certificate($ssl_server_id, $ssl_site_id, $domain);
		}

		// Configure load balancing if applicable.
		if ($load_balancer_server_id && $load_balancer_site_id && ! empty($created_sites)) {
			$this->configure_load_balancing($load_balancer_server_id, $load_balancer_site_id, $created_sites);
		}

		// Run deploy commands on backend servers.
		if (! empty($created_sites)) {
			$this->run_deploy_commands($created_sites, $domain);
		}
	}

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_domain($domain, $site_id): void {

		$servers = $this->get_server_list();

		foreach ($servers as $server_id) {
			$forge_site_id = $this->find_site_by_domain($server_id, $domain);

			if (! $forge_site_id) {
				wu_log_add(
					'integration-forge',
					sprintf('Site not found for domain %s on server %s', $domain, $server_id)
				);

				continue;
			}

			$response = $this->send_forge_request(
				sprintf('/servers/%s/sites/%s', $server_id, $forge_site_id),
				[],
				'DELETE'
			);

			if (is_wp_error($response)) {
				wu_log_add(
					'integration-forge',
					sprintf('Failed to delete site on server %s: %s', $server_id, $response->get_error_message()),
					LogLevel::ERROR
				);
			} else {
				wu_log_add(
					'integration-forge',
					sprintf('Site deleted for domain %s on server %s', $domain, $server_id)
				);
			}
		}
	}

	/**
	 * This method gets called when a new subdomain is being added.
	 *
	 * @since 2.3.0
	 *
	 * @param string $subdomain The subdomain being added to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_subdomain($subdomain, $site_id): void {
		// Subdomains are typically handled by wildcard DNS/SSL in Forge.
		// No action needed unless specific subdomain handling is required.
	}

	/**
	 * This method gets called when a new subdomain is being removed.
	 *
	 * @since 2.3.0
	 *
	 * @param string $subdomain The subdomain being removed from the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_subdomain($subdomain, $site_id): void {
		// Subdomains are typically handled by wildcard DNS/SSL in Forge.
		// No action needed unless specific subdomain handling is required.
	}

	/**
	 * Tests the connection with the Laravel Forge API.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function test_connection(): void {

		$server_id = $this->get_primary_server_id();

		if (! $server_id) {
			wp_send_json_error(
				new WP_Error('missing_server_id', __('Server ID is not configured.', 'ultimate-multisite'))
			);
		}

		$response = $this->send_forge_request(
			sprintf('/servers/%s', $server_id),
			[],
			'GET'
		);

		if (is_wp_error($response)) {
			wp_send_json_error($response);
		}

		$body = $this->parse_response($response);

		if (is_wp_error($body)) {
			wp_send_json_error($body);
		}

		wp_send_json_success($body);
	}

	/**
	 * Creates a site on a specific Forge server.
	 *
	 * @since 2.3.0
	 *
	 * @param int    $server_id The Forge server ID.
	 * @param string $domain The domain name.
	 * @return array|WP_Error
	 */
	protected function create_site_on_server(int $server_id, string $domain) {

		$response = $this->send_forge_request(
			sprintf('/servers/%s/sites', $server_id),
			[
				'domain'       => $domain,
				'project_type' => 'php',
				'directory'    => '/public',
			],
			'POST'
		);

		if (is_wp_error($response)) {
			return $response;
		}

		return $this->parse_response($response);
	}

	/**
	 * Installs a Let's Encrypt SSL certificate on a site.
	 *
	 * @since 2.3.0
	 *
	 * @param int    $server_id The Forge server ID.
	 * @param int    $site_id The Forge site ID.
	 * @param string $domain The domain name.
	 * @return void
	 */
	protected function install_ssl_certificate(int $server_id, int $site_id, string $domain): void {

		$domains = [$domain];

		// Add www subdomain if appropriate.
		if (! str_starts_with($domain, 'www.') && ! str_starts_with($domain, '*.')) {
			$domains[] = 'www.' . $domain;
		}

		$response = $this->send_forge_request(
			sprintf('/servers/%s/sites/%s/certificates/letsencrypt', $server_id, $site_id),
			['domains' => $domains],
			'POST'
		);

		if (is_wp_error($response)) {
			wu_log_add(
				'integration-forge',
				sprintf('Failed to install SSL for %s: %s', $domain, $response->get_error_message()),
				LogLevel::ERROR
			);
		} else {
			wu_log_add(
				'integration-forge',
				sprintf('SSL certificate requested for %s on server %s', $domain, $server_id)
			);
		}
	}

	/**
	 * Configures load balancing for a site.
	 *
	 * @since 2.3.0
	 *
	 * @param int   $lb_server_id Load balancer server ID.
	 * @param int   $lb_site_id Load balancer site ID.
	 * @param array $backend_servers Array of backend server/site pairs.
	 * @return void
	 */
	protected function configure_load_balancing(int $lb_server_id, int $lb_site_id, array $backend_servers): void {

		$servers = [];

		foreach ($backend_servers as $backend) {
			$servers[] = [
				'id'     => (int) $backend['server_id'],
				'weight' => 1,
			];
		}

		$response = $this->send_forge_request(
			sprintf('/servers/%s/sites/%s/balancing', $lb_server_id, $lb_site_id),
			[
				'servers' => $servers,
				'method'  => 'least_conn',
			],
			'PUT'
		);

		if (is_wp_error($response)) {
			wu_log_add(
				'integration-forge',
				sprintf('Failed to configure load balancing: %s', $response->get_error_message()),
				LogLevel::ERROR
			);
		} else {
			wu_log_add('integration-forge', 'Load balancing configured successfully');
		}
	}

	/**
	 * Runs deploy commands on the created sites.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $sites Array of server/site pairs.
	 * @param string $domain The domain name.
	 * @return void
	 */
	protected function run_deploy_commands(array $sites, string $domain): void {

		$command = $this->get_deploy_command($domain);

		if (empty($command)) {
			return;
		}

		foreach ($sites as $site) {
			$response = $this->send_forge_request(
				sprintf('/servers/%s/sites/%s/commands', $site['server_id'], $site['site_id']),
				['command' => $command],
				'POST'
			);

			if (is_wp_error($response)) {
				wu_log_add(
					'integration-forge',
					sprintf(
						'Failed to run deploy command on server %s: %s',
						$site['server_id'],
						$response->get_error_message()
					),
					LogLevel::ERROR
				);
			} else {
				wu_log_add(
					'integration-forge',
					sprintf('Deploy command executed on server %s for site %s', $site['server_id'], $site['site_id'])
				);
			}
		}
	}

	/**
	 * Finds a site by domain on a specific server.
	 *
	 * @since 2.3.0
	 *
	 * @param int    $server_id The Forge server ID.
	 * @param string $domain The domain name to search for.
	 * @return int|false Site ID if found, false otherwise.
	 */
	protected function find_site_by_domain(int $server_id, string $domain) {

		$response = $this->send_forge_request(
			sprintf('/servers/%s/sites', $server_id),
			[],
			'GET'
		);

		if (is_wp_error($response)) {
			return false;
		}

		$body = $this->parse_response($response);

		if (is_wp_error($body) || ! isset($body['sites'])) {
			return false;
		}

		foreach ($body['sites'] as $site) {
			if (isset($site['name']) && $site['name'] === $domain) {
				return (int) $site['id'];
			}
		}

		return false;
	}

	/**
	 * Sends a request to the Laravel Forge API.
	 *
	 * @since 2.3.0
	 *
	 * @param string $endpoint The API endpoint (without base URL).
	 * @param array  $data The data to send.
	 * @param string $method The HTTP method.
	 * @return array|WP_Error
	 */
	protected function send_forge_request(string $endpoint, array $data = [], string $method = 'POST') {

		$token = $this->get_api_token();

		if (empty($token)) {
			return new WP_Error(
				'missing_token',
				__('Laravel Forge API token is not configured.', 'ultimate-multisite')
			);
		}

		$url = self::API_BASE_URL . '/' . ltrim($endpoint, '/');

		$args = [
			'method'  => $method,
			'timeout' => 60,
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
			],
		];

		if ('GET' === $method) {
			if (! empty($data)) {
				$url = add_query_arg($data, $url);
			}
		} else {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		// Log the request for debugging.
		$log_message = sprintf(
			"Request: %s %s\nStatus: %s\nResponse: %s",
			$method,
			$url,
			is_wp_error($response) ? 'ERROR' : wp_remote_retrieve_response_code($response),
			is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response)
		);
		wu_log_add('integration-forge', $log_message);

		return $response;
	}

	/**
	 * Parses an API response.
	 *
	 * @since 2.3.0
	 *
	 * @param array|WP_Error $response The HTTP response.
	 * @return array|WP_Error
	 */
	protected function parse_response($response) {

		if (is_wp_error($response)) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		$body        = wp_remote_retrieve_body($response);
		$decoded     = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return new WP_Error(
				'json_error',
				// translators: %s is the JSON error message.
				sprintf(__('Invalid JSON response: %s', 'ultimate-multisite'), json_last_error_msg())
			);
		}

		// Handle error responses from Forge API.
		if ($status_code >= 400) {
			$error_message = isset($decoded['message']) ? $decoded['message'] : __('Unknown API error', 'ultimate-multisite');

			return new WP_Error('api_error', $error_message);
		}

		return $decoded;
	}

	/**
	 * Gets the API token from constants.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	protected function get_api_token(): string {

		return defined('WU_FORGE_API_TOKEN') ? WU_FORGE_API_TOKEN : '';
	}

	/**
	 * Gets the primary server ID from constants.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	protected function get_primary_server_id(): int {

		return defined('WU_FORGE_SERVER_ID') ? (int) WU_FORGE_SERVER_ID : 0;
	}

	/**
	 * Gets the primary site ID from constants.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	protected function get_primary_site_id(): int {

		return defined('WU_FORGE_SITE_ID') ? (int) WU_FORGE_SITE_ID : 0;
	}

	/**
	 * Gets the load balancer server ID from constants.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	protected function get_load_balancer_server_id(): int {

		return defined('WU_FORGE_LOAD_BALANCER_SERVER_ID') ? (int) WU_FORGE_LOAD_BALANCER_SERVER_ID : 0;
	}

	/**
	 * Gets the load balancer site ID from constants.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	protected function get_load_balancer_site_id(): int {

		return defined('WU_FORGE_LOAD_BALANCER_SITE_ID') ? (int) WU_FORGE_LOAD_BALANCER_SITE_ID : 0;
	}

	/**
	 * Gets the list of all servers (primary + additional + load balancer).
	 *
	 * @since 2.3.0
	 * @return array
	 */
	protected function get_server_list(): array {

		$servers = [];

		// Add load balancer first if configured.
		$lb_server_id = $this->get_load_balancer_server_id();
		if ($lb_server_id) {
			$servers[] = $lb_server_id;
		}

		// Add primary server.
		$primary_server_id = $this->get_primary_server_id();
		if ($primary_server_id && ! in_array($primary_server_id, $servers, true)) {
			$servers[] = $primary_server_id;
		}

		// Add additional servers.
		if (defined('WU_FORGE_ADDITIONAL_SERVER_IDS') && WU_FORGE_ADDITIONAL_SERVER_IDS) {
			$additional = array_filter(array_map('trim', explode(',', WU_FORGE_ADDITIONAL_SERVER_IDS)));

			foreach ($additional as $server_id) {
				$server_id = (int) $server_id;

				if ($server_id && ! in_array($server_id, $servers, true)) {
					$servers[] = $server_id;
				}
			}
		}

		return $servers;
	}

	/**
	 * Gets the deploy command with placeholders replaced.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain name.
	 * @return string
	 */
	protected function get_deploy_command(string $domain): string {

		$command = '';

		// Check for custom command.
		if (defined('WU_FORGE_DEPLOY_COMMAND') && WU_FORGE_DEPLOY_COMMAND) {
			$command = WU_FORGE_DEPLOY_COMMAND;
		} elseif (defined('WU_FORGE_SYMLINK_TARGET') && WU_FORGE_SYMLINK_TARGET) {
			// Build symlink command if target is specified.
			$target  = str_replace('{domain}', $domain, WU_FORGE_SYMLINK_TARGET);
			$command = sprintf(
				'rm -rf /home/forge/%s/* && ln -s %s /home/forge/%s/public',
				$domain,
				$target,
				$domain
			);
		}

		// Replace {domain} placeholder.
		return str_replace('{domain}', $domain, $command);
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/laravel-forge-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_description(): string {

		return __('Laravel Forge is a server management tool for PHP applications. This integration automatically adds mapped domains to your Forge servers, configures load balancing, and provisions SSL certificates.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_logo(): string {

		return wu_get_asset('laravel-forge.svg', 'img/hosts');
	}

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_explainer_lines(): array {

		$lines = parent::get_explainer_lines();

		$lines['will']['create_sites']   = __('Create new sites on your Forge servers for each mapped domain', 'ultimate-multisite');
		$lines['will']['load_balancing'] = __('Configure load balancing when using multiple backend servers', 'ultimate-multisite');

		if (defined('WU_FORGE_DEPLOY_COMMAND') || defined('WU_FORGE_SYMLINK_TARGET')) {
			$lines['will']['deploy'] = __('Run deploy commands or create symlinks after site creation', 'ultimate-multisite');
		}

		return $lines;
	}
}
