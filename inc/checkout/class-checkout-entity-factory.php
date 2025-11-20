<?php
/**
 * Handles creation of checkout entities.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Sites\Site_Type;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Models\Customer;
use WP_Ultimo\Models\Membership;
use WP_User;

/**
 * Factory class for creating checkout entities (customers, memberships, sites, payments).
 *
 * @since 2.4.8
 */
class Checkout_Entity_Factory {

	/**
	 * The session manager.
	 *
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	/**
	 * The Setup handler.
	 *
	 * @var Checkout_Setup_Handler
	 */
	protected Checkout_Setup_Handler $setup_handler;

	/**
	 * The cart/order object.
	 *
	 * @var Cart
	 */
	protected Cart $order;

	/**
	 * The gateway ID.
	 *
	 * @var string
	 */
	protected string $gateway_id = '';

	/**
	 * The checkout type.
	 *
	 * @var string
	 */
	protected string $type = 'new';

	/**
	 * Sets the session manager.
	 *
	 * @param Checkout_Session_Manager $session_manager The session manager.
	 * @param Checkout_Setup_Handler   $setup_handler The session manager.
	 */
	public function __construct(
		Checkout_Session_Manager $session_manager,
		Checkout_Setup_Handler $setup_handler
	) {
		$this->session_manager = $session_manager;
		$this->setup_handler   = $setup_handler;
	}

	/**
	 * Sets the order/cart.
	 *
	 * @param Cart $order The cart/order object.
	 * @return void
	 */
	public function set_order(Cart $order): void {

		$this->order = $order;
	}

	/**
	 * Sets the gateway ID.
	 *
	 * @param string $gateway_id The gateway ID.
	 * @return void
	 */
	public function set_gateway_id(string $gateway_id): void {

		$this->gateway_id = $gateway_id;
	}

	/**
	 * Sets the checkout type.
	 *
	 * @param string $type The checkout type.
	 * @return void
	 */
	public function set_type(string $type): void {

		$this->type = $type;
	}

	/**
	 * Gets data from a request or session using the callback.
	 *
	 * @param string $key The key to retrieve.
	 * @param mixed  $default_value The default value.
	 *
	 * @return mixed
	 */
	protected function request_or_session(string $key, $default_value = false) {
		return $this->session_manager->request_or_session($key, $default_value);
	}

	/**
	 * Checks if a customer exists, otherwise, creates a new one.
	 *
	 * @param Checkout $checkout The checkout instance for hooks.
	 * @return Customer|\WP_Error
	 */
	public function maybe_create_customer(Checkout $checkout) {

		$customer = wu_get_current_customer();

		$form_slug = $this->setup_handler->get_checkout_form() ? $this->setup_handler->get_checkout_form()->get_slug() : 'none';

		if (empty($customer)) {
			$username = $this->request_or_session('username');

			if ($this->request_or_session('auto_generate_username') === 'email') {
				$username = wu_username_from_email($this->request_or_session('email_address'));
			}

			$customer_data = [
				'username'           => $username,
				'email'              => $this->request_or_session('email_address'),
				'password'           => $this->request_or_session('password'),
				'email_verification' => $this->get_customer_email_verification_status(),
				'signup_form'        => $form_slug,
				'meta'               => [],
			];

			if ($this->is_existing_user()) {
				$customer_data = [
					'email'              => wp_get_current_user()->user_email,
					'email_verification' => 'verified',
				];
			} elseif (isset($customer_data['email']) && get_user_by('email', $customer_data['email'])) {
				return new \WP_Error('email_exists', __('The email address you entered is already in use.', 'ultimate-multisite'));
			}

			$customer = wu_create_customer($customer_data);

			if (is_wp_error($customer)) {
				return $customer;
			}
		}

		$customer->update_last_login(true, true);

		$billing_address = $customer->get_billing_address();

		$session = $this->session_manager->get_signup_data() ?? [];
		$billing_address->load_attributes_from_post($session);

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {
			return $valid_address;
		}

		$customer->set_billing_address($billing_address);

		$address_saved = $customer->save();

		if ( ! $address_saved) {
			return new \WP_Error('address_failure', __('Something wrong happened while attempting to save the customer billing address', 'ultimate-multisite'));
		}

		$this->handle_customer_meta_fields($customer, $form_slug);

		/**
		 * Allow plugin developers to do additional stuff when the customer is added.
		 *
		 * @param Customer $customer The customer that was maybe created.
		 * @param Checkout $checkout The current checkout class.
		 */
		do_action('wu_maybe_create_customer', $customer, $checkout);

		return $customer;
	}

