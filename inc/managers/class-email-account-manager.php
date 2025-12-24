<?php
/**
 * Email Account Manager
 *
 * Handles processes related to email accounts,
 * including provisioning, deletion, and lifecycle management.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Email_Account_Manager
 * @since 2.3.0
 */

namespace WP_Ultimo\Managers;

use Psr\Log\LogLevel;
use WP_Ultimo\Models\Email_Account;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to email accounts.
 *
 * @since 2.3.0
 */
class Email_Account_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api;
	use \WP_Ultimo\Apis\WP_CLI;
	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $slug = 'email_account';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $model_class = \WP_Ultimo\Models\Email_Account::class;

	/**
	 * Holds a list of registered email providers.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $providers = [];

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init(): void {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		// Load providers
		add_action('plugins_loaded', [$this, 'load_providers'], 20);

		// Settings
		add_action('wu_settings_email-accounts', [$this, 'add_email_account_settings']);

		// Async provisioning
		add_action('wu_async_provision_email_account', [$this, 'async_provision_email_account']);
		add_action('wu_async_delete_email_account', [$this, 'async_delete_email_account']);

		// Status transitions
		add_action('wu_transition_email_account_status', [$this, 'handle_status_transition'], 10, 3);

		// Clean up when membership is deleted
		add_action('wu_membership_post_delete', [$this, 'handle_membership_deleted']);

		// Clean up when customer is deleted
		add_action('wu_customer_post_delete', [$this, 'handle_customer_deleted']);
	}

	/**
	 * Load email providers.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function load_providers(): void {

		// Only load if email accounts are enabled
		if ( ! wu_get_setting('enable_email_accounts', false)) {
			return;
		}

		// Load built-in providers
		\WP_Ultimo\Integrations\Email_Providers\CPanel_Email_Provider::get_instance();
		\WP_Ultimo\Integrations\Email_Providers\Purelymail_Provider::get_instance();
		\WP_Ultimo\Integrations\Email_Providers\Google_Workspace_Provider::get_instance();
		\WP_Ultimo\Integrations\Email_Providers\Microsoft365_Provider::get_instance();

		// Allow additional providers to be loaded
		do_action('wu_email_providers_load');
	}

	/**
	 * Returns the list of registered email providers.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_providers() {

		return apply_filters('wu_email_manager_get_providers', $this->providers, $this);
	}

	/**
	 * Returns the list of enabled email providers.
	 *
	 * @since 2.3.0
	 * @return array Array of provider instances.
	 */
	public function get_enabled_providers() {

		$providers = $this->get_providers();
		$enabled   = [];

		foreach ($providers as $id => $class_name) {
			$instance = $class_name::get_instance();

			if ($instance->is_enabled() && $instance->is_setup()) {
				$enabled[ $id ] = $instance;
			}
		}

		return $enabled;
	}

	/**
	 * Get a specific provider instance.
	 *
	 * @since 2.3.0
	 *
	 * @param string $id The provider ID.
	 * @return \WP_Ultimo\Integrations\Email_Providers\Base_Email_Provider|null
	 */
	public function get_provider($id) {

		$providers = $this->get_providers();

		if (isset($providers[ $id ])) {
			$class_name = $providers[ $id ];

			return $class_name::get_instance();
		}

		return null;
	}

	/**
	 * Add email account settings.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function add_email_account_settings(): void {

		wu_register_settings_field(
			'email-accounts',
			'email_accounts_header',
			[
				'title' => __('Email Accounts Settings', 'ultimate-multisite'),
				'desc'  => __('Configure email account management for your network.', 'ultimate-multisite'),
				'type'  => 'header',
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'enable_email_accounts',
			[
				'title'   => __('Enable Email Accounts', 'ultimate-multisite'),
				'desc'    => __('Allow customers to create and manage email accounts.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => false,
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'email_default_quota_mb',
			[
				'title'   => __('Default Mailbox Quota (MB)', 'ultimate-multisite'),
				'desc'    => __('Default storage quota for new email accounts. Set to 0 for unlimited.', 'ultimate-multisite'),
				'type'    => 'number',
				'default' => 1024,
				'min'     => 0,
				'require' => [
					'enable_email_accounts' => true,
				],
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'email_per_account_header',
			[
				'title'   => __('Per-Account Purchases', 'ultimate-multisite'),
				'desc'    => __('Allow customers to purchase additional email accounts beyond their membership quota.', 'ultimate-multisite'),
				'type'    => 'header',
				'require' => [
					'enable_email_accounts' => true,
				],
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'enable_email_per_account_purchase',
			[
				'title'   => __('Enable Per-Account Purchases', 'ultimate-multisite'),
				'desc'    => __('Allow customers to buy additional email accounts separately.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => false,
				'require' => [
					'enable_email_accounts' => true,
				],
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'email_account_price',
			[
				'title'   => __('Price Per Email Account', 'ultimate-multisite'),
				'desc'    => __('One-time or monthly price for purchasing an additional email account.', 'ultimate-multisite'),
				'type'    => 'number',
				'default' => 5.00,
				'min'     => 0,
				'step'    => 0.01,
				'require' => [
					'enable_email_accounts'             => true,
					'enable_email_per_account_purchase' => true,
				],
			]
		);

		wu_register_settings_field(
			'email-accounts',
			'email_providers_header',
			[
				'title'   => __('Email Providers', 'ultimate-multisite'),
				'desc'    => __('Configure which email providers are available.', 'ultimate-multisite'),
				'type'    => 'header',
				'require' => [
					'enable_email_accounts' => true,
				],
			]
		);

		// Provider-specific settings are added by each provider via add_to_integration_list()
	}

	/**
	 * Async provision an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param int $email_account_id The email account ID.
	 * @return void
	 */
	public function async_provision_email_account($email_account_id): void {

		$email_account = wu_get_email_account($email_account_id);

		if ( ! $email_account) {
			wu_log_add('email-accounts', sprintf('Provisioning failed: Account %d not found.', $email_account_id), LogLevel::ERROR);
			return;
		}

		// Get the provider
		$provider = $email_account->get_provider_instance();

		if ( ! $provider) {
			$email_account->set_status('failed');
			$email_account->save();
			wu_log_add('email-accounts', sprintf('Provisioning failed: Provider %s not found.', $email_account->get_provider()), LogLevel::ERROR);
			return;
		}

		// Update status to provisioning
		$email_account->set_status('provisioning');
		$email_account->save();

		// Get the password from transient if available
		$password_token = get_transient('wu_email_provision_pwd_' . $email_account_id);
		$password       = '';

		if ($password_token) {
			$password = wu_get_email_password_from_token($password_token, $email_account_id);
			delete_transient('wu_email_provision_pwd_' . $email_account_id);
		}

		// If no password in transient, generate a new one
		if (empty($password)) {
			$password = wu_generate_email_password();
		}

		// Attempt to create the account
		$result = $provider->create_email_account(
			[
				'username' => $email_account->get_username(),
				'domain'   => $email_account->get_domain(),
				'password' => $password,
				'quota_mb' => $email_account->get_quota_mb(),
			]
		);

		if (is_wp_error($result)) {
			$email_account->set_status('failed');
			$email_account->save();

			wu_log_add(
				'email-accounts',
				sprintf('Provisioning failed for %s: %s', $email_account->get_email_address(), $result->get_error_message()),
				LogLevel::ERROR
			);

			/**
			 * Fires when email account provisioning fails.
			 *
			 * @since 2.3.0
			 *
			 * @param Email_Account $email_account The email account.
			 * @param \WP_Error     $result        The error.
			 */
			do_action('wu_email_account_provisioning_failed', $email_account, $result);

			return;
		}

		// Update the account with external ID if provided
		if (isset($result['external_id'])) {
			$email_account->set_external_id($result['external_id']);
		}

		// Store encrypted password temporarily for one-time display
		$password_display_token = wu_store_email_password_token($email_account_id, $password);
		$email_account->update_meta('password_display_token', $password_display_token);

		$email_account->set_status('active');
		$email_account->save();

		wu_log_add(
			'email-accounts',
			sprintf('Email account provisioned successfully: %s', $email_account->get_email_address())
		);

		/**
		 * Fires when email account is successfully provisioned.
		 *
		 * @since 2.3.0
		 *
		 * @param Email_Account $email_account The email account.
		 * @param string        $password      The password (for sending welcome email).
		 */
		do_action('wu_email_account_provisioned', $email_account, $password);
	}

	/**
	 * Async delete an email account from provider.
	 *
	 * @since 2.3.0
	 *
	 * @param array $data Data containing email_address and provider.
	 * @return void
	 */
	public function async_delete_email_account($data): void {

		if (empty($data['email_address']) || empty($data['provider'])) {
			return;
		}

		$provider = $this->get_provider($data['provider']);

		if ( ! $provider) {
			wu_log_add('email-accounts', sprintf('Delete failed: Provider %s not found.', $data['provider']), LogLevel::ERROR);
			return;
		}

		$result = $provider->delete_email_account($data['email_address']);

		if (is_wp_error($result)) {
			wu_log_add(
				'email-accounts',
				sprintf('Failed to delete %s from provider: %s', $data['email_address'], $result->get_error_message()),
				LogLevel::ERROR
			);
			return;
		}

		wu_log_add('email-accounts', sprintf('Email account deleted from provider: %s', $data['email_address']));
	}

	/**
	 * Handle status transitions.
	 *
	 * @since 2.3.0
	 *
	 * @param string        $old_status    The old status.
	 * @param string        $new_status    The new status.
	 * @param Email_Account $email_account The email account.
	 * @return void
	 */
	public function handle_status_transition($old_status, $new_status, $email_account): void {

		if ('suspended' === $new_status && 'active' === $old_status) {
			/**
			 * Fires when an email account is suspended.
			 *
			 * @since 2.3.0
			 *
			 * @param Email_Account $email_account The email account.
			 */
			do_action('wu_email_account_suspended', $email_account);
		}

		if ('active' === $new_status && 'suspended' === $old_status) {
			/**
			 * Fires when an email account is reactivated.
			 *
			 * @since 2.3.0
			 *
			 * @param Email_Account $email_account The email account.
			 */
			do_action('wu_email_account_reactivated', $email_account);
		}
	}

	/**
	 * Handle membership deletion - delete associated email accounts.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The deleted membership.
	 * @return void
	 */
	public function handle_membership_deleted($membership): void {

		$email_accounts = wu_get_email_accounts(
			[
				'membership_id' => $membership->get_id(),
			]
		);

		foreach ($email_accounts as $email_account) {
			// Queue deletion from provider
			wu_enqueue_async_action(
				'wu_async_delete_email_account',
				[
					'email_address' => $email_account->get_email_address(),
					'provider'      => $email_account->get_provider(),
				],
				'email_account'
			);

			// Delete from database
			$email_account->delete();
		}
	}

	/**
	 * Handle customer deletion - delete associated email accounts.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Ultimo\Models\Customer $customer The deleted customer.
	 * @return void
	 */
	public function handle_customer_deleted($customer): void {

		$email_accounts = wu_get_email_accounts(
			[
				'customer_id' => $customer->get_id(),
			]
		);

		foreach ($email_accounts as $email_account) {
			// Queue deletion from provider
			wu_enqueue_async_action(
				'wu_async_delete_email_account',
				[
					'email_address' => $email_account->get_email_address(),
					'provider'      => $email_account->get_provider(),
				],
				'email_account'
			);

			// Delete from database
			$email_account->delete();
		}
	}

	/**
	 * Create an email account with validation.
	 *
	 * @since 2.3.0
	 *
	 * @param array $params Account parameters.
	 * @return Email_Account|\WP_Error
	 */
	public function create_account($params) {

		$defaults = [
			'customer_id'   => 0,
			'membership_id' => null,
			'site_id'       => null,
			'email_address' => '',
			'provider'      => '',
			'password'      => '',
			'quota_mb'      => wu_get_setting('email_default_quota_mb', 1024),
			'purchase_type' => 'membership_included',
		];

		$params = wp_parse_args($params, $defaults);

		// Validate customer
		$customer = wu_get_customer($params['customer_id']);

		if ( ! $customer) {
			return new \WP_Error('invalid_customer', __('Invalid customer.', 'ultimate-multisite'));
		}

		// Validate provider
		$provider = $this->get_provider($params['provider']);

		if ( ! $provider || ! $provider->is_enabled() || ! $provider->is_setup()) {
			return new \WP_Error('invalid_provider', __('Invalid or unconfigured email provider.', 'ultimate-multisite'));
		}

		// Check quota if membership included
		if ('membership_included' === $params['purchase_type'] && $params['membership_id']) {
			if ( ! wu_can_create_email_account($params['customer_id'], $params['membership_id'])) {
				return new \WP_Error('quota_exceeded', __('Email account quota exceeded.', 'ultimate-multisite'));
			}
		}

		// Validate email address
		if (empty($params['email_address']) || ! is_email($params['email_address'])) {
			return new \WP_Error('invalid_email', __('Invalid email address.', 'ultimate-multisite'));
		}

		// Check if email already exists
		$existing = wu_get_email_account_by_email($params['email_address']);

		if ($existing) {
			return new \WP_Error('email_exists', __('This email address already exists.', 'ultimate-multisite'));
		}

		// Generate password if not provided
		$password = ! empty($params['password']) ? $params['password'] : wu_generate_email_password();

		// Create the account record
		$email_account = wu_create_email_account(
			[
				'customer_id'   => $params['customer_id'],
				'membership_id' => $params['membership_id'],
				'site_id'       => $params['site_id'],
				'email_address' => $params['email_address'],
				'provider'      => $params['provider'],
				'quota_mb'      => $params['quota_mb'],
				'purchase_type' => $params['purchase_type'],
				'payment_id'    => $params['payment_id'] ?? null,
				'status'        => 'pending',
			]
		);

		if (is_wp_error($email_account)) {
			return $email_account;
		}

		// Store password token for provisioning
		$token = wu_store_email_password_token($email_account->get_id(), $password);
		set_transient('wu_email_provision_pwd_' . $email_account->get_id(), $token, 3600);

		return $email_account;
	}

	/**
	 * Get DNS instructions for a provider and domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $provider_id The provider ID.
	 * @param string $domain      The domain.
	 * @return array|\WP_Error
	 */
	public function get_dns_instructions($provider_id, $domain) {

		$provider = $this->get_provider($provider_id);

		if ( ! $provider) {
			return new \WP_Error('invalid_provider', __('Invalid provider.', 'ultimate-multisite'));
		}

		return $provider->get_dns_instructions($domain);
	}
}
