<?php
/**
 * Adds domain mapping and auto SSL support to customer using BunnyNet CDN.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/BunnyNet_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;
use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * BunnyNet integration for DNS management and CDN support.
 */
class BunnyNet_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the ID of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'bunnynet';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'BunnyNet';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/BunnyNet-Integration';

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
		'WU_BUNNYNET_API_KEY',
		'WU_BUNNYNET_ZONE_ID',
	];

	/**
	 * Add BunnyNet own DNS entries to the comparison table.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $dns_records List of current dns records.
	 * @param string $domain The domain name.
	 * @return array
	 */
	public function add_bunnynet_dns_entries($dns_records, $domain) {

		$zone_id = defined('WU_BUNNYNET_ZONE_ID') && WU_BUNNYNET_ZONE_ID ? WU_BUNNYNET_ZONE_ID : false;

		if (! $zone_id) {
			return $dns_records;
		}

		/**
		 * Get DNS records from BunnyNet for this zone.
		 */
		$dns_entries = $this->bunnynet_api_call(
			"dnszone/$zone_id",
			'GET'
		);

		if (is_wp_error($dns_entries)) {
			return $dns_records;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- BunnyNet API uses PascalCase
		if (! empty($dns_entries->Records)) {
			$bunnynet_tag = sprintf('<span class="wu-bg-orange-500 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" role="tooltip" aria-label="%s">%s</span>', __('BunnyNet', 'ultimate-multisite'), __('BunnyNet', 'ultimate-multisite'));

			foreach ($dns_entries->Records as $entry) {
				// Only show records matching the requested domain
				if ($domain === $entry->Name || '@' === $entry->Name) {
					$dns_records[] = [
						'ttl'  => $entry->Ttl ?? 3600,
						'data' => $entry->Value ?? '',
						'type' => $entry->Type ?? 'A',
						'host' => '@' === $entry->Name ? $domain : $entry->Name,
						'tag'  => $bunnynet_tag,
					];
				}
			}
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return $dns_records;
	}

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function detect(): bool {
		// No automatic detection for BunnyNet
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
			'WU_BUNNYNET_ZONE_ID' => [
				'title'       => __('DNS Zone ID', 'ultimate-multisite'),
				'placeholder' => __('e.g. 12345', 'ultimate-multisite'),
			],
			'WU_BUNNYNET_API_KEY' => [
				'title'       => __('API Key', 'ultimate-multisite'),
				'placeholder' => __('e.g. your-bunnynet-api-key', 'ultimate-multisite'),
			],
		];
	}

	/**
	 * Tests the connection with the BunnyNet API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$zone_id = defined('WU_BUNNYNET_ZONE_ID') && WU_BUNNYNET_ZONE_ID ? WU_BUNNYNET_ZONE_ID : '';

		if (! $zone_id) {
			wp_send_json_error(new \WP_Error('bunnynet-error', __('Zone ID is required.', 'ultimate-multisite')));
		}

		$results = $this->bunnynet_api_call("dnszone/$zone_id");

		if (is_wp_error($results)) {
			wp_send_json_error($results);
		}

		wp_send_json_success($results);
	}

	/**
	 * Lets integrations add additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function additional_hooks(): void {

		add_filter('wu_domain_dns_get_record', [$this, 'add_bunnynet_dns_entries'], 10, 2);
	}

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id) {}

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_domain($domain, $site_id) {}

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

		global $current_site;

		$zone_id = defined('WU_BUNNYNET_ZONE_ID') && WU_BUNNYNET_ZONE_ID ? WU_BUNNYNET_ZONE_ID : '';

		if (! $zone_id) {
			return;
		}

		if (! str_contains($subdomain, (string) $current_site->domain)) {
			return; // Not a sub-domain of the main domain.
		}

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if (! $subdomain) {
			return;
		}

		// Build FQDN so Domain_Manager can classify main vs. subdomain correctly.
		$full_domain    = $subdomain . '.' . $current_site->domain;
		$should_add_www = apply_filters(
			'wu_bunnynet_should_add_www',
			\WP_Ultimo\Managers\Domain_Manager::get_instance()->should_create_www_subdomain($full_domain),
			$subdomain,
			$site_id
		);

		$domains_to_send = [$subdomain];

		/**
		 * Adds the www version, if necessary.
		 */
		if (! str_starts_with($subdomain, 'www.') && $should_add_www) {
			$domains_to_send[] = 'www.' . $subdomain;
		}

		foreach ($domains_to_send as $subdomain) {
			$server_addr = isset($_SERVER['SERVER_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_ADDR'])) : '';

			$data = apply_filters(
				'wu_bunnynet_on_add_domain_data',
				[
					'Type'  => 0, // A record type
					'Ttl'   => 3600,
					'Name'  => $subdomain,
					'Value' => $server_addr,
				],
				$subdomain,
				$site_id
			);

			$results = $this->bunnynet_api_call("dnszone/$zone_id/records", 'PUT', $data);

			if (is_wp_error($results)) {
				wu_log_add('integration-bunnynet', sprintf('Failed to add subdomain "%s" to BunnyNet. Reason: %s', $subdomain, $results->get_error_message()), LogLevel::ERROR);

				return;
			}

			wu_log_add('integration-bunnynet', sprintf('Added sub-domain "%s" to BunnyNet.', $subdomain));
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
	public function on_remove_subdomain($subdomain, $site_id): void {

		global $current_site;

		$zone_id = defined('WU_BUNNYNET_ZONE_ID') && WU_BUNNYNET_ZONE_ID ? WU_BUNNYNET_ZONE_ID : '';

		if (! $zone_id) {
			return;
		}

		if (! str_contains($subdomain, (string) $current_site->domain)) {
			return; // Not a sub-domain of the main domain.
		}

		$original_subdomain = $subdomain;

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if (! $subdomain) {
			return;
		}

		/**
		 * Created the list that we should remove.
		 */
		$domains_to_remove = [
			$subdomain,
			'www.' . $subdomain,
		];

		// Get all DNS records for this zone
		$zone_data = $this->bunnynet_api_call("dnszone/$zone_id", 'GET');

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- BunnyNet API uses PascalCase
		if (is_wp_error($zone_data) || empty($zone_data->Records)) {
			return;
		}

		foreach ($domains_to_remove as $domain_to_remove) {
			// Find the record ID for this subdomain
			foreach ($zone_data->Records as $record) {
				if ($domain_to_remove === $record->Name) {
					$record_id = $record->Id;
					// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

					$results = $this->bunnynet_api_call("dnszone/$zone_id/records/$record_id", 'DELETE');

					if (is_wp_error($results)) {
						wu_log_add('integration-bunnynet', sprintf('Failed to remove subdomain "%s" from BunnyNet. Reason: %s', $domain_to_remove, $results->get_error_message()), LogLevel::ERROR);

						continue;
					}

					wu_log_add('integration-bunnynet', sprintf('Removed sub-domain "%s" from BunnyNet.', $domain_to_remove));
				}
			}
		}
	}

	/**
	 * Sends an API call to BunnyNet.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param string $method The HTTP verb. Defaults to GET.
	 * @param array  $data The data to send.
	 * @return object|\WP_Error
	 */
	protected function bunnynet_api_call($endpoint = 'dnszone', $method = 'GET', $data = []): object {

		$api_url = 'https://api.bunny.net/';

		$endpoint_url = $api_url . $endpoint;

		$args = [
			'method'  => $method,
			'headers' => [
				'AccessKey'    => defined('WU_BUNNYNET_API_KEY') ? WU_BUNNYNET_API_KEY : '',
				'Content-Type' => 'application/json',
			],
		];

		if ('GET' !== $method && ! empty($data)) {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($endpoint_url, $args);

		if (! is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			$code = wp_remote_retrieve_response_code($response);

			if ($code >= 200 && $code < 300) {
				return json_decode($body);
			} else {
				$error_message = wp_remote_retrieve_response_message($response);

				$response = new \WP_Error('bunnynet-error', sprintf('%s: %s', $error_message, $body));
			}
		}

		return $response;
	}

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions(): void {

		wu_get_template('wizards/host-integrations/bunnynet-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('BunnyNet is a global content delivery network (CDN) and edge storage platform that provides DNS management, CDN acceleration, and DDoS protection for your websites and applications.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('bunnynet.svg', 'img/hosts');
	}

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_explainer_lines() {

		$explainer_lines = [
			'will'     => [],
			'will_not' => [],
		];

		if (is_subdomain_install()) {
			$explainer_lines['will']['send_sub_domains'] = __('Add a new DNS record to the configured BunnyNet DNS zone whenever a new site gets created', 'ultimate-multisite');
		} else {
			$explainer_lines['will']['subdirectory'] = __('Do nothing! The BunnyNet integration has no effect in subdirectory multisite installs such as this one', 'ultimate-multisite');
		}

		$explainer_lines['will_not']['send_domain'] = __('Add domain mappings as new BunnyNet DNS zones', 'ultimate-multisite');

		return $explainer_lines;
	}
}
