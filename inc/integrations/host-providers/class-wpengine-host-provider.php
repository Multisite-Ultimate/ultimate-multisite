<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on WPEngine.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/WPEngine
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class WPEngine_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'wpengine';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'WP Engine';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/WP-Engine-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
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
		['WPE_API', 'WPE_APIKEY'],
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

		return (defined('WPE_APIKEY') && WPE_APIKEY) || (defined('WPE_API') && WPE_API);
	}

	/**
	 * Can be used to load dependencies.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_dependencies(): void {

		// if WP Engine is not defined, then return
		if ( ! defined('WPE_PLUGIN_DIR') || ! is_readable(WPE_PLUGIN_DIR . '/class-wpeapi.php')) {
			return;
		}

		include_once WPE_PLUGIN_DIR . '/class-wpeapi.php';
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

		$api = new \WPE_API();

		$api->set_arg('method', 'domain');

		$api->set_arg('domain', $domain);

		$api->get();
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

		$api = new \WPE_API();

		$api->set_arg('method', 'domain-remove');

		$api->set_arg('domain', $domain);

		$api->get();
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

		$this->on_remove_domain($subdomain, $site_id);
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		$description = __('WP Engine drives your business forward faster with the first and only WordPress Digital Experience Platform. We offer the best WordPress hosting and developer experience on a proven, reliable architecture that delivers unparalleled speed, scalability, and security for your sites.', 'multisite-ultimate');

		$description .= '<br><br><b>' . __('We recommend to enter in contact with WP Engine support to ask for a Wildcard domain if you are using a subdomain install.', 'multisite-ultimate') . '</b>';

		return $description;
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('wpengine.svg', 'img/hosts');
	}

	/**
	 * Tests the connection with the WP Engine API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$api = new \WPE_API();

		$api->set_arg('method', 'site');

		$results = $api->get();

		if (is_wp_error($results)) {
			wp_send_json_error($results->get_error_message());
		} else {
			wp_send_json_success($results);
		}
	}
}