	/**
	 * Save meta data related to customers.
	 *
	 * @param Customer $customer The created customer.
	 * @param string   $form_slug The form slug.
	 * @return void
	 */
	public function handle_customer_meta_fields(Customer $customer, string $form_slug) {

		if (empty($form_slug) || 'none' === $form_slug) {
			return;
		}

		$checkout_form = wu_get_checkout_form_by_slug($form_slug);

		if ($checkout_form) {
			$customer_meta_fields = $checkout_form->get_all_meta_fields();

			$meta_repository = [];

			foreach ($customer_meta_fields as $customer_meta_field) {
				$meta_repository[ $customer_meta_field['id'] ] = $this->request_or_session($customer_meta_field['id']);

				wu_update_customer_meta(
					$customer->get_id(),
					$customer_meta_field['id'],
					$this->request_or_session($customer_meta_field['id']),
					$customer_meta_field['type'],
					$customer_meta_field['name']
				);
			}

			/**
			 * Allow plugin developers to save meta-data in different ways if they need to.
			 *
			 * @param array    $meta_repository The list of meta-fields, key => value structured.
			 * @param Customer $customer The Ultimate Multisite customer object.
			 * @param object   $context The context (this factory or checkout class).
			 */
			do_action('wu_handle_customer_meta_fields', $meta_repository, $customer, $this);

			$user_meta_fields = $checkout_form->get_all_meta_fields('user_meta');

			$user = $customer->get_user();

			$user_meta_repository = [];

			foreach ($user_meta_fields as $user_meta_field) {
				$user_meta_repository[ $user_meta_field['id'] ] = $this->request_or_session($user_meta_field['id']);

				update_user_meta($customer->get_user_id(), $user_meta_field['id'], $this->request_or_session($user_meta_field['id']));
			}

			/**
			 * Allow plugin developers to save user meta-data in different ways if they need to.
			 *
			 * @param array    $meta_repository The list of meta-fields, key => value structured.
			 * @param WP_User  $user The WordPress user object.
			 * @param Customer $customer The Ultimate Multisite customer object.
			 * @param object   $context The context (this factory or checkout class).
			 */
			do_action('wu_handle_user_meta_fields', $user_meta_repository, $user, $customer, $this);
		}
	}

	/**
	 * Checks if a membership exists, otherwise, creates a new one.
	 *
	 * @param Customer $customer The customer.
	 *
	 * @return Membership|\WP_Error
	 */
	public function maybe_create_membership(Customer $customer) {

		if ($this->order->get_membership()) {
			return $this->order->get_membership();
		}

		$membership_data = $this->order->to_membership_data();

		$membership_data['customer_id']   = $customer->get_id();
		$membership_data['user_id']       = $customer->get_user_id();
		$membership_data['gateway']       = $this->gateway_id;
		$membership_data['signup_method'] = $this->session_manager->get_request_value('signup_method');

		$membership_data['date_expiration'] = gmdate('Y-m-d 23:59:59', $this->order->get_billing_start_date());

		$membership = wu_create_membership($membership_data);

		$discount_code = $this->order->get_discount_code();

		if ($discount_code) {
			$membership->set_discount_code($discount_code);
			$membership->save();
		}

		return $membership;
	}

	/**
	 * Checks if a pending site exists, otherwise, creates a new one.
	 *
	 * @param Customer   $customer The customer.
	 * @param Membership $membership The membership.
	 *
	 * @return false|\WP_Ultimo\Models\Site|\WP_Error
	 */
	public function maybe_create_site(Customer $customer, Membership $membership) {

		$sites = $membership->get_sites();

		if ( ! empty($sites)) {
			return current($sites);
		}

		$site_url   = $this->request_or_session('site_url');
		$site_title = $this->request_or_session('site_title');

		// Handle special auto-generation values passed from form fields
		if ('autogenerate' === $site_title) {
			$site_title = $customer->get_username();
		}

		if ('autogenerate' === $site_url && $site_title) {
			$site_url = wu_generate_unique_site_url($site_title, $this->request_or_session('site_domain'));
		} elseif ('autogenerate' === $site_url) {
			$site_url = wu_generate_unique_site_url($customer->get_username(), $this->request_or_session('site_domain'));
		}

		if ( ! $site_url && ! $site_title) {
			return false;
		}

		$auto_generate_url = $this->request_or_session('auto_generate_site_url');

		$site_title = ! $site_title && ! $auto_generate_url ? $site_url : $site_title;

		if (empty($site_url) || in_array($auto_generate_url, ['username', 'site_title'], true)) {
			if ('username' === $auto_generate_url) {
				$site_url   = $customer->get_username();
				$site_title = $site_title ?: $site_url;
			} elseif ('site_title' === $auto_generate_url && $site_title) {
				$site_url = wu_generate_unique_site_url($site_title, $this->request_or_session('site_domain'));
			} else {
				$site_url = wu_generate_site_url_from_title($site_title);
				if (empty($site_url)) {
					$site_url = $customer->get_username();
				}

				$site_url = wu_generate_unique_site_url($site_url, $this->request_or_session('site_domain'));
			}
		}

		$d = wu_get_site_domain_and_path($site_url, $this->request_or_session('site_domain'));

		$results = wpmu_validate_blog_signup($site_url, $site_title, $customer->get_user());

		if ($results['errors']->has_errors()) {
			return $results['errors'];
		}
		$checkout_form = $this->setup_handler->get_checkout_form();

		$form_slug = $checkout_form ? $checkout_form->get_slug() : 'none';

		$transient = [];

		if ($checkout_form) {
			$site_meta_fields = $checkout_form->get_all_fields();

			foreach ($site_meta_fields as $site_meta_field) {
				if (str_contains((string) $site_meta_field['id'], 'password')) {
					continue;
				}

				$transient[ $site_meta_field['id'] ] = $this->request_or_session($site_meta_field['id']);
			}
		}

		$template_id = apply_filters('wu_checkout_template_id', (int) $this->request_or_session('template_id'), $membership, $this);

		$site_data = [
			'domain'         => $d->domain,
			'path'           => $d->path,
			'title'          => $site_title,
			'template_id'    => $template_id,
			'customer_id'    => $customer->get_id(),
			'membership_id'  => $membership->get_id(),
			'transient'      => $transient,
			'signup_options' => $this->get_site_meta_fields($form_slug, 'site_option'),
			'signup_meta'    => $this->get_site_meta_fields($form_slug),
			'type'           => Site_Type::CUSTOMER_OWNED,
		];

		return $membership->create_pending_site($site_data);
	}

