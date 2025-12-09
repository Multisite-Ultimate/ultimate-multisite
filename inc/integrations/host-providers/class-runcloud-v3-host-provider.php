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
class Runcloud_V3_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'runcloudv3';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'RunCloud V3';

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
	protected $supports = array(
		'autossl',
	);

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = array(
		'WU_RUNCLOUDV3_API_TOKEN',
		'WU_RUNCLOUDV3_SERVER_ID',
		'WU_RUNCLOUDV3_APP_ID',
	);

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {
		return strpos(ABSPATH, 'runcloud') !== false;
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function get_fields() {
		return array(
			'WU_RUNCLOUDV3_API_TOKEN' => array(
				'title'       => __('RunCloud API v3 Token', 'multisite-ultimate'),
				'desc'        => __('The API Token generated in RunCloud.', 'multisite-ultimate'),
				'placeholder' => __('e.g. your-api-token-here', 'multisite-ultimate'),
			),
			'WU_RUNCLOUDV3_SERVER_ID' => array(
				'title'       => __('RunCloud Server ID', 'multisite-ultimate'),
				'desc'        => __('The Server ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 11667', 'multisite-ultimate'),
			),
			'WU_RUNCLOUDV3_APP_ID'    => array(
				'title'       => __('RunCloud App ID', 'multisite-ultimate'),
				'desc'        => __('The App ID retrieved in the previous step.', 'multisite-ultimate'),
				'placeholder' => __('e.g. 940288', 'multisite-ultimate'),
			),
		);
	}

	/**
	 * Handles domain mapping when a new domain is added.
	 *
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 *
	 * @since 2.0.0
	 */
	public function on_add_domain($domain, $site_id) {
		$success = false;

		$response = $this->send_runcloud_request(
			$this->get_runcloud_base_url('domains'),
			array(
				'name'        => $domain,
				'www'         => true,
				'redirection' => 'non-www',
				'type'        => 'alias',
			),
			'POST'
		);

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', 'Add Domain Error: ' . $response->get_error_message(), LogLevel::ERROR);
		} else {
			$success = true;
			wu_log_add('integration-runcloud', 'Domain Added: ' . wp_remote_retrieve_body($response));
		}

		if ($success) {
			$ssl_id = $this->get_runcloud_ssl_id();
			if ($ssl_id) {
				$this->redeploy_runcloud_ssl($ssl_id);
			}
		}
	}

	/**
	 * Handles domain removal.
	 *
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 *
	 * @since 2.0.0
	 */
	public function on_remove_domain($domain, $site_id) {
		$domain_id = $this->get_runcloud_domain_id($domain);

		if ( ! $domain_id) {
			wu_log_add('integration-runcloud', __('Domain not found: ', 'multisite-ultimate') . $domain);

			return;
		}

		$response = $this->send_runcloud_request(
			$this->get_runcloud_base_url("domains/$domain_id"),
			array(),
			'DELETE'
		);

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', 'Remove Domain Error: ' . $response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-runcloud', 'Domain Removed: ' . wp_remote_retrieve_body($response));
		}
	}

	/**
	 * Handles subdomain additions (not used but required by interface).
	 */
	public function on_add_subdomain($subdomain, $site_id) {
	}

	/**
	 * Handles subdomain removals (not used but required by interface).
	 */
	public function on_remove_subdomain($subdomain, $site_id) {
	}

	/**
	 * Tests API connection.
	 */
	public function test_connection(): void {
		$response = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), array(), 'GET');

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			wp_send_json_error($response);
		} else {
			wp_send_json_success($this->maybe_return_runcloud_body($response));
		}
	}

	/**
	 * Constructs the base API URL.
	 *
	 * @param string $path path of endpoint.
	 */
	public function get_runcloud_base_url($path = '') {
		$serverid = defined('WU_RUNCLOUD_SERVER_ID') ? WU_RUNCLOUD_SERVER_ID : '';
		$appid    = defined('WU_RUNCLOUD_APP_ID') ? WU_RUNCLOUD_APP_ID : '';

		return "https://manage.runcloud.io/api/v3/servers/{$serverid}/webapps/{$appid}/{$path}";
	}

	/**
	 * Sends authenticated requests to RunCloud API.
	 */
	public function send_runcloud_request($url, $data = array(), $method = 'POST') {
		$token = defined('WU_RUNCLOUD_API_TOKEN') ? WU_RUNCLOUD_API_TOKEN : '';

		$args = array(
			'timeout'     => 100,
			'redirection' => 5,
			'method'      => $method,
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
			),
		);

		if ('GET' === $method) {
			$url = add_query_arg($data, $url);
		} else {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		// Enhanced logging
		$log_message = sprintf(
			"Request: %s %s\nStatus: %s\nResponse: %s",
			$method,
			$url,
			wp_remote_retrieve_response_code($response),
			wp_remote_retrieve_body($response)
		);
		wu_log_add('integration-runcloud', $log_message);

		return $response;
	}

	/**
	 * Processes API responses.
	 */
	public function maybe_return_runcloud_body($response) {
		if (is_wp_error($response)) {
			return $response->get_error_message();
		}

		$body = json_decode(wp_remote_retrieve_body($response));

		if (json_last_error() !== JSON_ERROR_NONE) {
			return 'Invalid JSON response: ' . json_last_error_msg();
		}

		return $body;
	}

	/**
	 * Finds domain ID in RunCloud.
	 */
	public function get_runcloud_domain_id($domain) {
		$response = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), array(), 'GET');
		$data     = $this->maybe_return_runcloud_body($response);

		if (is_object($data) && isset($data->data) && is_array($data->data)) {
			foreach ($data->data as $item) {
				if (isset($item->name) && $item->name === $domain) {
					return $item->id;
				}
			}
		}

		wu_log_add('integration-runcloud', "Domain $domain not found in response");

		return false;
	}

	/**
	 * Retrieves SSL certificate ID.
	 */
	public function get_runcloud_ssl_id() {
		$response = $this->send_runcloud_request($this->get_runcloud_base_url('settings/ssl'), array(), 'GET');
		$data     = $this->maybe_return_runcloud_body($response);

		if (is_object($data) && isset($data->sslCertificate) && isset($data->sslCertificate->id)) {
			return $data->sslCertificate->id;
		}

		wu_log_add('integration-runcloud', 'SSL Certificate not found');

		return false;
	}

	/**
	 * Redeploys SSL certificate.
	 */
	public function redeploy_runcloud_ssl($ssl_id) {
		$response = $this->send_runcloud_request(
			$this->get_runcloud_base_url("settings/ssl/$ssl_id/redeploy"),
			array(),
			'POST'
		);

		if (is_wp_error($response)) {
			wu_log_add('integration-runcloud', 'SSL Redeploy Error: ' . $response->get_error_message(), LogLevel::ERROR);
		} else {
			wu_log_add('integration-runcloud', 'SSL Redeploy Successful: ' . wp_remote_retrieve_body($response));
		}
	}

	/**
	 * Renders instructions.
	 */
	public function get_instructions() {
		wu_get_template('wizards/host-integrations/runcloud-instructions-v3');
	}

	/**
	 * Returns description.
	 */
	public function get_description() {
		return __('With RunCloud, you don’t need to be a Linux expert to build a website powered by DigitalOcean, AWS, or Google Cloud. Use our graphical interface and build a business on the cloud affordably.', 'multisite-ultimate');
	}

	/**
	 * Returns logo URL.
	 */
	public function get_logo() {
		return wu_get_asset('runcloud.svg', 'img/hosts');
	}
}
