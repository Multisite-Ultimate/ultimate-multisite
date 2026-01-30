<?php
/**
 * Base class that new email provider integrations must extend.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Email_Providers
 * @since 2.3.0
 */

namespace WP_Ultimo\Integrations\Email_Providers;

use WP_Ultimo\Helpers\WP_Config;
use WP_Ultimo\Models\Email_Account;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new email provider integrations.
 */
abstract class Base_Email_Provider {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the id of the integration.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $id;

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $title;

	/**
	 * Link to the documentation for this provider.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $documentation_link = '';

	/**
	 * Affiliate URL for the provider.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	protected $affiliate_url = '';

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $constants = [];

	/**
	 * Constants that are optional on wp-config.php.
	 *
	 * @since 2.3.0
	 * @var array
	 */
	protected $optional_constants = [];

	/**
	 * Runs on singleton instantiation.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init(): void {

		// Register this provider with the manager
		add_filter('wu_email_manager_get_providers', [$this, 'self_register']);

		// Add to settings integration list
		add_action('init', [$this, 'add_to_integration_list']);

		// Only add hooks if enabled and setup
		if ($this->is_enabled() && $this->is_setup()) {
			$this->register_hooks();
		}
	}

	/**
	 * Let the class register itself on the manager.
	 *
	 * @since 2.3.0
	 *
	 * @param array $providers List of providers added so far.
	 * @return array
	 */
	final public function self_register($providers) {

		$providers[ $this->get_id() ] = static::class;

		return $providers;
	}

	/**
	 * Get the list of enabled email providers.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	protected function get_enabled_list() {

		return get_network_option(null, 'wu_email_providers_enabled', []);
	}

	/**
	 * Check if this provider is enabled.
	 *
	 * @since 2.3.0
	 * @return boolean
	 */
	final public function is_enabled() {

		$list = $this->get_enabled_list();

		return wu_get_isset($list, $this->get_id(), false);
	}

	/**
	 * Enables this provider.
	 *
	 * @since 2.3.0
	 * @return boolean
	 */
	public function enable() {

		$list = $this->get_enabled_list();

		$list[ $this->get_id() ] = true;

		return update_network_option(null, 'wu_email_providers_enabled', $list);
	}

	/**
	 * Disables this provider.
	 *
	 * @since 2.3.0
	 * @return boolean
	 */
	public function disable() {

		$list = $this->get_enabled_list();

		$list[ $this->get_id() ] = false;

		return update_network_option(null, 'wu_email_providers_enabled', $list);
	}

