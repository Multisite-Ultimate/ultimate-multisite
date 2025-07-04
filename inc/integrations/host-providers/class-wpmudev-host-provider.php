<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on WPMU DEV.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/WPMUDEV_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;
use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class WPMUDEV_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'wpmudev';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'WPMU DEV Hosting';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/WPMU-DEV-Integration';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = [
		'autossl',
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
		'WPMUDEV_HOSTING_SITE_ID',
	];

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.1.1
	 * @return void
	 */
	public function init(): void {

		parent::init();

		/**
		 * Add filter to increase the number of tries to get the SSL certificate.
		 * This is needed because, from our tests, WPMU DEV hosting takes a while to get the SSL certificate.
		 */
		add_filter('wu_async_process_domain_stage_max_tries', [$this, 'ssl_tries'], 10, 2);
	}

	/**
	 * Increases the number of tries to get the SSL certificate.
	 *
	 * @since 2.1.1
	 * @param int                      $max_tries The number of tries to get the SSL certificate.
	 * @param \WP_Ultimo\Models\Domain $domain The domain object.
	 * @return int
	 */
	public function ssl_tries($max_tries, $domain) {

		if ( ! $this->is_enabled()) {
			return $max_tries;
		}

		if ('checking-ssl-cert' === $domain->get_stage()) {
			$max_tries = 10;
		}

		return $max_tries;
	}

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function detect() {

		return defined('WPMUDEV_HOSTING_SITE_ID') && WPMUDEV_HOSTING_SITE_ID;
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

		$site_id = WPMUDEV_HOSTING_SITE_ID;

		$api_key = get_site_option('wpmudev_apikey');

		$domains = [$domain];

		if (! str_starts_with($domain, 'www.')) {
			$domains[] = "www.$domain";
		}

		foreach ($domains as $_domain) {
			$response = wp_remote_post(
				"https://premium.wpmudev.org/api/hosting/v1/$site_id/domains",
				[
					'timeout' => 50,
					'body'    => [
						'domain'  => $_domain,
						'site_id' => $site_id,
					],
					'headers' => [
						'Authorization' => $api_key,
					],
				]
			);

			if (is_wp_error($response)) {

				// translators: The %s placeholder will be replaced with the domain name.
				wu_log_add('integration-wpmudev', sprintf(__('An error occurred while trying to add the custom domain %s to WPMU Dev hosting.', 'multisite-ultimate'), $_domain), LogLevel::ERROR);
			}

			$body = json_decode(wp_remote_retrieve_body($response));

			if ($body->message) {

				// translators: The %1$s will be replaced with the domain name and %2$s is the error message.
				wu_log_add('integration-wpmudev', sprintf(__('An error occurred while trying to add the custom domain %1$s to WPMU Dev hosting: %2$s', 'multisite-ultimate'), $_domain, $body->message->message), LogLevel::ERROR);
			} else {

				// translators: The %s placeholder will be replaced with the domain name.
				wu_log_add('integration-wpmudev', sprintf(__('Domain %s added to WPMU Dev hosting successfully.', 'multisite-ultimate'), $_domain));
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

		/**
		 * The WPMU DEV Hosting REST API does not offer an endpoint to remove domains yet.
		 * As soon as that's the case, we'll implement it here.
		 *
		 * @todo Implement support to removing domains when a mapping is removed.
		 */
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
	 * Tests the connection with the WPMUDEV API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$site_id = WPMUDEV_HOSTING_SITE_ID;

		$api_key = get_site_option('wpmudev_apikey');

		$response = wp_remote_get(
			"https://premium.wpmudev.org/api/hosting/v1/{$site_id}/domains",
			[
				'timeout' => 50,
				'headers' => [
					'Authorization' => $api_key,
				],
			]
		);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			wp_send_json_error($response);
		} else {
			wp_send_json_success(wp_remote_retrieve_body($response));
		}
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('WPMU DEV is one of the largest companies in the WordPress space. Founded in 2004, it was one of the first companies to scale the Website as a Service model with products such as Edublogs and CampusPress.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('wpmudev.webp', 'img/hosts');
	}
}
