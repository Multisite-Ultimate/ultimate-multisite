<?php
/**
 * The Email Account model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.3.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Database\Email_Accounts\Email_Account_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email Account model class. Implements the Base Model.
 *
 * @since 2.3.0
 */
class Email_Account extends Base_Model {

	/**
	 * Customer ID associated with this email account.
	 *
	 * @since 2.3.0
	 * @var int
	 */
	protected $customer_id;

	/**
	 * Membership ID associated with this email account (if membership included).
	 *
	 * @since 2.3.0
	 * @var int|null
	 */
	protected $membership_id;

	/**
	 * Site ID associated with this email account.
	 *
	 * @since 2.3.0
	 * @var int|null
	 */
	protected $site_id;

	/**
	 * The full email address.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $email_address = '';

	/**
	 * The domain portion of the email.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $domain = '';

	/**
	 * The email provider identifier.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $provider = '';

	/**
	 * The status of the email account.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $status = 'pending';

	/**
	 * Quota in megabytes.
	 *
	 * @since 2.3.0
	 * @var int
	 */
	protected $quota_mb = 0;

	/**
	 * External ID from the provider.
	 *
	 * @since 2.3.0
	 * @var string|null
	 */
	protected $external_id;

	/**
	 * Encrypted password hash (for temporary storage).
	 *
	 * @since 2.3.0
	 * @var string|null
	 */
	protected $password_hash;

	/**
	 * Purchase type: membership_included or per_account.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $purchase_type = 'membership_included';

	/**
	 * Payment ID (for per_account purchases).
	 *
	 * @since 2.3.0
	 * @var int|null
	 */
	protected $payment_id;

	/**
	 * Date when this was created.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Date when this was last modified.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $date_modified;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Email_Accounts\Email_Account_Query::class;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * @since 2.3.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return [
			'customer_id'   => 'required|integer',
			'email_address' => "required|email|unique:\WP_Ultimo\Models\Email_Account,email_address,{$id}",
			'domain'        => 'required',
			'provider'      => 'required',
			'status'        => 'required|in:pending,provisioning,active,suspended,failed|default:pending',
			'purchase_type' => 'in:membership_included,per_account|default:membership_included',
			'quota_mb'      => 'integer|default:0',
		];
	}

	/**
	 * Get the customer ID.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public function get_customer_id(): int {

		return (int) $this->customer_id;
	}

	/**
	 * Set the customer ID.
	 *
	 * @since 2.3.0
	 * @param int $customer_id The customer ID.
	 * @return void
	 */
	public function set_customer_id($customer_id): void {

		$this->customer_id = (int) $customer_id;
	}

	/**
	 * Get the customer object.
	 *
	 * @since 2.3.0
	 * @return Customer|null
	 */
	public function get_customer() {

		return wu_get_customer($this->get_customer_id());
	}

	/**
	 * Get the membership ID.
	 *
	 * @since 2.3.0
	 * @return int|null
	 */
	public function get_membership_id() {

		return $this->membership_id ? (int) $this->membership_id : null;
	}

	/**
	 * Set the membership ID.
	 *
	 * @since 2.3.0
	 * @param int|null $membership_id The membership ID.
	 * @return void
	 */
	public function set_membership_id($membership_id): void {

		$this->membership_id = $membership_id ? (int) $membership_id : null;
	}

	/**
	 * Get the membership object.
	 *
	 * @since 2.3.0
	 * @return Membership|null
	 */
	public function get_membership() {

		$membership_id = $this->get_membership_id();

		return $membership_id ? wu_get_membership($membership_id) : null;
	}

	/**
	 * Get the site ID.
	 *
	 * @since 2.3.0
	 * @return int|null
	 */
	public function get_site_id() {

		return $this->site_id ? (int) $this->site_id : null;
	}

	/**
	 * Set the site ID.
	 *
	 * @since 2.3.0
	 * @param int|null $site_id The site ID.
	 * @return void
	 */
	public function set_site_id($site_id): void {

		$this->site_id = $site_id ? (int) $site_id : null;
	}

