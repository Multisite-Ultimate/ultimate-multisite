<?php
/**
 * Purelymail Email Provider integration.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Email_Providers
 * @since 2.3.0
 */

namespace WP_Ultimo\Integrations\Email_Providers;

use WP_Ultimo\Models\Email_Account;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Purelymail Email Provider.
 *
 * Integrates with Purelymail API for email account management.
 *
 * @see https://news.purelymail.com/api/index.html
 * @since 2.3.0
 */
class Purelymail_Provider extends Base_Email_Provider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id = 'purelymail';

	/**
	 * Provider title.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title = 'Purelymail';

	/**
	 * Documentation link.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $documentation_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Purelymail-Email-Integration';

	/**
	 * Affiliate URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $affiliate_url = 'https://purelymail.com/?ref=ultimatemultisite';

	/**
	 * Required constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $constants = [
		'WU_PURELYMAIL_API_KEY',
	];

	/**
	 * API base URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	const API_BASE_URL = 'https://purelymail.com/api/v0';

	/**
	 * Returns the description of this provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_description() {

		return __('Affordable, privacy-focused email hosting with excellent deliverability. Purelymail offers simple pricing and powerful features.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo URL for the provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('purelymail.svg', 'img/hosts');
	}

	/**
	 * Get the configuration fields.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_PURELYMAIL_API_KEY' => [
				'type'        => 'password',
				'title'       => __('Purelymail API Key', 'ultimate-multisite'),
				'placeholder' => __('Your API key from Purelymail settings', 'ultimate-multisite'),
				'desc'        => sprintf(
					/* translators: %s is the link to get an API key */
					__('Get your API key from your %s.', 'ultimate-multisite'),
					'<a href="https://purelymail.com/manage/account" target="_blank" rel="noopener">' . __('Purelymail account settings', 'ultimate-multisite') . '</a>'
				),
			],
		];
	}

	/**
	 * Makes an API request to Purelymail.
	 *
	 * @since 2.3.0
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data     The data to send.
	 * @param string $method   The HTTP method.
	 * @return object|\WP_Error
	 */
	protected function api_request($endpoint, $data = [], $method = 'POST') {

		$api_key = defined('WU_PURELYMAIL_API_KEY') ? WU_PURELYMAIL_API_KEY : '';

		if (empty($api_key)) {
			return new \WP_Error('no_api_key', __('Purelymail API key is not configured.', 'ultimate-multisite'));
		}

		$url = self::API_BASE_URL . '/' . ltrim($endpoint, '/');

		$args = [
			'method'  => $method,
			'headers' => [
				'Content-Type'       => 'application/json',
				'Purelymail-Api-Key' => $api_key,
			],
			'timeout' => 30,
		];

		if (! empty($data)) {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			$this->log('API request error: ' . $response->get_error_message(), 'error');
			return $response;
		}

		$body = wp_remote_retrieve_body($response);
		$code = wp_remote_retrieve_response_code($response);

		$result = json_decode($body);

		if (JSON_ERROR_NONE !== json_last_error()) {
			return new \WP_Error('json_error', __('Failed to parse API response.', 'ultimate-multisite'));
		}

		// Purelymail returns {success: true/false, message: "...", result: {...}}
		if (isset($result->success) && ! $result->success) {
			$error_message = isset($result->message) ? $result->message : __('Unknown API error.', 'ultimate-multisite');
			$this->log('API error: ' . $error_message, 'error');
			return new \WP_Error('api_error', $error_message);
		}

		if ($code >= 400) {
			$error_message = isset($result->message) ? $result->message : __('API request failed.', 'ultimate-multisite');
			return new \WP_Error('api_http_error', $error_message);
		}

		return $result;
	}

	/**
	 * Creates an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param array $params The account parameters.
	 * @return array|\WP_Error
	 */
	public function create_email_account(array $params) {

		$defaults = [
			'username' => '',
			'domain'   => '',
			'password' => '',
			'quota_mb' => 0, // Purelymail doesn't use per-account quotas in the same way
		];

		$params = wp_parse_args($params, $defaults);

		if (empty($params['username']) || empty($params['domain']) || empty($params['password'])) {
			return new \WP_Error(
				'missing_params',
				__('Username, domain, and password are required.', 'ultimate-multisite')
			);
		}

		$email_address = $params['username'] . '@' . $params['domain'];

		// First, ensure the domain is added to Purelymail
		$domain_result = $this->ensure_domain_exists($params['domain']);
		if (is_wp_error($domain_result)) {
			// Domain might already exist, which is fine
			$this->log('Domain check result: ' . $domain_result->get_error_message());
		}

		// Create the user
		$result = $this->api_request(
			'createUser',
			[
				'userName'            => $email_address,
				'password'            => $params['password'],
				'enablePasswordReset' => false,
			]
		);

		if (is_wp_error($result)) {
			return $result;
		}

		$this->log(sprintf('Email account created: %s', $email_address));

		return [
			'email_address' => $email_address,
			'external_id'   => $email_address, // Purelymail uses email as ID
			'quota_mb'      => 0, // Purelymail uses account-level storage
		];
	}

	/**
	 * Ensures a domain exists in Purelymail.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain name.
	 * @return true|\WP_Error
	 */
	protected function ensure_domain_exists($domain) {

		$result = $this->api_request(
			'addDomainName',
			[
				'domainName' => $domain,
			]
		);

		if (is_wp_error($result)) {
			// Check if error is because domain already exists
			if (false !== strpos($result->get_error_message(), 'already')) {
				return true;
			}
			return $result;
		}

		return true;
	}

	/**
	 * Deletes an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address to delete.
	 * @return bool|\WP_Error
	 */
	public function delete_email_account($email_address) {

		$result = $this->api_request(
			'deleteUser',
			[
				'userName' => $email_address,
			]
		);

		if (is_wp_error($result)) {
			return $result;
		}

		$this->log(sprintf('Email account deleted: %s', $email_address));

		return true;
	}

	/**
	 * Changes the password for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address.
	 * @param string $new_password  The new password.
	 * @return bool|\WP_Error
	 */
	public function change_password($email_address, $new_password) {

		$result = $this->api_request(
			'modifyUser',
			[
				'userName' => $email_address,
				'password' => $new_password,
			]
		);

		if (is_wp_error($result)) {
			return $result;
		}

		$this->log(sprintf('Password changed for: %s', $email_address));

		return true;
	}

	/**
	 * Gets information about an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address.
	 * @return array|\WP_Error
	 */
	public function get_account_info($email_address) {

		$result = $this->api_request(
			'getUser',
			[
				'userName' => $email_address,
			]
		);

		if (is_wp_error($result)) {
			return $result;
		}

		// Handle nested result structure
		$user_data = isset($result->result) ? $result->result : $result;

		return [
			'email_address' => $email_address,
			'quota_mb'      => 0, // Purelymail uses account-level storage
			'disk_used_mb'  => 0,
		];
	}

	/**
	 * Gets the webmail URL for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param Email_Account $account The email account.
	 * @return string
	 */
	public function get_webmail_url(Email_Account $account) {

		return 'https://app.purelymail.com/';
	}

	/**
	 * Gets the DNS instructions for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain.
	 * @return array
	 */
	public function get_dns_instructions($domain) {

		return [
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'mailserver.purelymail.com',
				'priority'    => 10,
				'description' => __('Mail exchanger record for receiving email.', 'ultimate-multisite'),
			],
			[
				'type'        => 'TXT',
				'name'        => '@',
				'value'       => 'v=spf1 include:_spf.purelymail.com ~all',
				'description' => __('SPF record to authorize Purelymail to send email on your behalf.', 'ultimate-multisite'),
			],
			[
				'type'        => 'CNAME',
				'name'        => 'purelymail._domainkey',
				'value'       => 'key1._domainkey.purelymail.com',
				'description' => __('DKIM record for email authentication.', 'ultimate-multisite'),
			],
			[
				'type'        => 'TXT',
				'name'        => '_dmarc',
				'value'       => 'v=DMARC1; p=quarantine; rua=mailto:dmarc@' . $domain,
				'description' => __('DMARC policy for handling unauthenticated email.', 'ultimate-multisite'),
			],
		];
	}

	/**
	 * Gets the IMAP settings for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param Email_Account $account The email account.
	 * @return array
	 */
	public function get_imap_settings(Email_Account $account) {

		return [
			'server'   => 'imap.purelymail.com',
			'port'     => 993,
			'security' => 'SSL/TLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Gets the SMTP settings for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param Email_Account $account The email account.
	 * @return array
	 */
	public function get_smtp_settings(Email_Account $account) {

		return [
			'server'   => 'smtp.purelymail.com',
			'port'     => 587,
			'security' => 'STARTTLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Tests the connection with Purelymail.
	 *
	 * @since 2.3.0
	 * @return bool|\WP_Error
	 */
	public function test_connection() {

		// Try to list domains - this will test the connection
		$result = $this->api_request('listDomainNames', []);

		if (is_wp_error($result)) {
			return $result;
		}

		return true;
	}
}
