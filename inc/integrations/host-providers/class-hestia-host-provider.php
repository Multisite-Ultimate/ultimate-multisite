<?php
/**
 * Adds domain mapping support for Hestia Control Panel (via API wrapping CLI).
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Hestia_Host_Provider
 * @since 2.x
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use Psr\Log\LogLevel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Hestia Control Panel integration.
 *
 * Implements add/remove domain alias via Hestia API commands:
 * - v-add-web-domain-alias
 * - v-delete-web-domain-alias
 */
class Hestia_Host_Provider extends Base_Host_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Integration slug/id.
	 *
	 * @var string
	 */
	protected $id = 'hestia';

	/**
	 * Integration title.
	 *
	 * @var string
	 */
	protected $title = 'Hestia Control Panel';

	/**
	 * Docs link (optional for now).
	 *
	 * @var string
	 */
	protected $tutorial_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Hestia-Integration';

	/**
	 * Supported features.
	 *
	 * @var array
	 */
	protected $supports = [
		'no-instructions',
	];

	/**
	 * Required constants for configuration.
	 *
	 * @var array
	 */
	protected $constants = [
		'WU_HESTIA_API_URL',
		// Allow either hash-based auth OR user/password (at least one must be provided)
		['WU_HESTIA_API_HASH', 'WU_HESTIA_API_PASSWORD'],
		'WU_HESTIA_API_USER',
		'WU_HESTIA_ACCOUNT',
		'WU_HESTIA_WEB_DOMAIN',
	];

	/**
	 * Optional constants.
	 *
	 * @var array
	 */
	protected $optional_constants = [
		'WU_HESTIA_RESTART',
	];

	/**
	 * Try to detect Hestia environment.
	 *
	 * There's no reliable detection from within WordPress, so default to false.
	 */
	public function detect(): bool {

		return false;
	}

	/**
	 * Fields for the configuration wizard.
	 */
	public function get_fields() {

		return [
			'WU_HESTIA_API_URL'      => [
				'title'       => __('Hestia API URL', 'ultimate-multisite'),
				'desc'        => __('Base API endpoint, typically https://your-hestia:8083/api/', 'ultimate-multisite'),
				'placeholder' => __('e.g. https://server.example.com:8083/api/', 'ultimate-multisite'),
			],
			'WU_HESTIA_API_USER'     => [
				'title'       => __('Hestia API Username', 'ultimate-multisite'),
				'desc'        => __('Hestia user for API calls (often admin)', 'ultimate-multisite'),
				'placeholder' => __('e.g. admin', 'ultimate-multisite'),
			],
			'WU_HESTIA_API_PASSWORD' => [
				'type'        => 'password',
				'title'       => __('Hestia API Password', 'ultimate-multisite'),
				'desc'        => __('Optional if using API hash authentication.', 'ultimate-multisite'),
				'placeholder' => __('••••••••', 'ultimate-multisite'),
			],
			'WU_HESTIA_API_HASH'     => [
				'title'       => __('Hestia API Hash (Token)', 'ultimate-multisite'),
				'desc'        => __('Optional: API hash/token alternative to password. Provide either this OR a password.', 'ultimate-multisite'),
				'placeholder' => __('e.g. 1a2b3c4d...', 'ultimate-multisite'),
			],
			'WU_HESTIA_ACCOUNT'      => [
				'title'       => __('Hestia Account (Owner)', 'ultimate-multisite'),
				'desc'        => __('The Hestia user that owns the web domain (first argument to v-add-web-domain-alias).', 'ultimate-multisite'),
				'placeholder' => __('e.g. admin', 'ultimate-multisite'),
			],
			'WU_HESTIA_WEB_DOMAIN'   => [
				'title'       => __('Base Web Domain', 'ultimate-multisite'),
				'desc'        => __('Existing Hestia web domain that your WordPress is served from. Aliases will be attached to this.', 'ultimate-multisite'),
				'placeholder' => __('e.g. network.example.com', 'ultimate-multisite'),
			],
			'WU_HESTIA_RESTART'      => [
				'title'       => __('Restart Web Service', 'ultimate-multisite'),
				'desc'        => __('Whether to restart/reload services after alias changes (yes/no). Defaults to yes.', 'ultimate-multisite'),
				'placeholder' => __('yes', 'ultimate-multisite'),
				'value'       => 'yes',
			],
		];
	}

	/**
	 * Add domain alias when a new mapping is created.
	 *
	 * @param string $domain  Domain name to add.
	 * @param int    $site_id Site ID.
	 */
	public function on_add_domain($domain, $site_id): void {

		$account     = $this->get_credential('WU_HESTIA_ACCOUNT');
		$base_domain = $this->get_credential('WU_HESTIA_WEB_DOMAIN');
		$restart     = $this->get_credential('WU_HESTIA_RESTART') ?: 'yes';

		if (empty($account) || empty($base_domain)) {
			wu_log_add('integration-hestia', __('Missing WU_HESTIA_ACCOUNT or WU_HESTIA_WEB_DOMAIN; cannot add alias.', 'ultimate-multisite'), LogLevel::ERROR);
			return;
		}

		// Add primary alias
		$this->call_and_log('v-add-web-domain-alias', [$account, $base_domain, $domain, $restart], sprintf('Add alias %s', $domain));

		// Optionally add www alias if configured
		if (! str_starts_with($domain, 'www.') && \WP_Ultimo\Managers\Domain_Manager::get_instance()->should_create_www_subdomain($domain)) {
			$www = 'www.' . $domain;
			$this->call_and_log('v-add-web-domain-alias', [$account, $base_domain, $www, $restart], sprintf('Add alias %s', $www));
		}
	}

	/**
	 * Remove domain alias when mapping is deleted.
	 *
	 * @param string $domain  Domain name to remove.
	 * @param int    $site_id Site ID.
	 */
	public function on_remove_domain($domain, $site_id): void {

		$account     = $this->get_credential('WU_HESTIA_ACCOUNT');
		$base_domain = $this->get_credential('WU_HESTIA_WEB_DOMAIN');
		$restart     = $this->get_credential('WU_HESTIA_RESTART') ?: 'yes';

		if (empty($account) || empty($base_domain)) {
			wu_log_add('integration-hestia', __('Missing WU_HESTIA_ACCOUNT or WU_HESTIA_WEB_DOMAIN; cannot remove alias.', 'ultimate-multisite'), LogLevel::ERROR);
			return;
		}

		// Remove primary alias
		$this->call_and_log('v-delete-web-domain-alias', [$account, $base_domain, $domain, $restart], sprintf('Delete alias %s', $domain));

		// Also try to remove www alias if it exists
		$www = 'www.' . ltrim($domain, '.');
		if (! str_starts_with($domain, 'www.')) {
			$this->call_and_log('v-delete-web-domain-alias', [$account, $base_domain, $www, $restart], sprintf('Delete alias %s', $www));
		}
	}

	/**
	 * Not used for Hestia. Subdomain installs are handled via aliases too, if needed.
	 *
	 * @param string $subdomain Subdomain to add.
	 * @param int    $site_id   Site ID.
	 */
	public function on_add_subdomain($subdomain, $site_id) {}

	/**
	 * Not used for Hestia. Subdomain installs are handled via aliases too, if needed.
	 *
	 * @param string $subdomain Subdomain to remove.
	 * @param int    $site_id   Site ID.
	 */
	public function on_remove_subdomain($subdomain, $site_id) {}

	/**
	 * Test connection by listing web domains for the configured account.
	 */
	public function test_connection(): void {

		$account = $this->get_credential('WU_HESTIA_ACCOUNT');

		$response = $this->send_hestia_request('v-list-web-domains', [$account, 'json']);

		if (is_wp_error($response)) {
			wp_send_json_error($response);
			return;
		}

		wp_send_json_success($response);
	}

	/**
	 * Description.
	 */
	public function get_description() {

		return __('Integrates with Hestia Control Panel to add and remove web domain aliases automatically when domains are mapped or removed.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo for the integration.
	 */
	public function get_logo() {

		return wu_get_asset('hestia.svg', 'img/hosts');
	}

	/**
	 * Perform a Hestia API call and log result.
	 *
	 * @param string $cmd  Hestia command (e.g., v-add-web-domain-alias).
	 * @param array  $args Command args.
	 * @param string $action_label Log label.
	 * @return void
	 */
	protected function call_and_log($cmd, $args, $action_label): void {

		$result = $this->send_hestia_request($cmd, $args);

		if (is_wp_error($result)) {
			wu_log_add('integration-hestia', sprintf('[%s] %s', $action_label, $result->get_error_message()), LogLevel::ERROR);
			return;
		}

		wu_log_add('integration-hestia', sprintf('[%s] %s', $action_label, is_scalar($result) ? (string) $result : wp_json_encode($result)));
	}

	/**
	 * Send request to Hestia API. Returns body string or array/object if JSON, or WP_Error on failure.
	 *
	 * @param string $cmd  Command name (e.g., v-add-web-domain-alias).
	 * @param array  $args Positional args for the command.
	 * @return mixed|\WP_Error
	 */
	protected function send_hestia_request($cmd, $args = []) {

		$url = $this->get_credential('WU_HESTIA_API_URL');

		if (empty($url)) {
			return new \WP_Error('wu_hestia_no_url', __('Missing WU_HESTIA_API_URL', 'ultimate-multisite'));
		}

		// Normalize URL to point to /api endpoint
		$url = rtrim($url, '/');
		if (! preg_match('#/api$#', $url)) {
			$url .= '/api';
		}

		$body = [
			'cmd'        => $cmd,
			'returncode' => 'yes',
		];

		// Auth: prefer hash if provided, otherwise username/password
		$api_user = $this->get_credential('WU_HESTIA_API_USER');
		$api_hash = $this->get_credential('WU_HESTIA_API_HASH');
		$api_pass = $this->get_credential('WU_HESTIA_API_PASSWORD');

		$body['user'] = $api_user;
		if (! empty($api_hash)) {
			$body['hash'] = $api_hash;
		} else {
			$body['password'] = $api_pass;
		}

		// Map args to arg1..argN
		$index = 1;
		foreach ((array) $args as $arg) {
			$body[ 'arg' . $index ] = (string) $arg;
			++$index;
		}

		$response = wp_remote_post(
			$url,
			[
				'timeout' => 60,
				'body'    => $body,
				'method'  => 'POST',
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code($response);
		$raw  = wp_remote_retrieve_body($response);

		if (200 !== $code) {
			/* translators: %1$d: HTTP status code, %2$s: Response body */
			return new \WP_Error('wu_hestia_http_error', sprintf(__('HTTP %1$d from Hestia API: %2$s', 'ultimate-multisite'), $code, $raw));
		}

		// With returncode=yes Hestia typically returns numeric code (0 success). Keep raw for logs.
		$trim = trim((string) $raw);

		if ('0' === $trim) {
			return '0';
		}

		// Try to decode JSON if present, otherwise return raw string
		$json = json_decode($raw);
		return null !== $json ? $json : $raw;
	}
}
