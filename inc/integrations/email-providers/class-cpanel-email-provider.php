<?php
/**
 * CPanel Email Provider integration.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Email_Providers
 * @since 2.3.0
 */

namespace WP_Ultimo\Integrations\Email_Providers;

use WP_Ultimo\Models\Email_Account;
use WP_Ultimo\Integrations\Host_Providers\CPanel_API\CPanel_API;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * CPanel Email Provider.
 *
 * Uses the existing cPanel API to manage email accounts.
 */
class CPanel_Email_Provider extends Base_Email_Provider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id = 'cpanel';

	/**
	 * Provider title.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title = 'CPanel Email';

	/**
	 * Documentation link.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $documentation_link = 'https://github.com/superdav42/wp-multisite-waas/wiki/cPanel-Email-Integration';

	/**
	 * Required constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $constants = [
		'WU_CPANEL_USERNAME',
		'WU_CPANEL_PASSWORD',
		'WU_CPANEL_HOST',
	];

	/**
	 * Optional constants.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	protected $optional_constants = [
		'WU_CPANEL_PORT',
	];

	/**
	 * Holds the API object.
	 *
	 * @since 2.3.0
	 * @var CPanel_API|null
	 */
	protected $api = null;

	/**
	 * Returns the description of this provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_description() {

		return __('Create and manage email accounts directly through your cPanel hosting account.', 'ultimate-multisite');
	}

	/**
	 * Returns the logo URL for the provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cpanel.svg', 'img/hosts');
	}

	/**
	 * Get the configuration fields.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'WU_CPANEL_USERNAME' => [
				'title'       => __('cPanel Username', 'ultimate-multisite'),
				'placeholder' => __('e.g. username', 'ultimate-multisite'),
			],
			'WU_CPANEL_PASSWORD' => [
				'type'        => 'password',
				'title'       => __('cPanel Password', 'ultimate-multisite'),
				'placeholder' => __('password', 'ultimate-multisite'),
			],
			'WU_CPANEL_HOST'     => [
				'title'       => __('cPanel Host', 'ultimate-multisite'),
				'placeholder' => __('e.g. yourdomain.com', 'ultimate-multisite'),
			],
			'WU_CPANEL_PORT'     => [
				'title'       => __('cPanel Port', 'ultimate-multisite'),
				'placeholder' => __('Defaults to 2083', 'ultimate-multisite'),
				'value'       => 2083,
			],
		];
	}

	/**
	 * Load the cPanel API.
	 *
	 * @since 2.3.0
	 * @return CPanel_API
	 */
	protected function load_api() {

		if (null === $this->api) {
			$username = defined('WU_CPANEL_USERNAME') ? WU_CPANEL_USERNAME : '';
			$password = defined('WU_CPANEL_PASSWORD') ? WU_CPANEL_PASSWORD : '';
			$host     = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : '';
			$port     = defined('WU_CPANEL_PORT') && WU_CPANEL_PORT ? WU_CPANEL_PORT : 2083;

			$this->api = new CPanel_API($username, $password, preg_replace('#^https?://#', '', (string) $host), $port);
		}

		return $this->api;
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
			'quota_mb' => 0, // 0 = unlimited
		];

		$params = wp_parse_args($params, $defaults);

		if (empty($params['username']) || empty($params['domain']) || empty($params['password'])) {
			return new \WP_Error(
				'missing_params',
				__('Username, domain, and password are required.', 'ultimate-multisite')
			);
		}

		try {
			// Use UAPI for newer cPanel versions
			$result = $this->load_api()->uapi(
				'Email',
				'add_pop',
				[
					'email'    => $params['username'],
					'domain'   => $params['domain'],
					'password' => $params['password'],
					'quota'    => $params['quota_mb'] > 0 ? $params['quota_mb'] : 0,
				]
			);

			if (isset($result->status) && 1 === $result->status) {
				$this->log(sprintf('Email account created: %s@%s', $params['username'], $params['domain']));

				return [
					'email_address' => $params['username'] . '@' . $params['domain'],
					'external_id'   => $params['username'] . '@' . $params['domain'],
					'quota_mb'      => $params['quota_mb'],
				];
			}

			// Check for errors
			$error_message = isset($result->errors) && is_array($result->errors)
				? implode(', ', $result->errors)
				: __('Failed to create email account.', 'ultimate-multisite');

			$this->log('Error creating email: ' . $error_message, 'error');

			return new \WP_Error('cpanel_error', $error_message);
		} catch (\Exception $e) {
			$this->log('Exception creating email: ' . $e->getMessage(), 'error');

			return new \WP_Error('cpanel_exception', $e->getMessage());
		}
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

		$parts = explode('@', $email_address);

		if (count($parts) !== 2) {
			return new \WP_Error('invalid_email', __('Invalid email address format.', 'ultimate-multisite'));
		}

		[$username, $domain] = $parts;

		try {
			$result = $this->load_api()->uapi(
				'Email',
				'delete_pop',
				[
					'email'  => $username,
					'domain' => $domain,
				]
			);

			if (isset($result->status) && 1 === $result->status) {
				$this->log(sprintf('Email account deleted: %s', $email_address));

				return true;
			}

			$error_message = isset($result->errors) && is_array($result->errors)
				? implode(', ', $result->errors)
				: __('Failed to delete email account.', 'ultimate-multisite');

			return new \WP_Error('cpanel_error', $error_message);
		} catch (\Exception $e) {
			return new \WP_Error('cpanel_exception', $e->getMessage());
		}
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

		$parts = explode('@', $email_address);

		if (count($parts) !== 2) {
			return new \WP_Error('invalid_email', __('Invalid email address format.', 'ultimate-multisite'));
		}

		[$username, $domain] = $parts;

		try {
			$result = $this->load_api()->uapi(
				'Email',
				'passwd_pop',
				[
					'email'    => $username,
					'domain'   => $domain,
					'password' => $new_password,
				]
			);

			if (isset($result->status) && 1 === $result->status) {
				$this->log(sprintf('Password changed for: %s', $email_address));

				return true;
			}

			$error_message = isset($result->errors) && is_array($result->errors)
				? implode(', ', $result->errors)
				: __('Failed to change password.', 'ultimate-multisite');

			return new \WP_Error('cpanel_error', $error_message);
		} catch (\Exception $e) {
			return new \WP_Error('cpanel_exception', $e->getMessage());
		}
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

		$parts = explode('@', $email_address);

		if (count($parts) !== 2) {
			return new \WP_Error('invalid_email', __('Invalid email address format.', 'ultimate-multisite'));
		}

		[$username, $domain] = $parts;

		try {
			$result = $this->load_api()->uapi(
				'Email',
				'list_pops_with_disk',
				[
					'domain' => $domain,
				]
			);

			if (isset($result->status) && 1 === $result->status && isset($result->data)) {
				foreach ($result->data as $account) {
					if (isset($account->email) && $email_address === $account->email) {
						return [
							'email_address' => $account->email,
							'quota_mb'      => isset($account->_diskquota) ? (int) $account->_diskquota : 0,
							'disk_used_mb'  => isset($account->_diskused) ? (float) $account->_diskused : 0,
							'disk_used_pct' => isset($account->_diskusedpercent) ? (float) $account->_diskusedpercent : 0,
						];
					}
				}

				return new \WP_Error('not_found', __('Email account not found.', 'ultimate-multisite'));
			}

			return new \WP_Error('cpanel_error', __('Failed to get account info.', 'ultimate-multisite'));
		} catch (\Exception $e) {
			return new \WP_Error('cpanel_exception', $e->getMessage());
		}
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

		$host = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : '';
		$host = preg_replace('#^https?://#', '', $host);

		// cPanel webmail runs on port 2096
		return sprintf('https://%s:2096/', $host);
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

		$host = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : 'your-server.com';
		$host = preg_replace('#^https?://#', '', $host);

		return [
			[
				'type'        => 'MX',
				'name'        => '@',
				'value'       => 'mail.' . $domain,
				'priority'    => 10,
				'description' => __('Mail exchanger record for receiving email.', 'ultimate-multisite'),
			],
			[
				'type'        => 'A',
				'name'        => 'mail',
				'value'       => __('[Your Server IP]', 'ultimate-multisite'),
				'description' => __('Points the mail subdomain to your server. Replace with your actual server IP.', 'ultimate-multisite'),
			],
			[
				'type'        => 'TXT',
				'name'        => '@',
				'value'       => 'v=spf1 +a +mx ~all',
				'description' => __('SPF record to help prevent email spoofing.', 'ultimate-multisite'),
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

		$host = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : '';
		$host = preg_replace('#^https?://#', '', $host);

		return [
			'server'   => 'mail.' . $account->get_domain(),
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
			'server'   => 'mail.' . $account->get_domain(),
			'port'     => 587,
			'security' => 'STARTTLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Tests the connection with cPanel.
	 *
	 * @since 2.3.0
	 * @return bool|\WP_Error
	 */
	public function test_connection() {

		try {
			// Try to list email accounts - this will test the connection
			$result = $this->load_api()->uapi('Email', 'list_pops', []);

			if (isset($result->status) && 1 === $result->status) {
				return true;
			}

			return new \WP_Error('connection_failed', __('Failed to connect to cPanel.', 'ultimate-multisite'));
		} catch (\Exception $e) {
			return new \WP_Error('connection_exception', $e->getMessage());
		}
	}
}
