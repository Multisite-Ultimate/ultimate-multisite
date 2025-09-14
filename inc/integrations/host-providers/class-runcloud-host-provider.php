<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on RunCloud.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Runcloud_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Runcloud_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'runcloud';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'RunCloud';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Runcloud-Integration';

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
		'WU_RUNCLOUD_API_KEY',
		'WU_RUNCLOUD_API_SECRET',
		'WU_RUNCLOUD_SERVER_ID',
		'WU_RUNCLOUD_APP_ID',
	];

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {

		return str_contains(ABSPATH, 'runcloud');
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_RUNCLOUD_API_KEY'    => [
				'title'       => __('RunCloud API Key', 'multisite-ultimate'),
				'desc'        => __('The API Key retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. Sx9tHAn5XMrkeyZKS1a7uj8dGTLgKnlEOaJEFRt1m95L', 'multisite-ultimate'),
			],
			'WU_RUNCLOUD_API_SECRET' => [
				'title'       => __('RunCloud API Secret', 'multisite-ultimate'),
				'desc'        => __('The API secret retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. ZlAebXp2sa6J5xsrPoiPcMXZRIVsHJ2rEkNCNGknZnF0UK5cSNSePS8GBW9FXIQd', 'multisite-ultimate'),
			],
			'WU_RUNCLOUD_SERVER_ID'  => [
				'title'       => __('RunCloud Server ID', 'multisite-ultimate'),
				'desc'        => __('The Server ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 11667', 'multisite-ultimate'),
			],
			'WU_RUNCLOUD_APP_ID'     => [
				'title'       => __('RunCloud App ID', 'multisite-ultimate'),
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

		$success = false;

		$create_www = \WP_Ultimo\Managers\Domain_Manager::get_instance()->should_create_www_subdomain($domain);

		$response = $this->send_runcloud_request(
			$this->get_runcloud_base_url('domains'),
			[
				'name'        => $domain,
				'www'         => $create_www,
				'redirection' => $create_www ? 'non-www' : 'none',
			],
			'POST'
		);

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', $response->get_error_message(), LogLevel::ERROR);
		} else {
			$success = true; // At least one of the calls was successful;

			wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));
		}

		/**
		 * Only redeploy SSL if at least one of the domains were successfully added
		 */
		if ($success) {
			$ssl_id = $this->get_runcloud_ssl_id();

			if ($ssl_id) {
				$this->redeploy_runcloud_ssl($ssl_id);
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

		$domain_id = $this->get_runcloud_domain_id($domain);

		if ( ! $domain_id) {
			wu_log_add('integration-runcloud', __('Domain name not found on runcloud', 'multisite-ultimate'));
		}

		$response = $this->send_runcloud_request($this->get_runcloud_base_url("domains/$domain_id"), [], 'DELETE');

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', $response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));
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
	 * Tests the connection with the RunCloud API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$response = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), [], 'GET');

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			wp_send_json_error($response);
		} else {
			wp_send_json_success($this->maybe_return_runcloud_body($response));
		}
	}

	/**
	 * Returns the base domain API url to our calls.
	 *
	 * @since 1.7.0
	 * @param string $path Path relative to the main endpoint.
	 * @return string
	 */
	public function get_runcloud_base_url($path = '') {

		$serverid = defined('WU_RUNCLOUD_SERVER_ID') ? WU_RUNCLOUD_SERVER_ID : '';

		$appid = defined('WU_RUNCLOUD_APP_ID') ? WU_RUNCLOUD_APP_ID : '';

		return "https://manage.runcloud.io/api/v2/servers/{$serverid}/webapps/{$appid}/{$path}";
	}

	/**
	 * Sends the request to a given runcloud URL with a given body.
	 *
	 * @since 1.7.0
	 * @param string $url Endpoinbt to send the request to.
	 * @param array  $data Data to be sent.
	 * @param string $method HTTP Method to send. Defaults to POST.
	 * @return array
	 */
	public function send_runcloud_request($url, $data = [], $method = 'POST') {

		$username = defined('WU_RUNCLOUD_API_KEY') ? WU_RUNCLOUD_API_KEY : '';

		$password = defined('WU_RUNCLOUD_API_SECRET') ? WU_RUNCLOUD_API_SECRET : '';

		$response = wp_remote_request(
			$url,
			[
				'timeout'     => 100,
				'redirection' => 5,
				'body'        => $data,
				'method'      => $method,
				'headers'     => [
					'Authorization' => 'Basic ' . base64_encode($username . ':' . $password), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				],
			]
		);

		return $response;
	}

	/**
	 * Treats the response, maybe returning the json decoded version
	 *
	 * @since 1.7.0
	 * @param array $response The response.
	 * @return mixed
	 */
	public function maybe_return_runcloud_body($response) {

		if (is_wp_error($response)) {
			return $response->get_error_message();
		} else {
			return json_decode(wp_remote_retrieve_body($response));
		}
	}

	/**
	 * Returns the RunCloud.io domain id to remove.
	 *
	 * @since 1.7.0
	 * @param string $domain The domain name being removed.
	 * @return string
	 */
	public function get_runcloud_domain_id($domain) {

		$domains_list = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), [], 'GET');

		$list = $this->maybe_return_runcloud_body($domains_list);

		if (is_object($list) && ! empty($list->data)) {
			foreach ($list->data as $remote_domain) {
				if ($remote_domain->name === $domain) {
					return $remote_domain->id;
				}
			}
		}

		return false;
	}

	/**
	 * Checks if RunCloud has a SSL cert installed or not, and returns the ID.
	 *
	 * @since 1.10.4
	 * @return bool|int
	 */
	public function get_runcloud_ssl_id() {

		$ssl_id = false;

		$response = $this->send_runcloud_request($this->get_runcloud_base_url('ssl'), [], 'GET');

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', $response->get_error_message(), LogLevel::ERROR);
		} else {
			$data = $this->maybe_return_runcloud_body($response);

			wu_log_add('integration-runcloud', wp_json_encode($data));

			if (property_exists($data, 'id')) {
				$ssl_id = $data->id;
			}
		}

		return $ssl_id;
	}

	/**
	 * Redeploys the SSL cert when a new domain is added.
	 *
	 * @since 1.10.4
	 * @param int $ssl_id The SSL id on RunCloud.
	 * @return void
	 */
	public function redeploy_runcloud_ssl($ssl_id): void {

		$response = $this->send_runcloud_request($this->get_runcloud_base_url("ssl/$ssl_id"), [], 'PUT');

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', $response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));
		}
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/runcloud-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('With RunCloud, you don’t need to be a Linux expert to build a website powered by DigitalOcean, AWS, or Google Cloud. Use our graphical interface and build a business on the cloud affordably.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('runcloud.svg', 'img/hosts');
	}
}
