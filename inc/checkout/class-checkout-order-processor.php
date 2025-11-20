<?php
/**
 * Handles checkout order processing.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use Psr\Log\LogLevel;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Managers\Payment_Manager;

/**
 * Processes checkout orders and handles gateway interactions.
 *
 * @since 2.4.8
 */
class Checkout_Order_Processor {

	/**
	 * The session manager.
	 *
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	/**
	 * The entity factory.
	 *
	 * @var Checkout_Entity_Factory
	 */
	protected Checkout_Entity_Factory $entity_factory;

	/**
	 * The validator.
	 *
	 * @var Checkout_Validator
	 */
	protected Checkout_Validator $validator;

	/**
	 * The cart/order object.
	 *
	 * @var Cart
	 */
	protected Cart $order;

	/**
	 * The checkout type.
	 *
	 * @var string
	 */
	protected string $type = 'new';

	/**
	 * The gateway ID.
	 *
	 * @var string
	 */
	protected string $gateway_id = '';

	/**
	 * Holds checkout errors.
	 *
	 * @var \WP_Error|null
	 */
	protected ?\WP_Error $errors = null;

	/**
	 * The customer object.
	 *
	 * @var \WP_Ultimo\Models\Customer|null
	 */
	protected ?\WP_Ultimo\Models\Customer $customer = null;

	/**
	 * The membership object.
	 *
	 * @var \WP_Ultimo\Models\Membership|null
	 */
	protected ?\WP_Ultimo\Models\Membership $membership = null;

	/**
	 * The pending site object.
	 *
	 * @var \WP_Ultimo\Models\Site|null
	 */
	protected ?\WP_Ultimo\Models\Site $pending_site = null;

	/**
	 * The payment object.
	 *
	 * @var \WP_Ultimo\Models\Payment|null
	 */
	protected ?\WP_Ultimo\Models\Payment $payment = null;

	/**
	 * Constructor.
	 *
	 * @param Checkout_Session_Manager $session_manager The session manager.
	 * @param Checkout_Entity_Factory  $entity_factory The entity factory.
	 * @param Checkout_Validator       $validator The validator.
	 */
	public function __construct(
		Checkout_Session_Manager $session_manager,
		Checkout_Entity_Factory $entity_factory,
		Checkout_Validator $validator
	) {

		$this->session_manager = $session_manager;
		$this->entity_factory  = $entity_factory;
		$this->validator       = $validator;
	}

	/**
	 * Gets the order.
	 *
	 * @return Cart|null
	 */
	public function get_order(): ?Cart {

		return $this->order;
	}

	/**
	 * Gets the errors.
	 *
	 * @return \WP_Error|null
	 */
	public function get_errors(): ?\WP_Error {

		return $this->errors;
	}

	/**
	 * Gets the customer.
	 *
	 * @return \WP_Ultimo\Models\Customer|null
	 */
	public function get_customer(): ?\WP_Ultimo\Models\Customer {

		return $this->customer;
	}

	/**
	 * Gets the membership.
	 *
	 * @return \WP_Ultimo\Models\Membership|null
	 */
	public function get_membership(): ?\WP_Ultimo\Models\Membership {

		return $this->membership;
	}

	/**
	 * Gets the payment.
	 *
	 * @return \WP_Ultimo\Models\Payment|null
	 */
	public function get_payment(): ?\WP_Ultimo\Models\Payment {

		return $this->payment;
	}

	/**
	 * Validates the order submission and then delegates the processing to the gateway.
	 *
	 * @param Checkout $checkout The checkout instance for hooks.
	 *
	 * @return void
	 */
	public function handle_order_submission(Checkout $checkout): void {

		global $wpdb;

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$results = null;

		try {
			do_action('wu_before_handle_order_submission', $checkout);

			$results = $this->process_order($checkout);

			do_action('wu_after_handle_order_submission', $results, $checkout);

			if (is_wp_error($results)) {
				$this->errors = $results;
			}
		} catch (\Throwable $e) {
			wu_maybe_log_error($e);

			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			$this->errors = new \WP_Error('exception-order-submission', $e->getMessage(), $e->getTrace());
		}

		if (is_wp_error($this->errors)) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			wp_send_json_error($this->errors);
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$this->session_manager->clear_session();

		wp_send_json_success($results);
	}