	/**
	 * Returns the provider id.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Returns the provider title.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;
	}

	/**
	 * Returns the affiliate URL.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_affiliate_url() {

		return $this->affiliate_url;
	}

	/**
	 * Returns the documentation link.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_documentation_link() {

		return $this->documentation_link;
	}

	/**
	 * Checks if the integration is correctly setup.
	 *
	 * @since 2.3.0
	 * @return boolean
	 */
	public function is_setup() {

		foreach ($this->constants as $constant) {
			$constants = is_array($constant) ? $constant : [$constant];

			$found = false;

			foreach ($constants as $const) {
				if (defined($const) && constant($const)) {
					$found = true;
					break;
				}
			}

			if ( ! $found) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a list of missing constants.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_missing_constants() {

		$missing = [];

		foreach ($this->constants as $constant) {
			$constants = is_array($constant) ? $constant : [$constant];

			$found = false;

			foreach ($constants as $const) {
				if (defined($const) && constant($const)) {
					$found = true;
					break;
				}
			}

			if ( ! $found) {
				$missing = array_merge($missing, $constants);
			}
		}

		return $missing;
	}

	/**
	 * Returns all constants.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_all_constants() {

		$constants = [];

		foreach ($this->constants as $constant) {
			$current   = is_array($constant) ? $constant : [$constant];
			$constants = array_merge($constants, $current);
		}

		return array_merge($constants, $this->optional_constants);
	}

	/**
	 * Get Fields for the integration configuration.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_fields() {

		return [];
	}

	/**
	 * Adds the constants with their values into wp-config.php.
	 *
	 * @since 2.3.0
	 *
	 * @param array $constant_values Key => Value of the constants.
	 * @return void
	 */
	public function setup_constants($constant_values): void {

		$values = shortcode_atts(array_flip($this->get_all_constants()), $constant_values);

		foreach ($values as $constant => $value) {
			WP_Config::get_instance()->inject_wp_config_constant($constant, $value);
		}
	}

	/**
	 * Generates a define string for manual insertion.
	 *
	 * @since 2.3.0
	 *
	 * @param array $constant_values Key => Value of the constants.
	 * @return string
	 */
	public function get_constants_string($constant_values) {

		$content = [
			sprintf('// Ultimate Multisite - Email Provider - %s', $this->get_title()),
		];

		$constant_values = shortcode_atts(array_flip($this->get_all_constants()), $constant_values);

		foreach ($constant_values as $constant => $value) {
			$content[] = sprintf("define( '%s', '%s' );", $constant, $value);
		}

		$content[] = sprintf('// Ultimate Multisite - Email Provider - %s - End', $this->get_title());

		return implode(PHP_EOL, $content);
	}

	/**
	 * Adds the provider to the settings list.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function add_to_integration_list(): void {

		if ( ! wu_get_setting('enable_email_accounts', false)) {
			return;
		}

		$slug = $this->get_id();

		// Build status indicator
		$html = '';

		if ($this->is_enabled()) {
			if ($this->is_setup()) {
				$html .= sprintf(
					'<div class="wu-self-center wu-text-green-800 wu-mr-4"><span class="dashicons-wu-check"></span> %s</div>',
					__('Activated', 'ultimate-multisite')
				);
			} else {
				$html .= sprintf(
					'<div class="wu-self-center wu-text-yellow-600 wu-mr-4"><span class="dashicons-wu-warning"></span> %s</div>',
					__('Not Configured', 'ultimate-multisite')
				);
			}
		}

		// Add Configuration button
		$url = wu_network_admin_url(
			'wp-ultimo-email-integration-wizard',
			[
				'integration' => $slug,
			]
		);

		$html .= sprintf(
			'<a href="%s" class="button-primary">%s</a>',
			esc_url($url),
			__('Configuration', 'ultimate-multisite')
		);

		wu_register_settings_field(
			'email-accounts',
			"email_provider_{$slug}",
			[
				'type'  => 'note',
				// translators: %s is the provider name (e.g. "cPanel", "Purelymail")
				'title' => sprintf(__('%s Integration', 'ultimate-multisite'), $this->get_title()),
				'desc'  => $html,
			]
		);
	}

	/**
	 * Returns explainer lines for the activation wizard.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_explainer_lines() {

		return [
			'will'     => [
				__('Allow customers to create email accounts with this provider', 'ultimate-multisite'),
				__('Automatically provision email accounts via API', 'ultimate-multisite'),
				__('Allow customers to manage their email account passwords', 'ultimate-multisite'),
			],
			'will_not' => [
				__('Automatically configure DNS records (customers must do this manually)', 'ultimate-multisite'),
				__('Provide email hosting (you need an account with the provider)', 'ultimate-multisite'),
			],
		];
	}

	/**
	 * Checks if the integration supports a given feature.
	 *
	 * @since 2.3.0
	 *
	 * @param string $feature The feature to check.
	 * @return bool
	 */
	public function supports($feature) {

		$supports = property_exists($this, 'supports') ? $this->supports : [];

		return in_array($feature, $supports, true);
	}

	/**
	 * Register hooks for this provider.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_hooks(): void {

		// Providers can override this to add specific hooks
	}

	/**
	 * Returns the description of this provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	abstract public function get_description();

	/**
	 * Returns the logo URL for the provider.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	abstract public function get_logo();

	/**
	 * Creates an email account with the provider.
	 *
	 * @since 2.3.0
	 *
	 * @param array $params The account parameters.
	 *                      - username: string The username portion of the email.
	 *                      - domain: string The domain for the email.
	 *                      - password: string The password for the account.
	 *                      - quota_mb: int Optional quota in MB.
	 *                      - display_name: string Optional display name.
	 * @return array|\WP_Error Array with account details on success, WP_Error on failure.
	 */
	abstract public function create_email_account(array $params);

	/**
	 * Deletes an email account from the provider.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address to delete.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	abstract public function delete_email_account($email_address);

	/**
	 * Changes the password for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address.
	 * @param string $new_password  The new password.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	abstract public function change_password($email_address, $new_password);

	/**
	 * Gets information about an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param string $email_address The email address.
	 * @return array|\WP_Error Account info on success, WP_Error on failure.
	 */
	abstract public function get_account_info($email_address);

	/**
	 * Gets the webmail URL for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param Email_Account $account The email account.
	 * @return string The webmail URL.
	 */
	abstract public function get_webmail_url(Email_Account $account);

	/**
	 * Gets the DNS instructions for a domain.
	 *
	 * @since 2.3.0
	 *
	 * @param string $domain The domain.
	 * @return array Array of DNS record instructions.
	 */
	abstract public function get_dns_instructions($domain);

	/**
	 * Gets the IMAP settings for an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param Email_Account $account The email account.
	 * @return array IMAP settings.
	 */
	public function get_imap_settings(Email_Account $account) {

		return [
			'server'   => '',
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
	 * @return array SMTP settings.
	 */
	public function get_smtp_settings(Email_Account $account) {

		return [
			'server'   => '',
			'port'     => 587,
			'security' => 'STARTTLS',
			'username' => $account->get_email_address(),
		];
	}

	/**
	 * Tests the connection with the provider API.
	 *
	 * @since 2.3.0
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function test_connection() {

		return true;
	}

	/**
	 * Logs a message for this provider.
	 *
	 * @since 2.3.0
	 *
	 * @param string $message The message to log.
	 * @param string $level   The log level.
	 * @return void
	 */
	protected function log($message, $level = 'info'): void {

		wu_log_add('email-provider-' . $this->get_id(), $message, $level);
	}

	/**
	 * Gets the signup instructions with affiliate link.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_signup_instructions() {

		$affiliate_url = $this->get_affiliate_url();

		if (empty($affiliate_url)) {
			return '';
		}

		return sprintf(
			/* translators: %1$s is the provider name, %2$s is the affiliate URL */
			__('Don\'t have a %1$s account yet? <a href="%2$s" target="_blank">Sign up here</a>.', 'ultimate-multisite'),
			$this->get_title(),
			esc_url($affiliate_url)
		);
	}
}
