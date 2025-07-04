<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on WPMU DEV.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Gridpane_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Gridpane_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'gridpane';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Gridpane';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/GridPane-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
		'autossl',
		'no-config',
	];

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = [
		'WU_GRIDPANE',
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

		return defined('GRIDPANE') && GRIDPANE;
	}

	/**
	 * Enables this integration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enable(): void {
		/*
		 * Prevent issues with Gridpane
		 */
		$success = \WP_Ultimo\Helpers\WP_Config::get_instance()->revert('SUNRISE');

		parent::enable();
	}

	/**
	 * Sends a request to the GridPane API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The endpoint to hit.
	 * @param array  $data The post body to send to the API.
	 * @param string $method The HTTP method.
	 * @return mixed
	 */
	public function send_gridpane_api_request($endpoint, $data = [], $method = 'POST') {

		$post_fields = [
			'timeout'  => 45,
			'blocking' => true,
			'method'   => $method,
			'body'     => array_merge(
				[
					'api_token' => WU_GRIDPANE_API_KEY,
				],
				$data
			),
		];

		$response = wp_remote_request("https://my.gridpane.com/api/{$endpoint}", $post_fields);

		if ( ! is_wp_error($response)) {
			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (json_last_error() === JSON_ERROR_NONE) {
				return $body;
			}
		}

		return $response;
	}

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return object\WP_Error
	 */
	public function on_add_domain($domain, $site_id) {

		return $this->send_gridpane_api_request(
			'application/add-domain',
			[
				'server_ip'  => WU_GRIDPANE_SERVER_ID,
				'site_url'   => WU_GRIDPANE_APP_ID,
				'domain_url' => $domain,
			]
		);
	}

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return object\WP_Error
	 */
	public function on_remove_domain($domain, $site_id) {

		return $this->send_gridpane_api_request(
			'application/delete-domain',
			[
				'server_ip'  => WU_GRIDPANE_SERVER_ID,
				'site_url'   => WU_GRIDPANE_APP_ID,
				'domain_url' => $domain,
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
	 * Tests the connection with the Gridpane API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$results = $this->on_remove_domain('test.com', false);

		if (wu_get_isset($results, 'message') === 'This action is unauthorized.') {
			wp_send_json_error(
				[
					'error' => __('We were not able to successfully establish a connection.', 'multisite-ultimate'),
				]
			);
		}

		if (is_wp_error($results)) {
			wp_send_json_error(
				[
					'error' => __('We were not able to successfully establish a connection.', 'multisite-ultimate'),
				]
			);
		}

		wp_send_json_success(
			[
				'success' => __('Connection successfully established.', 'multisite-ultimate'),
			]
		);
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/gridpane-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __("GridPane is the world's first hosting control panel built exclusively for serious WordPress professionals.", 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('gridpane.webp', 'img/hosts');
	}
}