	/**
	 * Process an order.
	 *
	 * @param Checkout $checkout The checkout instance for hooks.
	 *
	 * @return array|\WP_Error
	 */
	public function process_order(Checkout $checkout) {

		$cart = new Cart(
			apply_filters(
				'wu_cart_parameters',
				[
					'products'      => $this->session_manager->request_or_session('products', []),
					'discount_code' => $this->session_manager->request_or_session('discount_code'),
					'country'       => $this->session_manager->request_or_session('billing_country'),
					'state'         => $this->session_manager->request_or_session('billing_state'),
					'city'          => $this->session_manager->request_or_session('billing_city'),
					'membership_id' => $this->session_manager->request_or_session('membership_id'),
					'payment_id'    => $this->session_manager->request_or_session('payment_id'),
					'auto_renew'    => $this->session_manager->request_or_session('auto_renew'),
					'duration'      => $this->session_manager->request_or_session('duration'),
					'duration_unit' => $this->session_manager->request_or_session('duration_unit'),
					'cart_type'     => $this->session_manager->request_or_session('cart_type', 'new'),
				],
				$checkout
			)
		);

		// Improved cart validation - check for both false and WP_Error
		$is_valid = $cart->is_valid();
		if (false === $is_valid || is_wp_error($is_valid)) {
			return $cart->get_errors();
		}

		$this->type = $cart->get_cart_type();

		// Configure dependencies
		$this->entity_factory->set_type($this->type);
		$this->validator->set_type($this->type);

		$gateway_id = $this->session_manager->request_or_session('gateway');
		$gateway    = wu_get_gateway($gateway_id);

		// Consolidated gateway validation
		if ($cart->should_collect_payment() === false) {
			$gateway = wu_get_gateway('free');
		} elseif ( ! $gateway || $gateway->get_id() === 'free') {
			return new \WP_Error('no-gateway', __('Payment gateway not registered.', 'ultimate-multisite'));
		}

		if ( ! $gateway) {
			return new \WP_Error('no-gateway', __('Payment gateway not registered.', 'ultimate-multisite'));
		}

		$this->gateway_id = $gateway->get_id();
		$this->entity_factory->set_gateway_id($this->gateway_id);

		$validation = $this->validator->validate();

		if (is_wp_error($validation)) {
			return $validation;
		}

		$this->order = $cart;
		$this->entity_factory->set_order($this->order);

		add_filter('pre_user_display_name', [$checkout, 'handle_display_name']);

		$customer = $this->entity_factory->maybe_create_customer($checkout);

		if (is_wp_error($customer)) {
			return $customer;
		} elseif ($customer) {
			$this->customer = $customer;
		}

		$membership = $this->entity_factory->maybe_create_membership($this->customer);

		if (is_wp_error($membership)) {
			return $membership;
		} elseif ($membership) {
			$this->membership = $membership;
		}

		$pending_site = $this->entity_factory->maybe_create_site($this->customer, $this->membership);

		if (is_wp_error($pending_site)) {
			return $pending_site;
		} elseif ($pending_site) {
			$this->pending_site = $pending_site;
		}

		$payment = $this->entity_factory->maybe_create_payment($this->customer, $this->membership);

		if (is_wp_error($payment)) {
			return $payment;
		} elseif ($payment) {
			$this->payment = $payment;
		}

		$this->payment->update_meta('wu_original_cart', $this->order);

		$this->order->set_customer($this->customer);
		$this->order->set_membership($this->membership);
		$this->order->set_payment($this->payment);

		$gateway->set_order($this->order);

		// Log in the user if not already logged in
		if ( ! is_user_logged_in()) {
			wp_clear_auth_cookie();

			$user_credentials = array(
				'user_login'    => $this->customer->get_username(),
				'user_password' => $this->session_manager->request_or_session('password'),
			);

			remove_action('wp_login', array(Payment_Manager::get_instance(), 'check_pending_payments'));

			wp_signon($user_credentials, is_ssl());
		}

		try {
			// Handle free memberships
			if ($this->order->is_free() && $this->order->get_recurring_total() === 0.0 && $this->customer->get_email_verification() !== 'pending') {
				if ($this->order->get_plan_id() === $this->membership->get_plan_id()) {
					$this->membership->set_status(Membership_Status::ACTIVE);
					$this->membership->save();
				}

				$gateway->trigger_payment_processed($this->payment, $this->membership);
			} elseif ($this->order->has_trial()) {
				$this->membership->set_date_trial_end(gmdate('Y-m-d 23:59:59', $this->order->get_billing_start_date()));
				$this->membership->set_date_expiration(gmdate('Y-m-d 23:59:59', $this->order->get_billing_start_date()));

				if (wu_get_setting('allow_trial_without_payment_method') && $this->customer->get_email_verification() !== 'pending') {
					$this->membership->set_status(Membership_Status::TRIALING);
					$this->membership->publish_pending_site_async();
				}

				$this->membership->save();

				$gateway->trigger_payment_processed($this->payment, $this->membership);
			}

			$success_data = [
				'nonce'           => wp_create_nonce('wp-ultimo-register-nonce'),
				'customer'        => $this->customer->to_search_results(),
				'total'           => $this->order->get_total(),
				'recurring_total' => $this->order->get_recurring_total(),
				'membership_id'   => $this->membership->get_id(),
				'payment_id'      => $this->payment->get_id(),
				'cart_type'       => $this->order->get_cart_type(),
				'auto_renew'      => $this->order->should_auto_renew(),
				'gateway'         => [
					'slug' => $gateway->get_id(),
					'data' => [],
				],
			];

			$result = $gateway->run_preflight();

			if (is_wp_error($result)) {
				return $result;
			}

			$success_data['gateway']['data'] = is_array($result) ? $result : [];
		} catch (\Throwable $e) {
			wu_maybe_log_error($e);

			return new \WP_Error('exception', $e->getMessage(), $e->getTrace());
		}

		do_action('wu_checkout_after_process_order', $checkout, $this->order);

		return $success_data;
	}

