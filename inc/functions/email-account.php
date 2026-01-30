<?php
/**
 * Email Account Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.3.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Models\Email_Account;

/**
 * Returns an email account by ID.
 *
 * @since 2.3.0
 *
 * @param int $email_account_id The id of the email account.
 * @return \WP_Ultimo\Models\Email_Account|false
 */
function wu_get_email_account($email_account_id) {

	return Email_Account::get_by_id($email_account_id);
}

/**
 * Queries email accounts.
 *
 * @since 2.3.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Email_Account[]|string[]|int
 */
function wu_get_email_accounts($query = []) {

	return Email_Account::query($query);
}

/**
 * Returns an email account by email address.
 *
 * @since 2.3.0
 *
 * @param string $email_address The email address.
 * @return \WP_Ultimo\Models\Email_Account|false
 */
function wu_get_email_account_by_email($email_address) {

	return Email_Account::get_by('email_address', strtolower($email_address));
}

/**
 * Gets email accounts for a customer.
 *
 * @since 2.3.0
 *
 * @param int $customer_id The customer ID.
 * @return \WP_Ultimo\Models\Email_Account[]
 */
function wu_get_email_accounts_by_customer($customer_id) {

	return wu_get_email_accounts(
		[
			'customer_id' => $customer_id,
		]
	);
}

/**
 * Gets email accounts for a site.
 *
 * @since 2.3.0
 *
 * @param int $site_id The site ID.
 * @return \WP_Ultimo\Models\Email_Account[]
 */
function wu_get_email_accounts_by_site($site_id) {

	return wu_get_email_accounts(
		[
			'site_id' => $site_id,
		]
	);
}

/**
 * Gets email accounts for a membership.
 *
 * @since 2.3.0
 *
 * @param int $membership_id The membership ID.
 * @return \WP_Ultimo\Models\Email_Account[]
 */
function wu_get_email_accounts_by_membership($membership_id) {

	return wu_get_email_accounts(
		[
			'membership_id' => $membership_id,
		]
	);
}

/**
 * Creates a new email account.
 *
 * @since 2.3.0
 *
 * @param array $email_account_data Email account attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Email_Account
 */
function wu_create_email_account($email_account_data) {

	$email_account_data = wp_parse_args(
		$email_account_data,
		[
			'customer_id'   => 0,
			'membership_id' => null,
			'site_id'       => null,
			'email_address' => '',
			'domain'        => '',
			'provider'      => '',
			'status'        => 'pending',
			'quota_mb'      => wu_get_setting('email_default_quota_mb', 1024),
			'purchase_type' => 'membership_included',
			'payment_id'    => null,
			'date_created'  => wu_get_current_time('mysql', true),
			'date_modified' => wu_get_current_time('mysql', true),
		]
	);

	// Auto-extract domain from email if not provided
	if (empty($email_account_data['domain']) && ! empty($email_account_data['email_address'])) {
		$parts = explode('@', $email_account_data['email_address']);
		if (2 === count($parts)) {
			$email_account_data['domain'] = $parts[1];
		}
	}

	$email_account = new Email_Account($email_account_data);

	$saved = $email_account->save();

	if (is_wp_error($saved)) {
		return $saved;
	}

	/**
	 * Enqueue the provisioning action.
	 */
	wu_enqueue_async_action(
		'wu_async_provision_email_account',
		['email_account_id' => $email_account->get_id()],
		'email_account'
	);

	/**
	 * Triggers when a new email account is created.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Ultimo\Models\Email_Account $email_account The email account object.
	 */
	do_action('wu_email_account_created', $email_account);

	return $email_account;
}

/**
 * Counts email accounts for a customer.
 *
 * @since 2.3.0
 *
 * @param int      $customer_id   The customer ID.
 * @param int|null $membership_id Optional membership ID.
 * @return int
 */
function wu_count_email_accounts($customer_id, $membership_id = null) {

	$args = [
		'customer_id' => $customer_id,
		'count'       => true,
	];

	if ($membership_id) {
		$args['membership_id'] = $membership_id;
	}

	return (int) wu_get_email_accounts($args);
}

/**
 * Checks if a customer can create more email accounts.
 *
 * @since 2.3.0
 *
 * @param int $customer_id   The customer ID.
 * @param int $membership_id The membership ID.
 * @return bool
 */
