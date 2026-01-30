<?php
/**
 * Microsoft 365 Email Provider integration.
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
 * Microsoft 365 Email Provider.
 *
 * Uses Microsoft Graph API to manage email accounts.
 *
 * @see https://learn.microsoft.com/en-us/graph/api/resources/users
 * @since 2.3.0
 */
class Microsoft365_Provider extends Base_Email_Provider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id = 'microsoft365';

	/**
	 * Provider title.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title = 'Microsoft 365';

	/**
	 * Documentation link.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $documentation_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Microsoft-365-Email-Integration';

	/**
	 * Affiliate URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $affiliate_url = 'https://www.microsoft.com/en-us/microsoft-365/business';

	/**
	 * Required constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $constants = [
		'WU_MS365_CLIENT_ID',
		'WU_MS365_CLIENT_SECRET',
		'WU_MS365_TENANT_ID',
	];

	/**
	 * Optional constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $optional_constants = [
		'WU_MS365_LICENSE_SKU',
	];

	/**
	 * Graph API base URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	const GRAPH_API_URL = 'https://graph.microsoft.com/v1.0';

	/**
	 * Cached access token.
	 *
	 * @var string|null
	 * @since 2.3.0
	 */
	protected $access_token = null;

	/**
	 * Returns the description of this provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_description() {

		return __('Enterprise email with Microsoft 365. Includes Outlook, OneDrive, Teams, and the full Office suite for your custom domain.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo URL for the provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('microsoft365.svg', 'img/hosts');
	}

	/**
	 * Get the configuration fields.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_MS365_CLIENT_ID'     => [
				'title'       => __('Application (Client) ID', 'ultimate-multisite'),
				'placeholder' => __('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'ultimate-multisite'),
				'desc'        => sprintf(
					/* translators: %s is the link to Azure portal */
					__('From your Azure AD app registration in the %s.', 'ultimate-multisite'),
					'<a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" rel="noopener">' . __('Azure Portal', 'ultimate-multisite') . '</a>'
				),
			],
			'WU_MS365_CLIENT_SECRET' => [
				'type'        => 'password',
				'title'       => __('Client Secret', 'ultimate-multisite'),
				'placeholder' => __('Your client secret', 'ultimate-multisite'),
				'desc'        => __('Create a client secret in your Azure AD app registration.', 'ultimate-multisite'),
			],
			'WU_MS365_TENANT_ID'     => [
				'title'       => __('Tenant ID', 'ultimate-multisite'),
				'placeholder' => __('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'ultimate-multisite'),
				'desc'        => __('Your Azure AD tenant ID (Directory ID).', 'ultimate-multisite'),
			],
			'WU_MS365_LICENSE_SKU'   => [
				'title'       => __('License SKU ID (Optional)', 'ultimate-multisite'),
				'placeholder' => __('e.g. 18181a46-0d4e-45cd-891e-60aabd171b4e', 'ultimate-multisite'),
				'desc'        => __('The SKU ID of the license to assign. Leave empty to skip license assignment.', 'ultimate-multisite'),
			],
		];
	}

	/**
	 * Gets an access token using client credentials.
	 *
	 * @since 2.3.0
	 * @return string|\WP_Error
	 */
	protected function get_access_token() {

		if (null !== $this->access_token) {
			return $this->access_token;
		}

		// Check for cached token
		$cached = get_transient('wu_ms365_token');
		if ($cached) {
			$this->access_token = $cached;
			return $cached;
		}

		$tenant_id     = defined('WU_MS365_TENANT_ID') ? WU_MS365_TENANT_ID : '';
		$client_id     = defined('WU_MS365_CLIENT_ID') ? WU_MS365_CLIENT_ID : '';
		$client_secret = defined('WU_MS365_CLIENT_SECRET') ? WU_MS365_CLIENT_SECRET : '';

		if (empty($tenant_id) || empty($client_id) || empty($client_secret)) {
			return new \WP_Error('missing_credentials', __('Microsoft 365 credentials are not configured.', 'ultimate-multisite'));
		}

		$token_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";

		$response = wp_remote_post(
			$token_url,
			[
				'body' => [
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'scope'         => 'https://graph.microsoft.com/.default',
					'grant_type'    => 'client_credentials',
				],
			]
		);

		if (is_wp_error($response)) {
			$this->log('Token request error: ' . $response->get_error_message(), 'error');
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (isset($body['error'])) {
			$error_msg = isset($body['error_description']) ? $body['error_description'] : $body['error'];
			$this->log('Token error: ' . $error_msg, 'error');
			return new \WP_Error('token_error', $error_msg);
		}

		if (! isset($body['access_token'])) {
			return new \WP_Error('no_token', __('Failed to obtain access token.', 'ultimate-multisite'));
		}

		$this->access_token = $body['access_token'];

		// Cache for slightly less than expiry
		$expires_in = isset($body['expires_in']) ? (int) $body['expires_in'] - 60 : 3540;
		set_transient('wu_ms365_token', $this->access_token, $expires_in);

		return $this->access_token;
	}

	/**
	 * Makes an API request to Microsoft Graph.
	 *
	 * @since 2.3.0
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data     The data to send.
	 * @param string $method   The HTTP method.
	 * @return object|\WP_Error
	 */
	protected function api_request($endpoint, $data = [], $method = 'GET') {

		$token = $this->get_access_token();

		if (is_wp_error($token)) {
			return $token;
		}

		$url = self::GRAPH_API_URL . '/' . ltrim($endpoint, '/');

		$args = [
			'method'  => $method,
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			],
			'timeout' => 30,
		];

		if (! empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
			$args['body'] = wp_json_encode($data);
		}

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			$this->log('API request error: ' . $response->get_error_message(), 'error');
			return $response;
		}

		$body = wp_remote_retrieve_body($response);
		$code = wp_remote_retrieve_response_code($response);

		// 204 No Content is success for DELETE
		if (204 === $code) {
			return (object) ['success' => true];
		}

		$result = json_decode($body);

		if ($code >= 400) {
			$error_message = isset($result->error->message) ? $result->error->message : __('API request failed.', 'ultimate-multisite');
			$this->log('API error: ' . $error_message, 'error');
			return new \WP_Error('api_error', $error_message);
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
			'username'     => '',
			'domain'       => '',
			'password'     => '',
			'display_name' => '',
			'first_name'   => '',
			'last_name'    => '',
			'quota_mb'     => 0,
		];

		$params = wp_parse_args($params, $defaults);

		if (empty($params['username']) || empty($params['domain']) || empty($params['password'])) {
			return new \WP_Error(
				'missing_params',
				__('Username, domain, and password are required.', 'ultimate-multisite')
			);
		}

		$email_address = $params['username'] . '@' . $params['domain'];

		// Default display name if not provided
		$display_name = ! empty($params['display_name']) ? $params['display_name'] : $params['username'];
		$first_name   = ! empty($params['first_name']) ? $params['first_name'] : $params['username'];
		$last_name    = ! empty($params['last_name']) ? $params['last_name'] : 'User';

		// Microsoft requires mailNickname (alias before @)
		$mail_nickname = $params['username'];

		$user_data = [
			'accountEnabled'    => true,
			'displayName'       => $display_name,
			'mailNickname'      => $mail_nickname,
			'userPrincipalName' => $email_address,
			'givenName'         => $first_name,
			'surname'           => $last_name,
			'passwordProfile'   => [
				'forceChangePasswordNextSignIn' => false,
				'password'                      => $params['password'],
			],
			'usageLocation'     => 'US', // Required for license assignment
		];

		$result = $this->api_request('users', $user_data, 'POST');

		if (is_wp_error($result)) {
			return $result;
		}

		$user_id = isset($result->id) ? $result->id : '';

		// Assign license if configured
		if (! empty($user_id)) {
			$license_result = $this->assign_license($user_id);
			if (is_wp_error($license_result)) {
				$this->log('License assignment failed: ' . $license_result->get_error_message(), 'warning');
				// Don't fail the account creation, just log the warning
			}
		}

		$this->log(sprintf('Email account created: %s', $email_address));

		return [
			'email_address' => $email_address,
			'external_id'   => $user_id,
			'quota_mb'      => $params['quota_mb'],
		];
	}

	/**
	 * Assigns a license to a user.
	 *
	 * @since 2.3.0
	 *
	 * @param string $user_id The user ID.
	 * @return bool|\WP_Error
	 */
	protected function assign_license($user_id) {

		$license_sku = defined('WU_MS365_LICENSE_SKU') ? WU_MS365_LICENSE_SKU : '';

		if (empty($license_sku)) {
			return true; // No license to assign
		}

		$result = $this->api_request(
			"users/{$user_id}/assignLicense",
			[
				'addLicenses'    => [
					[
						'skuId' => $license_sku,
					],
				],
				'removeLicenses' => [],
			],
			'POST'
		);

		if (is_wp_error($result)) {
			return $result;
		}

		$this->log(sprintf('License assigned to user: %s', $user_id));

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
			'users/' . urlencode($email_address),
			[],
			'DELETE'
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
			'users/' . urlencode($email_address),
			[
				'passwordProfile' => [
					'forceChangePasswordNextSignIn' => false,
					'password'                      => $new_password,
				],
			],
			'PATCH'
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

		$result = $this->api_request('users/' . urlencode($email_address));

		if (is_wp_error($result)) {
			return $result;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Microsoft Graph API response properties.
		return [
			'email_address' => $email_address,
			'display_name'  => isset($result->displayName) ? $result->displayName : '',
			'first_name'    => isset($result->givenName) ? $result->givenName : '',
			'last_name'     => isset($result->surname) ? $result->surname : '',
			'suspended'     => isset($result->accountEnabled) ? ! $result->accountEnabled : false,
			'quota_mb'      => 0, // Microsoft 365 uses license-based storage
		];
		// phpcs:enable
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

		return 'https://outlook.office365.com/';
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
				'value'       => $domain . '.mail.protection.outlook.com',
				'priority'    => 0,
				'description' => __('Microsoft 365 mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'TXT',
				'name'        => '@',
				'value'       => 'v=spf1 include:spf.protection.outlook.com -all',
				'description' => __('SPF record to authorize Microsoft to send email.', 'ultimate-multisite'),
			],
			[
				'type'        => 'CNAME',
				'name'        => 'autodiscover',
				'value'       => 'autodiscover.outlook.com',
				'description' => __('Autodiscover for automatic email client configuration.', 'ultimate-multisite'),
			],
			[
				'type'        => 'CNAME',
				'name'        => 'selector1._domainkey',
				'value'       => 'selector1-' . str_replace('.', '-', $domain) . '._domainkey.YOUR_TENANT.onmicrosoft.com',
				'description' => __('DKIM record for email authentication. Replace YOUR_TENANT with your Microsoft tenant name.', 'ultimate-multisite'),
			],
			[
				'type'        => 'CNAME',
				'name'        => 'selector2._domainkey',
				'value'       => 'selector2-' . str_replace('.', '-', $domain) . '._domainkey.YOUR_TENANT.onmicrosoft.com',
				'description' => __('Secondary DKIM record. Replace YOUR_TENANT with your Microsoft tenant name.', 'ultimate-multisite'),
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
			'server'   => 'outlook.office365.com',
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
			'server'   => 'smtp.office365.com',
			'port'     => 587,
			'security' => 'STARTTLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Tests the connection with Microsoft 365.
	 *
	 * @since 2.3.0
	 * @return bool|\WP_Error
	 */
	public function test_connection() {

		// Try to get organization info
		$result = $this->api_request('organization');

		if (is_wp_error($result)) {
			return $result;
		}

		return true;
	}
}
