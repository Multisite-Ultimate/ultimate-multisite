<?php
/**
 * The Customer model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;
use WP_Ultimo\Models\Membership;
use WP_Ultimo\Models\Site;
use WP_Ultimo\Models\Payment;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Customer extends Base_Model {

	use Traits\Billable;
	use Traits\Notable;

	/**
	 * User ID of the associated user.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $user_id;

	/**
	 * The type of customer.
	 *
	 * Almost a 100% of the time this will be 'customer'
	 * but since we use this table to store support-agents as well
	 * this can be 'support-agent'.
	 *
	 * @see \WP_Ultimo\Models\Support_Agent
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type;

	/**
	 * Date when the customer was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_registered;

	/**
	 * Email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $email_verification;

	/**
	 * Date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $last_login;

	/**
	 * Whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @var null|bool
	 */
	protected $has_trialed;

	/**
	 * If this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $vip = false;

	/**
	 * List of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $ips;

	/**
	 * The form used to signup.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $signup_form = 'by-admin';

	/**
	 * Extra information about this customer.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $extra_information;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = \WP_Ultimo\Database\Customers\Customer_Query::class;

	/**
	 * Allows injection, which is useful for mocking.
	 *
	 * @since 2.2.0
	 * @var string
	 */
	public $_user;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		$id = $this->get_id();

		return [
			'user_id'            => "required|integer|unique:\WP_Ultimo\Models\Customer,user_id,{$id}",
			'email_verification' => 'required|in:none,pending,verified',
			'type'               => 'required|in:customer',
			'last_login'         => 'default:',
			'has_trialed'        => 'boolean|default:0',
			'vip'                => 'boolean|default:0',
			'ips'                => 'array',
			'extra_information'  => 'default:',
			'signup_form'        => 'default:',
		];
	}

	/**
	 * Get user ID of the associated user.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_user_id() {

		return absint($this->user_id);
	}

	/**
	 * Set user ID of the associated user.
	 *
	 * @since 2.0.0
	 * @param int $user_id The WordPress user ID attached to this customer.
	 * @return void
	 */
	public function set_user_id($user_id): void {

		$this->user_id = $user_id;
	}

	/**
	 * Returns the user associated with this customer.
	 *
	 * @since 2.0.0
	 * @return WP_User
	 */
	public function get_user() {

		return get_user_by('id', $this->get_user_id());
	}

	/**
	 * Returns the customer's display name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_display_name() {

		$user = $this->get_user();

		if (empty($user)) {
			return __('User Deleted', 'multisite-ultimate');
		}

		return $user->display_name;
	}

	/**
	 * Returns the default billing address.
	 *
	 * Classes that implement this trait need to implement
	 * this method.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_default_billing_address() {

		return new \WP_Ultimo\Objects\Billing_Address(
			[
				'company_name'    => $this->get_display_name(),
				'billing_email'   => $this->get_email_address(),
				'billing_country' => $this->get_meta('ip_country'),
			]
		);
	}

	/**
	 * Returns the customer country.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_country() {

		$billing_address = $this->get_billing_address();

		$country = $billing_address->billing_country;

		if ( ! $country) {
			return $this->get_meta('ip_country');
		}

		return $country;
	}

	/**
	 * Returns the customer's username.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_username() {

		$user = $this->get_user();

		if (empty($user)) {
			return __('none', 'multisite-ultimate');
		}

		return $user->user_login;
	}

	/**
	 * Returns the customer's email address.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_email_address() {

		$user = $this->get_user();

		if (empty($user)) {
			return __('none', 'multisite-ultimate');
		}

		return $user->user_email;
	}

	/**
	 * Get date when the customer was created.
	 *
	 * @since 2.0.0
	 * @param bool $formatted To format or not.
	 * @return string
	 */
	public function get_date_registered($formatted = true) {

		return $this->date_registered;
	}

	/**
	 * Set date when the customer was created.
	 *
	 * @since 2.0.0
	 * @param string $date_registered Date when the customer was created.
	 * @return void
	 */
	public function set_date_registered($date_registered): void {

		$this->date_registered = $date_registered;
	}

	/**
	 * Get email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_email_verification() {

		return $this->email_verification;
	}

	/**
	 * Set email verification status - either `none`, `pending`, or `verified`.
	 *
	 * @since 2.0.0
	 * @param string $email_verification Email verification status - either `none`, `pending`, or `verified`.
	 * @return void
	 */
	public function set_email_verification($email_verification): void {

		$this->email_verification = $email_verification;
	}

	/**
	 * Get date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @param bool $formatted To format or not.
	 * @return string
	 */
	public function get_last_login($formatted = true) {

		return $this->last_login;
	}

	/**
	 * Set date this customer last logged in.
	 *
	 * @since 2.0.0
	 * @param string $last_login Date this customer last logged in.
	 * @return void
	 */
	public function set_last_login($last_login): void {

		$this->last_login = $last_login;
	}

	/**
	 * Get whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @return null|bool
	 */
	public function has_trialed() {

		if ((bool) $this->has_trialed) {
			return true;
		}

		$this->has_trialed = $this->get_meta('wu_has_trialed');

		if ( ! $this->has_trialed) {
			$trial = wu_get_memberships(
				[
					'customer_id'            => $this->get_id(),
					'date_trial_end__not_in' => [null, '0000-00-00 00:00:00'],
					'fields'                 => 'ids',
					'number'                 => 1,
				]
			);

			if ( ! empty($trial)) {
				$this->update_meta('wu_has_trialed', true);

				$this->has_trialed = true;
			}
		}

		return $this->has_trialed;
	}

	/**
	 * Set whether or not the customer has trialed before.
	 *
	 * @since 2.0.0
	 * @param bool $has_trialed Whether or not the customer has trialed before.
	 * @return void
	 */
	public function set_has_trialed($has_trialed): void {

		$this->meta['wu_has_trialed'] = $has_trialed;

		$this->has_trialed = $has_trialed;
	}

	/**
	 * Get if this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_vip() {

		return (bool) $this->vip;
	}

	/**
	 * Set if this customer is a VIP customer or not.
	 *
	 * @since 2.0.0
	 * @param bool $vip If this customer is a VIP customer or not.
	 * @return void
	 */
	public function set_vip($vip): void {

		$this->vip = $vip;
	}

	/**
	 * Get list of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_ips() {

		if (empty($this->ips)) {
			return [];
		}

		if (is_string($this->ips)) {
			$this->ips = maybe_unserialize($this->ips);
		}

		return $this->ips;
	}

	/**
	 * Returns the last IP address recorded for the customer.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_last_ip() {

		$ips = $this->get_ips();

		return array_pop($ips);
	}

	/**
	 * Set list of IP addresses used by this customer.
	 *
	 * @since 2.0.0
	 * @param array $ips List of IP addresses used by this customer.
	 * @return void
	 */
	public function set_ips($ips): void {

		if (is_string($ips)) {
			$ips = maybe_unserialize(wp_unslash($ips));
		}

		$this->ips = $ips;
	}

	/**
	 * Adds a new IP to the IP list.
	 *
	 * @since 2.0.0
	 *
	 * @param string $ip New IP address to add.
	 * @return void
	 */
	public function add_ip($ip): void {

		$ips = $this->get_ips();

		if ( ! is_array($ips)) {
			$ips = [];
		}

		/*
		 * IP already exists.
		 */
		if (in_array($ip, $ips, true)) {
			return;
		}

		$ips[] = sanitize_text_field($ip);

		$this->set_ips($ips);
	}

	/**
	 * Updates the last login, as well as the ip and country if necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $update_ip If we want to update the IP address.
	 * @param boolean $update_country_and_state If we want to update country and state.
	 * @return boolean
	 */
	public function update_last_login($update_ip = true, $update_country_and_state = false) {

		$this->attributes(
			[
				'last_login' => wu_get_current_time('mysql', true),
			]
		);

		$geolocation = $update_ip || $update_country_and_state ? \WP_Ultimo\Geolocation::geolocate_ip('', true) : false;

		if ($update_ip) {
			$this->add_ip($geolocation['ip']);
		}

		if ($update_country_and_state) {
			$this->update_meta('ip_country', $geolocation['country']);
			$this->update_meta('ip_state', $geolocation['state']);
		}

		return $this->save();
	}

	/**
	 * Get extra information.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_information() {

		if (null === $this->extra_information) {
			$extra_information = (array) $this->get_meta('wu_customer_extra_information');

			$this->extra_information = array_filter($extra_information);
		}

		return $this->extra_information;
	}

	/**
	 * Set featured extra information.
	 *
	 * @since 2.0.0
	 * @param array $extra_information Any extra information related to this customer.
	 * @return void
	 */
	public function set_extra_information($extra_information): void {

		$extra_information = array_filter((array) $extra_information);

		$this->extra_information                     = $extra_information;
		$this->meta['wu_customer_extra_information'] = $extra_information;
	}

	/**
	 * Returns the subscriptions attached to this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_memberships() {

		return Membership::query(
			[
				'customer_id' => $this->get_id(),
			]
		);
	}

	/**
	 * Returns the sites attached to this customer.
	 *
	 * @since 2.0.0
	 * @param array $query_args Query arguments.
	 * @return array
	 */
	public function get_sites($query_args = []) {

		$query_args = array_merge(
			$query_args,
			[
				'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'customer_id' => [
						'key'   => 'wu_customer_id',
						'value' => $this->get_id(),
					],
				],
			]
		);

		return Site::query($query_args);
	}

	/**
	 * Returns all pending sites associated with a customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pending_sites() {

		$pending_sites = [];

		$memberships = $this->get_memberships();

		foreach ($memberships as $membership) {
			$pending_site = $membership->get_pending_site();

			if ($pending_site) {
				$pending_sites[] = $pending_site;
			}
		}

		return $pending_sites;
	}

	/**
	 * The the primary site ID if available.
	 *
	 * In cases where none is set, we:
	 * - return the id of the first site on the list off sites
	 * belonging to this customer;
	 * - or return the main site id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_primary_site_id() {

		$primary_site_id = get_user_option('primary_blog', $this->get_user_id());

		if ( ! $primary_site_id) {
			$sites = $this->get_sites();

			$primary_site_id = $sites ? $sites[0]->get_id() : wu_get_main_site_id();
		}

		return $primary_site_id;
	}

	/**
	 * Returns the payments attached to this customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payments() {

		return Payment::query(
			[
				'customer_id' => $this->get_id(),
			]
		);
	}

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$user = get_userdata($this->get_user_id());

		if (isset($this->_user)) {
			$user = $this->_user; // Allows for injection, which is useful for mocking.

			unset($this->_user);
		}

		$search_result = $this->to_array();

		if ($user) {
			$user->data->avatar = get_avatar(
				$user->data->user_email,
				40,
				'identicon',
				'',
				[
					'force_display' => true,
					'class'         => 'wu-rounded-full wu-mr-3',
				]
			);

			$search_result = array_merge((array) $user->data, $search_result);
		}

		$search_result['billing_address_data'] = $this->get_billing_address()->to_array();
		$search_result['billing_address']      = $this->get_billing_address()->to_string();

		return $search_result;
	}

	/**
	 * Get the customer type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return $this->type;
	}

	/**
	 * Get the customer type.
	 *
	 * @since 2.0.0
	 * @param string $type The customer type. Can be 'customer'.
	 * @options customer
	 * @return void
	 */
	public function set_type($type) {

		$this->type = $type;
	}

	/**
	 * Gets the total grossed by the customer so far.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total_grossed() {

		global $wpdb;

		static $sum;

		if (null === $sum) {
			$sum = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT SUM(total) FROM {$wpdb->base_prefix}wu_payments WHERE parent_id = 0 AND customer_id = %d",
					$this->get_id()
				)
			);
		}

		return $sum;
	}

	/**
	 * Get if the customer is online or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_online() {

		if ($this->get_last_login() === '0000-00-00 00:00:00') {
			return false;
		}

		$last_login_date = new \DateTime($this->get_last_login());
		$now             = new \DateTime('now');

		$interval          = $last_login_date->diff($now);
		$minutes_interval  = $interval->days * 24 * 60;
		$minutes_interval += $interval->h * 60;
		$minutes_interval += $interval->i;

		return $minutes_interval <= apply_filters('wu_is_online_minutes_interval', 3);
	}

	/**
	 * Saves a verification key.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function generate_verification_key() {

		$seed = time();

		$hash = \WP_Ultimo\Helpers\Hash::encode($seed, 'verification-key');

		return $this->update_meta('wu_verification_key', $hash);
	}

	/**
	 * Returns the saved verification key.
	 *
	 * @since 2.0.0
	 * @return string|bool
	 */
	public function get_verification_key() {

		return $this->get_meta('wu_verification_key', false);
	}

	/**
	 * Disabled the verification by setting the key to false.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function disable_verification_key() {

		return $this->update_meta('wu_verification_key', false);
	}

	/**
	 * Returns the link of the email verification endpoint.
	 *
	 * @since 2.0.0
	 * @return string|bool
	 */
	public function get_verification_url() {

		$key = $this->get_verification_key();

		if ( ! $key) {
			return get_site_url(wu_get_main_site_id());
		}

		return add_query_arg(
			[
				'email-verification-key' => $key,
				'customer'               => $this->get_hash(),
			],
			get_site_url(wu_get_main_site_id())
		);
	}

	/**
	 * Send verification email.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function send_verification_email(): void {

		$this->generate_verification_key();

		$payload = array_merge(
			['verification_link' => $this->get_verification_url()],
			wu_generate_event_payload('customer', $this)
		);

		wu_do_event('confirm_email_address', $payload);
	}

	/**
	 * Get the form used to signup.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_signup_form() {

		return $this->signup_form;
	}

	/**
	 * Set the form used to signup.
	 *
	 * @since 2.0.0
	 * @param string $signup_form The form used to signup.
	 * @return void
	 */
	public function set_signup_form($signup_form): void {

		$this->signup_form = $signup_form;
	}
}