	/**
	 * Creates an order object to display the order summary tables.
	 *
	 * @return void
	 */
	public function create_order(): void {

		// Set the billing address to be used on the order
		$country = ! empty($this->session_manager->request_or_session('country')) ? $this->session_manager->request_or_session('country') : $this->session_manager->request_or_session('billing_country', '');
		$state   = ! empty($this->session_manager->request_or_session('state')) ? $this->session_manager->request_or_session('state') : $this->session_manager->request_or_session('billing_state', '');
		$city    = ! empty($this->session_manager->request_or_session('city')) ? $this->session_manager->request_or_session('city') : $this->session_manager->request_or_session('billing_city', '');

		$cart = new Cart(
			apply_filters(
				'wu_cart_parameters',
				[
					'products'      => $this->session_manager->request_or_session('products', []),
					'discount_code' => $this->session_manager->request_or_session('discount_code'),
					'country'       => $country,
					'state'         => $state,
					'city'          => $city,
					'membership_id' => $this->session_manager->request_or_session('membership_id'),
					'payment_id'    => $this->session_manager->request_or_session('payment_id'),
					'auto_renew'    => $this->session_manager->request_or_session('auto_renew'),
					'duration'      => $this->session_manager->request_or_session('duration'),
					'duration_unit' => $this->session_manager->request_or_session('duration_unit'),
					'cart_type'     => $this->session_manager->request_or_session('cart_type', 'new'),
				],
				$this
			)
		);

		$country_data = wu_get_country($cart->get_country());

		wp_send_json_success(
			[
				'order'  => $cart->done(),
				'states' => wu_key_map_to_array($country_data->get_states_as_options(), 'code', 'name'),
				'cities' => wu_key_map_to_array($country_data->get_cities_as_options($state), 'code', 'name'),
				'labels' => [
					'state_field' => $country_data->get_administrative_division_name(null, true),
					'city_field'  => $country_data->get_municipality_name(null, true),
				],
			]
		);
	}

