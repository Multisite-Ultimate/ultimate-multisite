<?php
/**
 * External Cron Registration Handler
 *
 * Handles registration and unregistration with the External Cron Service.
 *
 * @package WP_Ultimo
 * @subpackage External_Cron
 * @since 2.3.0
 */

namespace WP_Ultimo\External_Cron;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * External Cron Registration class.
 *
 * @since 2.3.0
 */
class External_Cron_Registration {

	/**
	 * Service client instance.
	 *
	 * @var External_Cron_Service_Client
	 */
	private External_Cron_Service_Client $client;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 * @param External_Cron_Service_Client $client Service client.
	 */
	public function __construct(External_Cron_Service_Client $client) {

		$this->client = $client;
	}

	/**
	 * Register the network with the service.
	 *
	 * @since 2.3.0
	 * @return array|\WP_Error
	 */
	public function register_network() {

		$granularity = wu_get_setting('external_cron_granularity', 'network');

		$data = [
			'site_url'                => network_site_url(),
			'site_hash'               => $this->client->get_site_hash(),
			'is_network_registration' => true,
			'granularity'             => $granularity,
			'timezone'                => wp_timezone_string(),
			'cron_url'                => site_url('wp-cron.php'),
		];

		$result = $this->client->register_site($data);

		if (is_wp_error($result)) {
			return $result;
		}

		// Save credentials.
		wu_save_setting('external_cron_site_id', $result['site_id']);
		wu_save_setting('external_cron_api_key', $result['api_key']);
		wu_save_setting('external_cron_api_secret', $result['api_secret']);
		wu_save_setting('external_cron_enabled', true);

		// Update client with new credentials.
		$this->client->set_credentials($result['api_key'], $result['api_secret']);

		/**
		 * Fires after network is registered with external cron service.
		 *
		 * @since 2.3.0
		 * @param array $result Registration result.
		 */
		do_action('wu_external_cron_network_registered', $result);

		return $result;
	}

	/**
	 * Register a specific subsite with the service.
	 *
	 * @since 2.3.0
	 * @param int $blog_id Blog ID.
	 * @return array|\WP_Error
	 */
	public function register_subsite(int $blog_id) {

		switch_to_blog($blog_id);

		$data = [
			'site_url'                => site_url(),
			'site_hash'               => $this->generate_site_hash($blog_id),
			'is_network_registration' => false,
			'network_id'              => wu_get_setting('external_cron_site_id'),
			'timezone'                => wp_timezone_string(),
			'cron_url'                => site_url('wp-cron.php'),
		];

		restore_current_blog();

		$result = $this->client->register_site($data);

		if (is_wp_error($result)) {
			return $result;
		}

		// Save site ID for this blog.
		update_blog_option($blog_id, 'external_cron_site_id', $result['site_id']);

		/**
		 * Fires after subsite is registered with external cron service.
		 *
		 * @since 2.3.0
		 * @param int   $blog_id Blog ID.
		 * @param array $result  Registration result.
		 */
		do_action('wu_external_cron_subsite_registered', $blog_id, $result);

		return $result;
	}

	/**
	 * Register all subsites in the network.
	 *
	 * @since 2.3.0
	 * @return array Results for each site.
	 */
	public function register_all_subsites(): array {

		$sites   = get_sites(
			[
				'number'   => 0,
				'fields'   => 'ids',
				'archived' => 0,
				'deleted'  => 0,
			]
		);
		$results = [];

		foreach ($sites as $blog_id) {
			// Skip if already registered.
			$existing = get_blog_option($blog_id, 'external_cron_site_id');
			if (! empty($existing)) {
				$results[ $blog_id ] = ['skipped' => true];
				continue;
			}

			$results[ $blog_id ] = $this->register_subsite($blog_id);
		}

		return $results;
	}

	/**
	 * Unregister from the service.
	 *
	 * @since 2.3.0
	 * @return array|\WP_Error
	 */
	public function unregister() {

		$result = $this->client->unregister_site();

		if (is_wp_error($result)) {
			return $result;
		}

		// Clear settings.
		wu_save_setting('external_cron_site_id', null);
		wu_save_setting('external_cron_api_key', null);
		wu_save_setting('external_cron_api_secret', null);
		wu_save_setting('external_cron_enabled', false);

		// Unschedule actions.
		wu_unschedule_all_actions('wu_external_cron_sync_schedules');
		wu_unschedule_all_actions('wu_external_cron_heartbeat');

		/**
		 * Fires after network is unregistered from external cron service.
		 *
		 * @since 2.3.0
		 */
		do_action('wu_external_cron_network_unregistered');

		return $result;
	}

	/**
	 * Generate a unique site hash for a specific blog.
	 *
	 * @since 2.3.0
	 * @param int|null $blog_id Blog ID.
	 * @return string
	 */
	private function generate_site_hash(?int $blog_id = null): string {

		if ($blog_id) {
			$url = get_blog_option($blog_id, 'siteurl');
		} else {
			$url = network_site_url();
		}

		return hash('sha256', $url . AUTH_KEY);
	}
}
