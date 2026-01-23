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
		'dns-management',
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
				$proxied_tag = sprintf('<span class="wu-bg-orange-500 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" role="tooltip" aria-label="%s">%s</span>', __('Proxied', 'ultimate-multisite'), __('Cloudflare', 'ultimate-multisite'));

				$not_proxied_tag = sprintf('<span class="wu-bg-gray-700 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" role="tooltip" aria-label="%s">%s</span>', __('Not Proxied', 'ultimate-multisite'), __('Cloudflare', 'ultimate-multisite'));

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
				'title'       => __('Zone ID', 'ultimate-multisite'),
				'placeholder' => __('e.g. 644c7705723d62e31f700bb798219c75', 'ultimate-multisite'),
			],
			'WU_CLOUDFLARE_API_KEY' => [
				'title'       => __('API Key', 'ultimate-multisite'),
				'placeholder' => __('e.g. xKGbxxVDpdcUv9dUzRf4i4ngv0QNf1wCtbehiec_o', 'ultimate-multisite'),
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

		// Build FQDN so Domain_Manager can classify main vs. subdomain correctly.
		$full_domain    = $subdomain . '.' . $current_site->domain;
		$should_add_www = apply_filters(
			'wu_cloudflare_should_add_www',
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
	 * Get DNS records for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain to query.
	 * @return array|\WP_Error Array of DNS_Record objects or WP_Error.
	 */
	public function get_dns_records(string $domain) {

		$zone_id = $this->get_zone_id($domain);

		if (! $zone_id) {
			return new \WP_Error(
				'zone-not-found',
				sprintf(
					/* translators: %s: domain name */
					__('Could not find Cloudflare zone for domain: %s', 'ultimate-multisite'),
					$domain
				)
			);
		}

		$supported_types = implode(',', $this->get_supported_record_types());

		$response = $this->cloudflare_api_call(
			"client/v4/zones/{$zone_id}/dns_records",
			'GET',
			[
				'per_page' => 100,
				'type'     => $supported_types,
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		if (! isset($response->result) || ! is_array($response->result)) {
			return new \WP_Error(
				'invalid-response',
				__('Invalid response from Cloudflare API.', 'ultimate-multisite')
			);
		}

		$records = [];

		foreach ($response->result as $record) {
			$records[] = DNS_Record::from_provider(
				[
					'id'        => $record->id,
					'type'      => $record->type,
					'name'      => $record->name,
					'content'   => $record->content,
					'ttl'       => $record->ttl,
					'priority'  => $record->priority ?? null,
					'proxied'   => $record->proxied ?? false,
					'zone_id'   => $record->zone_id ?? $zone_id,
					'zone_name' => $record->zone_name ?? '',
				],
				'cloudflare'
			);
		}

		return $records;
	}

	/**
	 * Create a DNS record for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain.
	 * @param array  $record Record data (type, name, content, ttl, priority, proxied).
	 * @return array|\WP_Error Created record data or WP_Error.
	 */
	public function create_dns_record(string $domain, array $record) {

		$zone_id = $this->get_zone_id($domain);

		if (! $zone_id) {
			return new \WP_Error(
				'zone-not-found',
				sprintf(
					/* translators: %s: domain name */
					__('Could not find Cloudflare zone for domain: %s', 'ultimate-multisite'),
					$domain
				)
			);
		}

		$data = [
			'type'    => strtoupper($record['type']),
			'name'    => $record['name'],
			'content' => $record['content'],
			'ttl'     => (int) ($record['ttl'] ?? 1), // 1 = auto
			'proxied' => ! empty($record['proxied']),
		];

		// Add priority for MX records
		if ('MX' === $record['type'] && isset($record['priority'])) {
			$data['priority'] = (int) $record['priority'];
		}

		// Cloudflare doesn't support proxied for certain record types
		if (in_array($data['type'], ['MX', 'TXT'], true)) {
			unset($data['proxied']);
		}

		$response = $this->cloudflare_api_call(
			"client/v4/zones/{$zone_id}/dns_records",
			'POST',
			$data
		);

		if (is_wp_error($response)) {
			wu_log_add(
				'integration-cloudflare',
				sprintf(
					'Failed to create DNS record for %s: %s',
					$domain,
					$response->get_error_message()
				),
				LogLevel::ERROR
			);

			return $response;
		}

		if (! isset($response->result)) {
			return new \WP_Error(
				'invalid-response',
				__('Invalid response from Cloudflare API.', 'ultimate-multisite')
			);
		}

		$created = $response->result;

		wu_log_add(
			'integration-cloudflare',
			sprintf(
				'Created DNS record: %s %s -> %s (ID: %s)',
				$created->type,
				$created->name,
				$created->content,
				$created->id
			)
		);

		return DNS_Record::from_provider(
			[
				'id'       => $created->id,
				'type'     => $created->type,
				'name'     => $created->name,
				'content'  => $created->content,
				'ttl'      => $created->ttl,
				'priority' => $created->priority ?? null,
				'proxied'  => $created->proxied ?? false,
				'zone_id'  => $zone_id,
			],
			'cloudflare'
		)->to_array();
	}

	/**
	 * Update a DNS record for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain    The domain.
	 * @param string $record_id The record identifier.
	 * @param array  $record    Updated record data.
	 * @return array|\WP_Error Updated record data or WP_Error.
	 */
	public function update_dns_record(string $domain, string $record_id, array $record) {

		$zone_id = $this->get_zone_id($domain);

		if (! $zone_id) {
			return new \WP_Error(
				'zone-not-found',
				sprintf(
					/* translators: %s: domain name */
					__('Could not find Cloudflare zone for domain: %s', 'ultimate-multisite'),
					$domain
				)
			);
		}

		$data = [
			'type'    => strtoupper($record['type']),
			'name'    => $record['name'],
			'content' => $record['content'],
			'ttl'     => (int) ($record['ttl'] ?? 1),
			'proxied' => ! empty($record['proxied']),
		];

		// Add priority for MX records
		if ('MX' === $record['type'] && isset($record['priority'])) {
			$data['priority'] = (int) $record['priority'];
		}

		// Cloudflare doesn't support proxied for certain record types
		if (in_array($data['type'], ['MX', 'TXT'], true)) {
			unset($data['proxied']);
		}

		$response = $this->cloudflare_api_call(
			"client/v4/zones/{$zone_id}/dns_records/{$record_id}",
			'PATCH',
			$data
		);

		if (is_wp_error($response)) {
			wu_log_add(
				'integration-cloudflare',
				sprintf(
					'Failed to update DNS record %s for %s: %s',
					$record_id,
					$domain,
					$response->get_error_message()
				),
				LogLevel::ERROR
			);

			return $response;
		}

		if (! isset($response->result)) {
			return new \WP_Error(
				'invalid-response',
				__('Invalid response from Cloudflare API.', 'ultimate-multisite')
			);
		}

		$updated = $response->result;

		wu_log_add(
			'integration-cloudflare',
			sprintf(
				'Updated DNS record: %s %s -> %s (ID: %s)',
				$updated->type,
				$updated->name,
				$updated->content,
				$updated->id
			)
		);

		return DNS_Record::from_provider(
			[
				'id'       => $updated->id,
				'type'     => $updated->type,
				'name'     => $updated->name,
				'content'  => $updated->content,
				'ttl'      => $updated->ttl,
				'priority' => $updated->priority ?? null,
				'proxied'  => $updated->proxied ?? false,
				'zone_id'  => $zone_id,
			],
			'cloudflare'
		)->to_array();
	}

	/**
	 * Delete a DNS record for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain    The domain.
	 * @param string $record_id The record identifier.
	 * @return bool|\WP_Error True on success or WP_Error.
	 */
	public function delete_dns_record(string $domain, string $record_id) {

		$zone_id = $this->get_zone_id($domain);

		if (! $zone_id) {
			return new \WP_Error(
				'zone-not-found',
				sprintf(
					/* translators: %s: domain name */
					__('Could not find Cloudflare zone for domain: %s', 'ultimate-multisite'),
					$domain
				)
			);
		}

		$response = $this->cloudflare_api_call(
			"client/v4/zones/{$zone_id}/dns_records/{$record_id}",
			'DELETE'
		);

		if (is_wp_error($response)) {
			wu_log_add(
				'integration-cloudflare',
				sprintf(
					'Failed to delete DNS record %s for %s: %s',
					$record_id,
					$domain,
					$response->get_error_message()
				),
				LogLevel::ERROR
			);

			return $response;
		}

		wu_log_add(
			'integration-cloudflare',
			sprintf(
				'Deleted DNS record: ID %s for domain %s',
				$record_id,
				$domain
			)
		);

		return true;
	}

	/**
	 * Get the zone ID for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain name.
	 * @return string|null Zone ID or null if not found.
	 */
	public function get_zone_id(string $domain): ?string {

		// Try configured zone first
		$default_zone = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : null;

		// Extract root domain for zone lookup
		$root_domain = $this->extract_root_domain($domain);

		// Try to find zone by domain name
		$response = $this->cloudflare_api_call(
			'client/v4/zones',
			'GET',
			[
				'name'   => $root_domain,
				'status' => 'active',
			]
		);

		if (! is_wp_error($response) && ! empty($response->result)) {
			return $response->result[0]->id;
		}

		// Fall back to configured zone
		return $default_zone;
	}

	/**
	 * Extract the root domain from a full domain name.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The full domain name.
	 * @return string The root domain.
	 */
	protected function extract_root_domain(string $domain): string {

		$parts = explode('.', $domain);

		// Known multi-part TLDs
		$multi_tlds = ['.co.uk', '.com.au', '.co.nz', '.com.br', '.co.in', '.org.uk', '.net.au'];

		foreach ($multi_tlds as $tld) {
			if (str_ends_with($domain, $tld)) {
				// Return last 3 parts for multi-part TLD
				return implode('.', array_slice($parts, -3));
			}
		}

		// Return last 2 parts for standard TLD
		if (count($parts) >= 2) {
			return implode('.', array_slice($parts, -2));
		}

		return $domain;
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

		return __('Cloudflare secures and ensures the reliability of your external-facing resources such as websites, APIs, and applications. It protects your internal resources such as behind-the-firewall applications, teams, and devices. And it is your platform for developing globally-scalable applications.', 'ultimate-multisite');
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
			$explainer_lines['will']['send_sub_domains'] = __('Add a new proxied subdomain to the configured CloudFlare zone whenever a new site gets created', 'ultimate-multisite');
		} else {
			$explainer_lines['will']['subdirectory'] = __('Do nothing! The CloudFlare integration has no effect in subdirectory multisite installs such as this one', 'ultimate-multisite');
		}

		$explainer_lines['will_not']['send_domain'] = __('Add domain mappings as new CloudFlare zones', 'ultimate-multisite');

		return $explainer_lines;
	}
}
