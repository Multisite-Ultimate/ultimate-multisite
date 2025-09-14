<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on ServerPilot.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/ServerPilot_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;

defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class ServerPilot_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'serverpilot';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'ServerPilot';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/ServerPilot-Integration';

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
		'WU_SERVER_PILOT_CLIENT_ID',
		'WU_SERVER_PILOT_API_KEY',
		'WU_SERVER_PILOT_APP_ID',
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

		return preg_match('/\/srv\/users\/(.+)\/apps\/(.+)/', WP_ULTIMO_PLUGIN_DIR);
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.1.2
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_SERVER_PILOT_CLIENT_ID' => [
				'title'       => __('ServerPilot Client ID', 'multisite-ultimate'),
				'desc'        => __('Your ServerPilot Client ID.', 'multisite-ultimate'),
				'placeholder' => __('e.g. cid_lSmjevkdoSOpasYVqm', 'multisite-ultimate'),
			],
			'WU_SERVER_PILOT_API_KEY'   => [
				'title'       => __('ServerPilot API Key', 'multisite-ultimate'),
				'desc'        => __('The API Key retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. eYP0Jo3Fzzm5SOZCi5nLR0Mki2lbYZ', 'multisite-ultimate'),
			],
			'WU_SERVER_PILOT_APP_ID'    => [
				'title'       => __('ServerPilot App ID', 'multisite-ultimate'),
				'desc'        => __('The App ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 940288', 'multisite-ultimate'),
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

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {
			$domains_to_add = [$domain];

			if (\WP_Ultimo\Managers\Domain_Manager::get_instance()->should_create_www_subdomain($domain)) {
				$domains_to_add[] = 'www.' . $domain;
			}

			$this->send_server_pilot_api_request(
				'',
				[
					'domains' => array_merge($current_domain_list, $domains_to_add),
				]
			);

			/**
			 * Makes sure autoSSL is always on
			 */
			$this->turn_server_pilot_auto_ssl_on();
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

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {

			/**
			 * Removes the current domain fromt he domain list
			 */
			$current_domain_list = array_filter($current_domain_list, fn($remote_domain) => $remote_domain !== $domain && 'www.' . $domain !== $remote_domain);

			$this->send_server_pilot_api_request(
				'',
				[
					'domains' => $current_domain_list,
				]
			);
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
	public function on_add_subdomain($subdomain, $site_id): void {

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {
			$this->send_server_pilot_api_request(
				'',
				[
					'domains' => array_merge($current_domain_list, [$subdomain]),
				]
			);

			/**
			 * Makes sure autoSSL is always on
			 */
			$this->turn_server_pilot_auto_ssl_on();
		}
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
	public function on_remove_subdomain($subdomain, $site_id) {}

	/**
	 * Sends a request to ServerPilot, with the right API key.
	 *
	 * @since  1.7.3
	 * @param  string $endpoint Endpoint to send the call to.
	 * @param  array  $data     Array containing the params to the call.
	 * @param  string $method   HTTP Method: POST, GET, PUT, etc.
	 * @return object
	 */
	public function send_server_pilot_api_request($endpoint, $data = [], $method = 'POST') {

		$post_fields = [
			'timeout'  => 45,
			'blocking' => true,
			'method'   => $method,
			'body'     => $data ? wp_json_encode($data) : [],
			'headers'  => [
				'Authorization' => 'Basic ' . base64_encode(WU_SERVER_PILOT_CLIENT_ID . ':' . WU_SERVER_PILOT_API_KEY), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Content-Type'  => 'application/json',
			],
		];

		$response = wp_remote_request('https://api.serverpilot.io/v1/apps/' . WU_SERVER_PILOT_APP_ID . $endpoint, $post_fields);

		if ( ! is_wp_error($response)) {
			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (json_last_error() === JSON_ERROR_NONE) {
				return $body;
			}
		}

		return $response;
	}

	/**
	 * Makes sure ServerPilot autoSSL is always on, when possible.
	 *
	 * @since 1.7.4
	 * @return object
	 */
	public function turn_server_pilot_auto_ssl_on() {

		return $this->send_server_pilot_api_request(
			'/ssl',
			[
				'auto' => true,
			]
		);
	}

	/**
	 * Get the current list of domains added on Server Pilot.
	 *
	 * @since 1.7.4
	 * @return mixed
	 */
	public function get_server_pilot_domains() {

		$app_info = $this->send_server_pilot_api_request('', [], 'GET');

		if (isset($app_info['data']['domains'])) {
			return $app_info['data']['domains'];
		}

		/*
		 * Log response so we can see what went wrong
		 */

		// translators: %s is the wp_json_encode of the error.
		wu_log_add('integration-serverpilot', sprintf(__('An error occurred while trying to get the current list of domains: %s', 'multisite-ultimate'), wp_json_encode($app_info)), LogLevel::ERROR);

		return false;
	}

	/**
	 * Tests the connection with the ServerPilot API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$response = $this->send_server_pilot_api_request('', [], 'GET');

		if (is_wp_error($response) || wu_get_isset($response, 'error')) {
			wp_send_json_error($response);
		}

		wp_send_json_success($response);
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.1.2
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/serverpilot-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('ServerPilot is a cloud service for hosting WordPress and other PHP websites on servers at DigitalOcean, Amazon, Google, or any other server provider. You can think of ServerPilot as a modern, centralized hosting control panel.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.1.2
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('serverpilot.svg', 'img/hosts');
	}
}