	/**
	 * Get the site object.
	 *
	 * @since 2.3.0
	 * @return Site|null
	 */
	public function get_site() {

		$site_id = $this->get_site_id();

		return $site_id ? wu_get_site($site_id) : null;
	}

	/**
	 * Get the full email address.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_email_address(): string {

		return $this->email_address;
	}

	/**
	 * Set the email address.
	 *
	 * @since 2.3.0
	 * @param string $email_address The email address.
	 * @return void
	 */
	public function set_email_address($email_address): void {

		$this->email_address = strtolower(sanitize_email($email_address));

		// Auto-extract domain from email address
		$parts = explode('@', $this->email_address);
		if (count($parts) === 2) {
			$this->domain = $parts[1];
		}
	}

	/**
	 * Get the username portion of the email.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_username(): string {

		$parts = explode('@', $this->get_email_address());

		return $parts[0] ?? '';
	}

	/**
	 * Get the domain.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_domain(): string {

		return $this->domain;
	}

	/**
	 * Set the domain.
	 *
	 * @since 2.3.0
	 * @param string $domain The domain.
	 * @return void
	 */
	public function set_domain($domain): void {

		$this->domain = strtolower($domain);
	}

	/**
	 * Get the provider identifier.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_provider(): string {

		return $this->provider;
	}

	/**
	 * Set the provider.
	 *
	 * @since 2.3.0
	 * @param string $provider The provider identifier.
	 * @return void
	 */
	public function set_provider($provider): void {

		$this->provider = $provider;
	}

	/**
	 * Get the provider instance.
	 *
	 * @since 2.3.0
	 * @return \WP_Ultimo\Integrations\Email_Providers\Base_Email_Provider|null
	 */
	public function get_provider_instance() {

		$manager = \WP_Ultimo\Managers\Email_Account_Manager::get_instance();

		return $manager->get_provider($this->get_provider());
	}

	/**
	 * Get the status.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_status(): string {

		return $this->status;
	}

	/**
	 * Set the status.
	 *
	 * @since 2.3.0
	 * @param string $status The status.
	 * @return void
	 */
	public function set_status($status): void {

		$this->status = $status;
	}

	/**
	 * Check if the email account is active.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_active(): bool {

		return $this->get_status() === 'active';
	}

	/**
	 * Returns the status label.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_status_label(): string {

		$status = new Email_Account_Status($this->get_status());

		return $status->get_label();
	}

	/**
	 * Gets the CSS classes for the status.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_status_class(): string {

		$status = new Email_Account_Status($this->get_status());

		return $status->get_classes();
	}

	/**
	 * Get the quota in megabytes.
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public function get_quota_mb(): int {

		return (int) $this->quota_mb;
	}

	/**
	 * Set the quota in megabytes.
	 *
	 * @since 2.3.0
	 * @param int $quota_mb The quota in MB.
	 * @return void
	 */
	public function set_quota_mb($quota_mb): void {

		$this->quota_mb = (int) $quota_mb;
	}

	/**
	 * Get the external ID from the provider.
	 *
	 * @since 2.3.0
	 * @return string|null
	 */
	public function get_external_id() {

		return $this->external_id;
	}

	/**
	 * Set the external ID.
	 *
	 * @since 2.3.0
	 * @param string|null $external_id The external ID.
	 * @return void
	 */
	public function set_external_id($external_id): void {

		$this->external_id = $external_id;
	}

	/**
	 * Get the encrypted password hash.
	 *
	 * @since 2.3.0
	 * @return string|null
	 */
	public function get_password_hash() {

		return $this->password_hash;
	}

	/**
	 * Set the encrypted password hash.
	 *
	 * @since 2.3.0
	 * @param string|null $password_hash The encrypted password hash.
	 * @return void
	 */
	public function set_password_hash($password_hash): void {

		$this->password_hash = $password_hash;
	}