	/**
	 * Gets a list of site meta-data.
	 *
	 * @param string $form_slug The form slug.
	 * @param string $meta_type The meta-type. Can be site_meta or site_option.
	 * @return array
	 */
	public function get_site_meta_fields(string $form_slug, string $meta_type = 'site_meta'): array {

		if (empty($form_slug) || 'none' === $form_slug) {
			return [];
		}

		$checkout_form = wu_get_checkout_form_by_slug($form_slug);

		$list = [];

		if ($checkout_form) {
			$site_meta_fields = $checkout_form->get_all_meta_fields($meta_type);

			foreach ($site_meta_fields as $site_meta_field) {
				$list[ $site_meta_field['id'] ] = $this->request_or_session($site_meta_field['id']);
			}
		}

		return $list;
	}

	/**
	 * Checks if a pending payment exists, otherwise, creates a new one.
	 *
	 * @param Customer   $customer The customer.
	 * @param Membership $membership The membership.
	 *
	 * @return \WP_Ultimo\Models\Payment|\WP_Error
	 */
	public function maybe_create_payment(Customer $customer, Membership $membership) {

		$payment = $this->order->get_payment();

		if ($payment) {
			if ($payment->get_gateway() !== $this->gateway_id) {
				$payment->set_gateway($this->gateway_id);
				$payment->save();
			}

			return $this->order->get_payment();
		}

		$previous_payment = $membership->get_last_pending_payment();

		$cancel_types = [
			'upgrade',
			'downgrade',
			'addon',
		];

		if ($previous_payment && in_array($this->type, $cancel_types, true)) {
			$previous_payment->set_status(Payment_Status::CANCELLED);
			$previous_payment->save();
		}

		$payment_data = $this->order->to_payment_data();

		$payment_data['customer_id']   = $customer->get_id();
		$payment_data['membership_id'] = $membership->get_id();
		$payment_data['gateway']       = $this->gateway_id;

		if ( ! $this->order->should_collect_payment() && 'downgrade' === $this->type) {
			$payment_data['status'] = Payment_Status::COMPLETED;
		}

		$payment = wu_create_payment($payment_data);

		if (is_wp_error($payment)) {
			return $payment;
		}

		if ($this->order->has_trial()) {
			$payment->attributes(
				[
					'tax_total'    => 0,
					'subtotal'     => 0,
					'refund_total' => 0,
					'total'        => 0,
				]
			);

			$payment->save();
		}

		return $payment;
	}

	/**
	 * Checks if the user already exists.
	 *
	 * @return boolean
	 */
	protected function is_existing_user(): bool {

		return is_user_logged_in();
	}

	/**
	 * Returns the customer email verification status.
	 *
	 * @return string
	 */
	protected function get_customer_email_verification_status(): string {

		$email_verification_setting = wu_get_setting('enable_email_verification', 'free_only');

		switch ($email_verification_setting) {
			case 'never':
				return 'none';

			case 'always':
				return 'pending';

			case 'free_only':
				return $this->order->should_collect_payment() === false ? 'pending' : 'none';

			default:
				$should_confirm_email = (bool) $email_verification_setting;
				return $this->order->should_collect_payment() === false && $should_confirm_email ? 'pending' : 'none';
		}
	}
}
