<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on cPanel.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/CPanel_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;
use WP_Ultimo\Integrations\Host_Providers\CPanel_API\CPanel_API;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class CPanel_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cpanel';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'cPanel';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/cPanel-Integration';

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
		'WU_CPANEL_USERNAME',
		'WU_CPANEL_PASSWORD',
		'WU_CPANEL_HOST',
	];

	/**
	 * Constants that are optional on wp-config.php.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = [
		'WU_CPANEL_PORT',
		'WU_CPANEL_ROOT_DIR',
	];

	/**
	 * Holds the API object.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Integrations\Host_Providers\CPanel_API\CPanel_API
	 */
	protected $api = null;

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 * Unfortunately, we don't have a good method of detecting if someone is running from cPanel.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {

		return false;
	}

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_CPANEL_USERNAME' => [
				'title'       => __('cPanel Username', 'multisite-ultimate'),
				'placeholder' => __('e.g. username', 'multisite-ultimate'),
			],
			'WU_CPANEL_PASSWORD' => [
				'type'        => 'password',
				'title'       => __('cPanel Password', 'multisite-ultimate'),
				'placeholder' => __('password', 'multisite-ultimate'),
			],
			'WU_CPANEL_HOST'     => [
				'title'       => __('cPanel Host', 'multisite-ultimate'),
				'placeholder' => __('e.g. yourdomain.com', 'multisite-ultimate'),
			],
			'WU_CPANEL_PORT'     => [
				'title'       => __('cPanel Port', 'multisite-ultimate'),
				'placeholder' => __('Defaults to 2083', 'multisite-ultimate'),
				'value'       => 2083,
			],
			'WU_CPANEL_ROOT_DIR' => [
				'title'       => __('Root Directory', 'multisite-ultimate'),
				'placeholder' => __('Defaults to /public_html', 'multisite-ultimate'),
				'value'       => '/public_html',
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

		// Root Directory
		$root_dir = defined('WU_CPANEL_ROOT_DIR') && WU_CPANEL_ROOT_DIR ? WU_CPANEL_ROOT_DIR : '/public_html';

		// Send Request
		$results = $this->load_api()->api2(
			'AddonDomain',
			'addaddondomain',
			[
				'dir'       => $root_dir,
				'newdomain' => $domain,
				'subdomain' => $this->get_subdomain($domain),
			]
		);

		$this->log_calls($results);
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

		// Send Request
		$results = $this->load_api()->api2(
			'AddonDomain',
			'deladdondomain',
			[
				'domain'    => $domain,
				'subdomain' => $this->get_subdomain($domain) . '_' . $this->get_site_url(),
			]
		);

		$this->log_calls($results);
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

		// Root Directory
		$root_dir = defined('WU_CPANEL_ROOT_DIR') && WU_CPANEL_ROOT_DIR ? WU_CPANEL_ROOT_DIR : '/public_html';

		$subdomain = $this->get_subdomain($subdomain, false);

		$rootdomain = str_replace($subdomain . '.', '', $this->get_site_url($site_id));

		// Send Request
		$results = $this->load_api()->api2(
			'SubDomain',
			'addsubdomain',
			[
				'dir'        => $root_dir,
				'domain'     => $subdomain,
				'rootdomain' => $rootdomain,
			]
		);

		// Check the results
		$this->log_calls($results);
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
	 * Load the CPanel API.
	 *
	 * @since 2.0.0
	 * @return CPanel_API
	 */
	public function load_api() {

		if (null === $this->api) {
			$username = defined('WU_CPANEL_USERNAME') ? WU_CPANEL_USERNAME : '';
			$password = defined('WU_CPANEL_PASSWORD') ? WU_CPANEL_PASSWORD : '';
			$host     = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : '';
			$port     = defined('WU_CPANEL_PORT') && WU_CPANEL_PORT ? WU_CPANEL_PORT : 2083;

			/*
			 * Set up the API.
			 */
			$this->api = new CPanel_API($username, $password, preg_replace('#^https?://#', '', (string) $host), $port);
		}

		return $this->api;
	}

	/**
	 * Returns the Site URL.
	 *
	 * @since  1.6.2
	 * @param null|int $site_id The site id.
	 */
	public function get_site_url($site_id = null): string {

		return trim(preg_replace('#^https?://#', '', get_site_url($site_id)), '/');
	}

	/**
	 * Returns the sub-domain version of the domain.
	 *
	 * @since 1.6.2
	 * @param string $domain The domain to be used.
	 * @param string $mapped_domain If this is a mapped domain.
	 * @return string
	 */
	public function get_subdomain($domain, $mapped_domain = true) {

		if (false === $mapped_domain) {
			$domain_parts = explode('.', $domain);

			return array_shift($domain_parts);
		}

		$subdomain = str_replace(['.', '/'], '', $domain);

		return $subdomain;
	}

	/**
	 * Logs the results of the calls for debugging purposes
	 *
	 * @since 1.6.2
	 * @param object $results Results of the cPanel call.
	 * @return void
	 */
	public function log_calls($results) {

		if (is_object($results->cpanelresult->data)) {
			wu_log_add('integration-cpanel', $results->cpanelresult->data->reason);
			return;
		} elseif ( ! isset($results->cpanelresult->data[0])) {
			wu_log_add('integration-cpanel', __('Unexpected error ocurred trying to sync domains with CPanel', 'multisite-ultimate'), LogLevel::ERROR);
			return;
		}

		wu_log_add('integration-cpanel', $results->cpanelresult->data[0]->reason);
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('cPanel is the management panel being used on a large number of shared and dedicated hosts across the globe.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cpanel.svg', 'img/hosts');
	}

	/**
	 * Tests the connection with the Cloudflare API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$results = $this->load_api()->api2('Cron', 'fetchcron', []);

		$this->log_calls($results);

		if (isset($results->cpanelresult->data) && ! isset($results->cpanelresult->error)) {
			wp_send_json_success($results);

			exit;
		}

		wp_send_json_error($results);
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
				'send_domains' => __('Add a new Addon Domain on cPanel whenever a new domain mapping gets created on your network', 'multisite-ultimate'),
			],
			'will_not' => [],
		];

		if (is_subdomain_install()) {
			$explainer_lines['will']['send_sub_domains'] = __('Add a new SubDomain on cPanel whenever a new site gets created on your network', 'multisite-ultimate');
		}

		return $explainer_lines;
	}
}