function wu_can_create_email_account($customer_id, $membership_id) {

	// Check if email accounts feature is enabled
	if ( ! wu_get_setting('enable_email_accounts', false)) {
		return false;
	}

	$membership = wu_get_membership($membership_id);

	if ( ! $membership) {
		return false;
	}

	// Check if membership has email accounts enabled
	if ($membership->has_limitations()) {
		$limitations = $membership->get_limitations();

		if ( ! isset($limitations->email_accounts) || ! $limitations->email_accounts->is_enabled()) {
			return false;
		}

		$limit = $limitations->email_accounts->get_limit();

		// 0 means unlimited
		if ($limit > 0) {
			$current_count = wu_count_email_accounts($customer_id, $membership_id);

			if ($current_count >= $limit) {
				return false;
			}
		}
	}

	/**
	 * Filter whether a customer can create an email account.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $can_create    Whether the customer can create an email account.
	 * @param int  $customer_id   The customer ID.
	 * @param int  $membership_id The membership ID.
	 */
	return apply_filters('wu_can_create_email_account', true, $customer_id, $membership_id);
}

/**
 * Gets the enabled email providers.
 *
 * @since 2.3.0
 *
 * @return array Array of enabled provider instances.
 */
function wu_get_enabled_email_providers() {

	$manager = \WP_Ultimo\Managers\Email_Account_Manager::get_instance();

	return $manager->get_enabled_providers();
}

/**
 * Gets a specific email provider by ID.
 *
 * @since 2.3.0
 *
 * @param string $provider_id The provider ID.
 * @return \WP_Ultimo\Integrations\Email_Providers\Base_Email_Provider|null
 */
function wu_get_email_provider($provider_id) {

	$manager = \WP_Ultimo\Managers\Email_Account_Manager::get_instance();

	return $manager->get_provider($provider_id);
}

/**
 * Gets the per-account email price.
 *
 * @since 2.3.0
 *
 * @return float
 */
function wu_get_email_account_price() {

	return (float) wu_get_setting('email_account_price', 5.00);
}

/**
 * Encrypts a password for temporary storage.
 *
 * @since 2.3.0
 *
 * @param string $password The password to encrypt.
 * @return string The encrypted password.
 */
function wu_encrypt_email_password($password) {

	if (function_exists('sodium_crypto_secretbox')) {
		$key   = substr(hash('sha256', wp_salt('auth'), true), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

		$encrypted = sodium_crypto_secretbox($password, $nonce, $key);

		return base64_encode($nonce . $encrypted); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	// Fallback to basic encoding (not secure, but better than plaintext)
	return base64_encode($password); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
}

/**
 * Decrypts a password from storage.
 *
 * @since 2.3.0
 *
 * @param string $encrypted The encrypted password.
 * @return string|false The decrypted password or false on failure.
 */
function wu_decrypt_email_password($encrypted) {

	if (function_exists('sodium_crypto_secretbox_open')) {
		$key     = substr(hash('sha256', wp_salt('auth'), true), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
		$decoded = base64_decode($encrypted, true); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if (false === $decoded) {
			return false;
		}

		$nonce      = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

		$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

		return false !== $decrypted ? $decrypted : false;
	}

	// Fallback for basic encoding
	return base64_decode($encrypted, true); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
}

/**
 * Stores a temporary password token for one-time display.
 *
 * @since 2.3.0
 *
 * @param int    $email_account_id The email account ID.
 * @param string $password         The password to store.
 * @return string The access token.
 */
function wu_store_email_password_token($email_account_id, $password) {

	$token     = wp_generate_password(32, false);
	$encrypted = wu_encrypt_email_password($password);

	set_transient(
		'wu_email_pwd_' . $token,
		[
			'email_account_id' => $email_account_id,
			'password'         => $encrypted,
		],
		600 // 10 minutes
	);

	return $token;
}

/**
 * Retrieves and deletes a temporary password token.
 *
 * @since 2.3.0
 *
 * @param string $token             The access token.
 * @param int    $email_account_id  The email account ID for verification.
 * @return string|false The password or false on failure.
 */
function wu_get_email_password_from_token($token, $email_account_id) {

	$data = get_transient('wu_email_pwd_' . $token);

	if ( ! $data || ! isset($data['email_account_id']) || (int) $data['email_account_id'] !== (int) $email_account_id) {
		return false;
	}

	// Delete the transient after retrieval (one-time use)
	delete_transient('wu_email_pwd_' . $token);

	return wu_decrypt_email_password($data['password']);
}

/**
 * Generates a strong random password for email accounts.
 *
 * @since 2.3.0
 *
 * @param int $length The password length.
 * @return string
 */
function wu_generate_email_password($length = 16) {

	return wp_generate_password($length, true, false);
}