	/**
	 * Get the purchase type.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_purchase_type(): string {

		return $this->purchase_type;
	}

	/**
	 * Set the purchase type.
	 *
	 * @since 2.3.0
	 * @param string $purchase_type The purchase type.
	 * @return void
	 */
	public function set_purchase_type($purchase_type): void {

		$this->purchase_type = $purchase_type;
	}

	/**
	 * Check if this is a per-account purchase.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_per_account_purchase(): bool {

		return $this->get_purchase_type() === 'per_account';
	}

	/**
	 * Get the payment ID.
	 *
	 * @since 2.3.0
	 * @return int|null
	 */
	public function get_payment_id() {

		return $this->payment_id ? (int) $this->payment_id : null;
	}

	/**
	 * Set the payment ID.
	 *
	 * @since 2.3.0
	 * @param int|null $payment_id The payment ID.
	 * @return void
	 */
	public function set_payment_id($payment_id): void {

		$this->payment_id = $payment_id ? (int) $payment_id : null;
	}

	/**
	 * Get the payment object.
	 *
	 * @since 2.3.0
	 * @return Payment|null
	 */
	public function get_payment() {

		$payment_id = $this->get_payment_id();

		return $payment_id ? wu_get_payment($payment_id) : null;
	}

	/**
	 * Get date when this was created.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;
	}

	/**
	 * Set date when this was created.
	 *
	 * @since 2.3.0
	 * @param string $date_created Date when the email account was created.
	 * @return void
	 */
	public function set_date_created($date_created): void {

		$this->date_created = $date_created;
	}

	/**
	 * Get the webmail URL for this email account.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_webmail_url(): string {

		$provider = $this->get_provider_instance();

		if ($provider) {
			return $provider->get_webmail_url($this);
		}

		return '';
	}

	/**
	 * Get the IMAP settings for this email account.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_imap_settings(): array {

		$provider = $this->get_provider_instance();

		if ($provider) {
			return $provider->get_imap_settings($this);
		}

		return [];
	}

	/**
	 * Get the SMTP settings for this email account.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_smtp_settings(): array {

		$provider = $this->get_provider_instance();

		if ($provider) {
			return $provider->get_smtp_settings($this);
		}

		return [];
	}

	/**
	 * Get all email accounts by customer.
	 *
	 * @since 2.3.0
	 * @param int $customer_id The customer ID.
	 * @return Email_Account[]
	 */
	public static function get_by_customer($customer_id) {

		return self::get_items(
			[
				'customer_id' => $customer_id,
			]
		);
	}

	/**
	 * Get all email accounts by membership.
	 *
	 * @since 2.3.0
	 * @param int $membership_id The membership ID.
	 * @return Email_Account[]
	 */
	public static function get_by_membership($membership_id) {

		return self::get_items(
			[
				'membership_id' => $membership_id,
			]
		);
	}

	/**
	 * Get all email accounts by site.
	 *
	 * @since 2.3.0
	 * @param int $site_id The site ID.
	 * @return Email_Account[]
	 */
	public static function get_by_site($site_id) {

		return self::get_items(
			[
				'site_id' => $site_id,
			]
		);
	}

	/**
	 * Get an email account by email address.
	 *
	 * @since 2.3.0
	 * @param string $email_address The email address.
	 * @return Email_Account|null
	 */
	public static function get_by_email_address($email_address) {

		return self::get_by('email_address', strtolower($email_address));
	}

	/**
	 * Count email accounts for a customer.
	 *
	 * @since 2.3.0
	 * @param int      $customer_id   The customer ID.
	 * @param int|null $membership_id Optional membership ID filter.
	 * @return int
	 */
	public static function count_by_customer($customer_id, $membership_id = null): int {

		$args = [
			'customer_id' => $customer_id,
			'count'       => true,
		];

		if ($membership_id) {
			$args['membership_id'] = $membership_id;
		}

		return (int) self::get_items($args);
	}
}