	/**
	 * Handles the checkout submission.
	 *
	 * @param Checkout $checkout The checkout instance for hooks.
	 *
	 * @return bool|void|\WP_Error
	 */
	public function process_checkout(Checkout $checkout) {

		do_action('wu_checkout_before_process_checkout', $checkout);

		$gateway = wu_get_gateway($this->session_manager->get_request_value('gateway'));
		$payment = wu_get_payment($this->session_manager->request_or_session('payment_id'));

		if ( ! $payment) {
			// translators: %s is the payment ID that was not found.
			$this->errors = new \WP_Error('no-payment', sprintf(__('Payment (%s) not found.', 'ultimate-multisite'), $this->session_manager->request_or_session('payment_id')));
			return false;
		}

		$customer   = $payment->get_customer();
		$membership = $payment->get_membership();

		$this->order = $payment->get_meta('wu_original_cart');
		$this->order->set_membership($membership);
		$this->order->set_customer($customer);
		$this->order->set_payment($payment);

		try {
			if ($payment->get_status() === Payment_Status::COMPLETED) {
				$gateway = wu_get_gateway($payment->get_gateway());
			} elseif ($this->order->should_collect_payment() === false) {
				$gateway = wu_get_gateway('free');
			} elseif ($gateway && $gateway->get_id() === 'free') {
				$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'ultimate-multisite'));
				return false;
			}

			if ( ! $gateway) {
				$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'ultimate-multisite'));
				return false;
			}

			$gateway->set_order($this->order);

			$type = $this->order->get_cart_type();

			$status = $gateway->process_checkout($payment, $membership, $customer, $this->order, $type);

			if (false === $status) {
				return true;
			}

			do_action('wu_checkout_done', $payment, $membership, $customer, $this->order, $type, $checkout);

			// Handle deprecated hook
			if (has_action('wp_ultimo_registration')) {
				$_payment = wu_get_payment($payment->get_id());

				$args = [
					0, // Site ID is not yet available at this point
					$customer->get_user_id(),
					$this->session_manager->get_session()->get('signup'),
					$_payment && $_payment->get_membership() ? new \WU_Plan($_payment->get_membership()->get_plan()) : false,
				];

				ob_start();

				do_action_deprecated('wp_ultimo_registration', $args, '2.0.0');

				ob_flush();
			}

			$redirect_url = $gateway->get_return_url();

			if ( ! is_admin()) {
				$redirect_url = apply_filters('wp_ultimo_redirect_url_after_signup', $redirect_url, 0, get_current_user_id());

				$redirect_url = add_query_arg(
					[
						'payment' => $payment->get_hash(),
						'status'  => 'done',
					],
					$redirect_url
				);
			}

			wp_safe_redirect($redirect_url);

			exit;
		} catch (\Throwable $e) {
			$membership_id = $this->order->get_membership() ? $this->order->get_membership()->get_id() : 'unknown';

			// translators: %s is the membership ID for which checkout failed.
			$log_message  = sprintf(__('Checkout failed for customer %s: ', 'ultimate-multisite'), $membership_id);
			$log_message .= $e->getMessage();

			wu_log_add('checkout', $log_message, LogLevel::ERROR);

			return new \WP_Error(
				'error',
				$e->getMessage(),
				[
					'trace'   => $e->getTrace(),
					'payment' => $payment,
				]
			);
		}
	}
}
