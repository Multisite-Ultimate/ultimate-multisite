<?php
/**
 * Google Workspace Email Provider integration.
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
 * Google Workspace Email Provider.
 *
 * Uses Google Admin SDK Directory API to manage email accounts.
 *
 * @see https://developers.google.com/admin-sdk/directory
 * @since 2.3.0
 */
class Google_Workspace_Provider extends Base_Email_Provider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id = 'google_workspace';

	/**
	 * Provider title.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title = 'Google Workspace';

	/**
	 * Documentation link.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $documentation_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/Google-Workspace-Email-Integration';

	/**
	 * Affiliate URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $affiliate_url = 'https://referworkspace.app.goo.gl/ultimatemultisite';

	/**
	 * Required constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $constants = [
		'WU_GOOGLE_SERVICE_ACCOUNT_JSON',
		'WU_GOOGLE_ADMIN_EMAIL',
		'WU_GOOGLE_CUSTOMER_ID',
	];

	/**
	 * API base URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	const API_BASE_URL = 'https://admin.googleapis.com/admin/directory/v1';

	/**
	 * OAuth2 token URL.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	const TOKEN_URL = 'https://oauth2.googleapis.com/token';

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

		return __('Professional email hosting with Google Workspace. Includes Gmail, Drive, Calendar, and more for your custom domain.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo URL for the provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('google-workspace.svg', 'img/hosts');
	}

	/**
	 * Get the configuration fields.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_GOOGLE_SERVICE_ACCOUNT_JSON' => [
				'title'       => __('Service Account JSON Path', 'ultimate-multisite'),
				'placeholder' => __('/path/to/service-account.json', 'ultimate-multisite'),
				'desc'        => sprintf(
					/* translators: %s is the link to Google Cloud Console */
					__('Path to your service account JSON file. Create one in the %s.', 'ultimate-multisite'),
					'<a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" rel="noopener">' . __('Google Cloud Console', 'ultimate-multisite') . '</a>'
				),
			],
			'WU_GOOGLE_ADMIN_EMAIL'          => [
				'title'       => __('Admin Email', 'ultimate-multisite'),
				'placeholder' => __('admin@yourdomain.com', 'ultimate-multisite'),
				'desc'        => __('A super admin email address for domain-wide delegation.', 'ultimate-multisite'),
			],
			'WU_GOOGLE_CUSTOMER_ID'          => [
				'title'       => __('Customer ID', 'ultimate-multisite'),
				'placeholder' => __('C01234567', 'ultimate-multisite'),
				'desc'        => sprintf(
					/* translators: %s is the link to Google Admin Console */
					__('Your Google Workspace customer ID. Find it in %s.', 'ultimate-multisite'),
					'<a href="https://admin.google.com/ac/accountsettings" target="_blank" rel="noopener">' . __('Admin Console > Account Settings', 'ultimate-multisite') . '</a>'
				),
			],
		];
	}

	/**
	 * Gets an access token using service account credentials.
	 *
	 * @since 2.3.0
	 * @return string|\WP_Error
	 */
	protected function get_access_token() {

		if (null !== $this->access_token) {
			return $this->access_token;
		}

		// Check for cached token
		$cached = get_transient('wu_google_workspace_token');
		if ($cached) {
			$this->access_token = $cached;
			return $cached;
		}

		$json_path = defined('WU_GOOGLE_SERVICE_ACCOUNT_JSON') ? WU_GOOGLE_SERVICE_ACCOUNT_JSON : '';

		if (empty($json_path) || ! file_exists($json_path)) {
			return new \WP_Error('no_credentials', __('Google service account JSON file not found.', 'ultimate-multisite'));
		}

		$credentials = json_decode(file_get_contents($json_path), true); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if (! $credentials || ! isset($credentials['private_key']) || ! isset($credentials['client_email'])) {
			return new \WP_Error('invalid_credentials', __('Invalid service account JSON format.', 'ultimate-multisite'));
		}

		$admin_email = defined('WU_GOOGLE_ADMIN_EMAIL') ? WU_GOOGLE_ADMIN_EMAIL : '';

		if (empty($admin_email)) {
			return new \WP_Error('no_admin_email', __('Google admin email is not configured.', 'ultimate-multisite'));
		}

		// Create JWT for authentication
		$now    = time();
		$header = [
			'alg' => 'RS256',
			'typ' => 'JWT',
		];

		$payload = [
			'iss'   => $credentials['client_email'],
			'sub'   => $admin_email, // Impersonate admin for domain-wide delegation
			'scope' => 'https://www.googleapis.com/auth/admin.directory.user',
			'aud'   => self::TOKEN_URL,
			'iat'   => $now,
			'exp'   => $now + 3600,
		];

		$jwt = $this->create_jwt($header, $payload, $credentials['private_key']);

		if (is_wp_error($jwt)) {
			return $jwt;
		}

		// Exchange JWT for access token
		$response = wp_remote_post(
			self::TOKEN_URL,
			[
				'body' => [
					'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
					'assertion'  => $jwt,
				],
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (isset($body['error'])) {
			$error_msg = isset($body['error_description']) ? $body['error_description'] : $body['error'];
			return new \WP_Error('token_error', $error_msg);
		}

		if (! isset($body['access_token'])) {
			return new \WP_Error('no_token', __('Failed to obtain access token.', 'ultimate-multisite'));
		}

		$this->access_token = $body['access_token'];

		// Cache for slightly less than expiry
		$expires_in = isset($body['expires_in']) ? (int) $body['expires_in'] - 60 : 3540;
		set_transient('wu_google_workspace_token', $this->access_token, $expires_in);

		return $this->access_token;
	}

	/**
	 * Creates a JWT token.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $header     The JWT header.
	 * @param array  $payload    The JWT payload.
	 * @param string $private_key The private key.
	 * @return string|\WP_Error
	 */
	protected function create_jwt($header, $payload, $private_key) {

		$header_encoded  = $this->base64url_encode(wp_json_encode($header));
		$payload_encoded = $this->base64url_encode(wp_json_encode($payload));

		$signature_input = $header_encoded . '.' . $payload_encoded;

		$signature = '';
		$result    = openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);

		if (! $result) {
			return new \WP_Error('sign_error', __('Failed to sign JWT.', 'ultimate-multisite'));
		}

		return $signature_input . '.' . $this->base64url_encode($signature);
	}

	/**
	 * Base64URL encodes a string.
	 *
	 * @since 2.3.0
	 *
	 * @param string $data The data to encode.
	 * @return string
	 */
	protected function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Makes an API request to Google Admin SDK.
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

		$url = self::API_BASE_URL . '/' . ltrim($endpoint, '/');

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

		$result = json_decode($body);

		// 204 No Content is success for DELETE
		if (204 === $code) {
			return (object) ['success' => true];
		}

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
			'username'   => '',
			'domain'     => '',
			'password'   => '',
			'first_name' => '',
			'last_name'  => '',
			'quota_mb'   => 0,
		];

		$params = wp_parse_args($params, $defaults);

		if (empty($params['username']) || empty($params['domain']) || empty($params['password'])) {
			return new \WP_Error(
				'missing_params',
				__('Username, domain, and password are required.', 'ultimate-multisite')
			);
		}

		$email_address = $params['username'] . '@' . $params['domain'];

		// Default names if not provided
		$first_name = ! empty($params['first_name']) ? $params['first_name'] : $params['username'];
		$last_name  = ! empty($params['last_name']) ? $params['last_name'] : 'User';

		$result = $this->api_request(
			'users',
			[
				'primaryEmail' => $email_address,
				'name'         => [
					'givenName'  => $first_name,
					'familyName' => $last_name,
				],
				'password'     => $params['password'],
			],
			'POST'
		);

		if (is_wp_error($result)) {
			return $result;
		}

		$this->log(sprintf('Email account created: %s', $email_address));

		return [
			'email_address' => $email_address,
			'external_id'   => isset($result->id) ? $result->id : $email_address,
			'quota_mb'      => $params['quota_mb'],
		];
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
				'password' => $new_password,
			],
			'PUT'
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

		return [
			'email_address' => $email_address,
			'first_name'    => isset($result->name->givenName) ? $result->name->givenName : '',
			'last_name'     => isset($result->name->familyName) ? $result->name->familyName : '',
			'suspended'     => isset($result->suspended) ? $result->suspended : false,
			'quota_mb'      => 0, // Google Workspace uses license-based storage
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

		return 'https://mail.google.com/';
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
				'value'       => 'aspmx.l.google.com',
				'priority'    => 1,
				'description' => __('Primary Google mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'alt1.aspmx.l.google.com',
				'priority'    => 5,
				'description' => __('Backup Google mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'alt2.aspmx.l.google.com',
				'priority'    => 5,
				'description' => __('Backup Google mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'alt3.aspmx.l.google.com',
				'priority'    => 10,
				'description' => __('Backup Google mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'alt4.aspmx.l.google.com',
				'priority'    => 10,
				'description' => __('Backup Google mail server.', 'ultimate-multisite'),
			],
			[
				'type'        => 'TXT',
				'name'        => '@',
				'value'       => 'v=spf1 include:_spf.google.com ~all',
				'description' => __('SPF record to authorize Google to send email.', 'ultimate-multisite'),
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
			'server'   => 'imap.gmail.com',
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
			'server'   => 'smtp.gmail.com',
			'port'     => 587,
			'security' => 'STARTTLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Tests the connection with Google Workspace.
	 *
	 * @since 2.3.0
	 * @return bool|\WP_Error
	 */
	public function test_connection() {

		$customer_id = defined('WU_GOOGLE_CUSTOMER_ID') ? WU_GOOGLE_CUSTOMER_ID : '';

		if (empty($customer_id)) {
			return new \WP_Error('no_customer_id', __('Google customer ID is not configured.', 'ultimate-multisite'));
		}

		// Try to get customer info
		$result = $this->api_request('customers/' . urlencode($customer_id));

		if (is_wp_error($result)) {
			return $result;
		}

		return true;
	}
}
