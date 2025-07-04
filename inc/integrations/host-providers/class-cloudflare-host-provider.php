<?php
/**
 * Adds domain mapping and auto SSL support to customer using Cloudflare.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Cloudflare_Host_Provider
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
class Cloudflare_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cloudflare';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Cloudflare';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Cloudflare-Integration';

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
		'WU_CLOUDFLARE_API_KEY',
		'WU_CLOUDFLARE_ZONE_ID',
	];

	/**
	 * Add Cloudflare own DNS entries to the comparison table.
	 *
	 * @since 2.0.4
	 *
	 * @param array  $dns_records List of current dns records.
	 * @param string $domain The domain name.
	 * @return array
	 */
	public function add_cloudflare_dns_entries($dns_records, $domain) {

		$zone_ids = [];

		$default_zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : false;

		if ($default_zone_id) {
			$zone_ids[] = $default_zone_id;
		}

		$cloudflare_zones = $this->cloudflare_api_call(
			'client/v4/zones',
			'GET',
			[
				'name'   => $domain,
				'status' => 'active',
			]
		);

		foreach ($cloudflare_zones->result as $zone) {
			$zone_ids[] = $zone->id;
		}

		foreach ($zone_ids as $zone_id) {

			/**
			 * First, try to detect the domain as a proxied on the current zone,
			 * if applicable
			 */
			$dns_entries = $this->cloudflare_api_call(
				"client/v4/zones/$zone_id/dns_records/",
				'GET',
				[
					'name'  => $domain,
					'match' => 'any',
					'type'  => 'A,AAAA,CNAME',
				]
			);

			if ( ! empty($dns_entries->result)) {
				$proxied_tag = sprintf('<span class="wu-bg-orange-500 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" %s>%s</span>', wu_tooltip_text(__('Proxied', 'multisite-ultimate')), __('Cloudflare', 'multisite-ultimate'));

				$not_proxied_tag = sprintf('<span class="wu-bg-gray-700 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" %s>%s</span>', wu_tooltip_text(__('Not Proxied', 'multisite-ultimate')), __('Cloudflare', 'multisite-ultimate'));

				foreach ($dns_entries->result as $entry) {
					$dns_records[] = [
						'ttl'  => $entry->ttl,
						'data' => $entry->content,
						'type' => $entry->type,
						'host' => $entry->name,
						'tag'  => $entry->proxied ? $proxied_tag : $not_proxied_tag,
					];
				}
			}
		}

		return $dns_records;
	}

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 * Unfortunately, we don't have a good method of detecting if someone is running from cPanel.
	 *
	 * @since 2.0.0
	 */
	public function detect(): bool {
		/**
		 * As Cloudflare recently enabled wildcards for all customers, this integration is no longer required.
		 * https://blog.cloudflare.com/wildcard-proxy-for-everyone/
		 *
		 * @since 2.1
		 */
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
			'WU_CLOUDFLARE_ZONE_ID' => [
				'title'       => __('Zone ID', 'multisite-ultimate'),
				'placeholder' => __('e.g. 644c7705723d62e31f700bb798219c75', 'multisite-ultimate'),
			],
			'WU_CLOUDFLARE_API_KEY' => [
				'title'       => __('API Key', 'multisite-ultimate'),
				'placeholder' => __('e.g. xKGbxxVDpdcUv9dUzRf4i4ngv0QNf1wCtbehiec_o', 'multisite-ultimate'),
			],
		];
	}

	/**
	 * Tests the connection with the Cloudflare API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection(): void {

		$results = $this->cloudflare_api_call('client/v4/user/tokens/verify');

		if (is_wp_error($results)) {
			wp_send_json_error($results);
		}

		wp_send_json_success($results);
	}

	/**
	 * Lets integrations add additional hooks.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function additional_hooks(): void {

		add_filter('wu_domain_dns_get_record', [$this, 'add_cloudflare_dns_entries'], 10, 2);
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

		$zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : '';

		if ( ! $zone_id) {
			return;
		}

		if (! str_contains($subdomain, (string) $current_site->domain)) {
			return; // Not a sub-domain of the main domain.

		}

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if ( ! $subdomain) {
			return;
		}

		$should_add_www = apply_filters('wu_cloudflare_should_add_www', true, $subdomain, $site_id);

		$domains_to_send = [$subdomain];

		/**
		 * Adds the www version, if necessary.
		 */
		if (! str_starts_with($subdomain, 'www.') && $should_add_www) {
			$domains_to_send[] = 'www.' . $subdomain;
		}

		foreach ($domains_to_send as $subdomain) {
			$should_proxy = apply_filters('wu_cloudflare_should_proxy', true, $subdomain, $site_id);

			$data = apply_filters(
				'wu_cloudflare_on_add_domain_data',
				[
					'type'    => 'CNAME',
					'name'    => $subdomain,
					'content' => '@',
					'proxied' => $should_proxy,
					'ttl'     => 1,
				],
				$subdomain,
				$site_id
			);

			$results = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/", 'POST', $data);

			if (is_wp_error($results)) {
				wu_log_add('integration-cloudflare', sprintf('Failed to add subdomain "%s" to Cloudflare. Reason: %s', $subdomain, $results->get_error_message()), LogLevel::ERROR);

				return;
			}

			wu_log_add('integration-cloudflare', sprintf('Added sub-domain "%s" to Cloudflare.', $subdomain));
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

		$zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : '';

		if ( ! $zone_id) {
			return;
		}

		if (! str_contains($subdomain, (string) $current_site->domain)) {
			return; // Not a sub-domain of the main domain.

		}

		$original_subdomain = $subdomain;

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if ( ! $subdomain) {
			return;
		}

		/**
		 * Created the list that we should remove.
		 */
		$domains_to_remove = [
			$original_subdomain,
			'www.' . $original_subdomain,
		];

		foreach ($domains_to_remove as $original_subdomain) {
			$dns_entries = $this->cloudflare_api_call(
				"client/v4/zones/$zone_id/dns_records/",
				'GET',
				[
					'name' => $original_subdomain,
					'type' => 'CNAME',
				]
			);

			if ( ! $dns_entries->result) {
				return;
			}

			$dns_entry_to_remove = $dns_entries->result[0];

			$results = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/$dns_entry_to_remove->id", 'DELETE');

			if (is_wp_error($results)) {
				wu_log_add('integration-cloudflare', sprintf('Failed to remove subdomain "%s" to Cloudflare. Reason: %s', $subdomain, $results->get_error_message()), LogLevel::ERROR);

				return;
			}

			wu_log_add('integration-cloudflare', sprintf('Removed sub-domain "%s" to Cloudflare.', $subdomain));
		}
	}

	/**
	 * Sends an API call to Cloudflare.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param string $method The HTTP verb. Defaults to GET.
	 * @param array  $data The date to send.
	 * @return object|\WP_Error
	 */
	protected function cloudflare_api_call($endpoint = 'client/v4/user/tokens/verify', $method = 'GET', $data = []): object {

		$api_url = 'https://api.cloudflare.com/';

		$endpoint_url = $api_url . $endpoint;

		$response = wp_remote_request(
			$endpoint_url,
			[
				'method'      => $method,
				'body'        => 'GET' === $method ? $data : wp_json_encode($data),
				'data_format' => 'body',
				'headers'     => [
					'Authorization' => sprintf('Bearer %s', defined('WU_CLOUDFLARE_API_KEY') ? WU_CLOUDFLARE_API_KEY : ''),
					'Content-Type'  => 'application/json',
				],
			]
		);

		if ( ! is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);

			if (wp_remote_retrieve_response_code($response) === 200) {
				return json_decode($body);
			} else {
				$error_message = wp_remote_retrieve_response_message($response);

				$response = new \WP_Error('cloudflare-error', sprintf('%s: %s', $error_message, $body));
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

		wu_get_template('wizards/host-integrations/cloudflare-instructions');
	}

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Cloudflare secures and ensures the reliability of your external-facing resources such as websites, APIs, and applications. It protects your internal resources such as behind-the-firewall applications, teams, and devices. And it is your platform for developing globally-scalable applications.', 'multisite-ultimate');
	}

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cloudflare.svg', 'img/hosts');
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
			$explainer_lines['will']['send_sub_domains'] = __('Add a new proxied subdomain to the configured CloudFlare zone whenever a new site gets created', 'multisite-ultimate');
		} else {
			$explainer_lines['will']['subdirectory'] = __('Do nothing! The CloudFlare integration has no effect in subdirectory multisite installs such as this one', 'multisite-ultimate');
		}

		$explainer_lines['will_not']['send_domain'] = __('Add domain mappings as new CloudFlare zones', 'multisite-ultimate');

		return $explainer_lines;
	}
}
