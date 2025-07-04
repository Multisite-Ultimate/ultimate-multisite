<?php
/**
 * Base Gateway.
 *
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Site_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use Stripe\PaymentMethod;
use Stripe;
use WP_Ultimo\Checkout\Cart;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @since 2.0.0
 */
class Stripe_Checkout_Gateway extends Base_Stripe_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'stripe-checkout';

	/**
	 * Adds the Stripe Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings(): void {

		$error_message_wrap = '<span class="wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-mt-3 wu-mb-0 wu-block wu-text-xs">%s</span>';

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_header',
			[
				'title'           => __('Stripe Checkout', 'multisite-ultimate'),
				'desc'            => __('Use the settings section below to configure Stripe Checkout as a payment method.', 'multisite-ultimate'),
				'type'            => 'header',
				'show_as_submenu' => true,
				'require'         => [
					'active_gateways' => 'stripe-checkout',
				],
			]
		);

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_public_title',
			[
				'title'   => __('Stripe Public Name', 'multisite-ultimate'),
				'tooltip' => __('The name to display on the payment method selection field. By default, "Credit Card" is used.', 'multisite-ultimate'),
				'type'    => 'text',
				'default' => __('Credit Card', 'multisite-ultimate'),
				'require' => [
					'active_gateways' => 'stripe-checkout',
				],
			]
		);

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_sandbox_mode',
			[
				'title'     => __('Stripe Checkout Sandbox Mode', 'multisite-ultimate'),
				'desc'      => __('Toggle this to put Stripe on sandbox mode. This is useful for testing and making sure Stripe is correctly setup to handle your payments.', 'multisite-ultimate'),
				'type'      => 'toggle',
				'default'   => 1,
				'html_attr' => [
					'v-model' => 'stripe_checkout_sandbox_mode',
				],
				'require'   => [
					'active_gateways' => 'stripe-checkout',
				],
			]
		);

		$pk_test_status = wu_get_setting('stripe_checkout_test_pk_key_status', '');

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_test_pk_key',
			[
				'title'       => __('Stripe Test Publishable Key', 'multisite-ultimate'),
				'desc'        => ! empty($pk_test_status) ? sprintf($error_message_wrap, $pk_test_status) : '',
				'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'multisite-ultimate'),
				'placeholder' => __('pk_test_***********', 'multisite-ultimate'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-checkout',
					'stripe_checkout_sandbox_mode' => 1,
				],
			]
		);

		$sk_test_status = wu_get_setting('stripe_checkout_test_sk_key_status', '');

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_test_sk_key',
			[
				'title'       => __('Stripe Test Secret Key', 'multisite-ultimate'),
				'desc'        => ! empty($sk_test_status) ? sprintf($error_message_wrap, $sk_test_status) : '',
				'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'multisite-ultimate'),
				'placeholder' => __('sk_test_***********', 'multisite-ultimate'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-checkout',
					'stripe_checkout_sandbox_mode' => 1,
				],
			]
		);

		$pk_status = wu_get_setting('stripe_checkout_live_pk_key_status', '');

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_live_pk_key',
			[
				'title'       => __('Stripe Live Publishable Key', 'multisite-ultimate'),
				'desc'        => ! empty($pk_status) ? sprintf($error_message_wrap, $pk_status) : '',
				'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'multisite-ultimate'),
				'placeholder' => __('pk_live_***********', 'multisite-ultimate'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-checkout',
					'stripe_checkout_sandbox_mode' => 0,
				],
			]
		);

		$sk_status = wu_get_setting('stripe_checkout_live_sk_key_status', '');

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_live_sk_key',
			[
				'title'       => __('Stripe Live Secret Key', 'multisite-ultimate'),
				'desc'        => ! empty($sk_status) ? sprintf($error_message_wrap, $sk_status) : '',
				'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'multisite-ultimate'),
				'placeholder' => __('sk_live_***********', 'multisite-ultimate'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-checkout',
					'stripe_checkout_sandbox_mode' => 0,
				],
			]
		);

		$webhook_message = sprintf('<span class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-mt-3 wu-mb-0 wu-block wu-text-xs">%s</span>', __('Whenever you change your Stripe settings, Multisite Ultimate will automatically check the webhook URLs on your Stripe account to make sure we get notified about changes in subscriptions and payments.', 'multisite-ultimate'));

		wu_register_settings_field(
			'payment-gateways',
			'stripe_checkout_webhook_listener_explanation',
			[
				'title'           => __('Webhook Listener URL', 'multisite-ultimate'),
				'desc'            => $webhook_message,
				'tooltip'         => __('This is the URL Stripe should send webhook calls to.', 'multisite-ultimate'),
				'type'            => 'text-display',
				'copy'            => true,
				'default'         => $this->get_webhook_listener_url(),
				'wrapper_classes' => '',
				'require'         => [
					'active_gateways' => 'stripe-checkout',
				],
			]
		);

		parent::settings();
	}

	/**
	 * Run preparations before checkout processing.
	 *
	 * This runs during the checkout form validation
	 * and it is a great chance to do preflight stuff
	 * if the gateway requires it.
	 *
	 * If you return an array here, Ultimo
	 * will append the key => value of that array
	 * as hidden fields to the checkout field,
	 * and those get submitted with the rest of the form.
	 *
	 * As an example, this is how we create payment
	 * intents for Stripe to make the experience more
	 * streamlined.
	 *
	 * @since 2.0.0
	 * @return void|array
	 */
	public function run_preflight() {

		/**
		 * Ensure the correct api keys are set
		 */
		$this->setup_api_keys();

		/*
		 * Creates or retrieves the Stripe Customer
		 */
		$s_customer = $this->get_or_create_customer($this->customer->get_id());

		/*
		 * Stripe Checkout allows for tons of different payment methods.
		 * These include:
		 *
		 * 'card'
		 * 'alipay'
		 * 'ideal'
		 * 'fpx'
		 * 'bacs_debit'
		 * 'bancontact'
		 * 'giropay'
		 * 'p24'
		 * 'eps'
		 * 'sofort'
		 * 'sepa_debit'
		 * 'grabpay'
		 * 'afterpay_clearpay'
		 *
		 * For those to work, you'll need to activate them on your
		 * Stripe account, and you should also be in live mode.
		 */
		$allowed_payment_method_types = apply_filters(
			'wu_stripe_checkout_allowed_payment_method_types',
			[
				'card',
			],
			$this
		);

		$metadata = [
			'payment_id'    => $this->payment->get_id(),
			'membership_id' => $this->membership->get_id(),
			'customer_id'   => $this->customer->get_id(),
		];

		$this->membership->set_gateway_customer_id($s_customer->id);
		$this->membership->set_gateway($this->get_id());

		$this->membership->save();

		/**
		 * Verify the card type
		 */
		if ($this->order->get_cart_type() === 'new') {
			$redirect_url = $this->get_return_url();
		} else {
			/*
			 * Saves cart for later swap.
			 */
			$swap_id = $this->save_swap($this->order);

			$redirect_url = add_query_arg('swap', $swap_id, $this->get_confirm_url());

			$metadata['swap_id'] = $swap_id;
		}

		$subscription_data = [
			'payment_method_types'       => $allowed_payment_method_types,
			'success_url'                => $redirect_url,
			'cancel_url'                 => $this->get_cancel_url(),
			'billing_address_collection' => 'required',
			'client_reference_id'        => $this->customer->get_id(),
			'customer'                   => $s_customer->id,
			'metadata'                   => $metadata,
		];

		if ($this->order->should_auto_renew()) {
			$stripe_cart               = $this->build_stripe_cart($this->order);
			$stripe_non_recurring_cart = $this->build_non_recurring_cart($this->order);

			/*
			 * Adds recurring stuff.
			 */
			$subscription_data['subscription_data'] = [
				'items' => array_values($stripe_cart),
			];
		} else {
			/*
			 * Create non-recurring only cart.
			 */
			$stripe_non_recurring_cart = $this->build_non_recurring_cart($this->order, true);
		}

		/*
		 * Add non-recurring line items
		 */
		$subscription_data['line_items'] = $stripe_non_recurring_cart;

		/**
		 * If we have pro-rata credit (in case of an upgrade, for example)
		 * try to create a custom coupon.
		 */
		$s_coupon = $this->get_credit_coupon($this->order);

		if ($s_coupon) {
			$subscription_data['discounts'] = [
				['coupon' => $s_coupon],
			];
		}

		/**
		 * If its a downgrade, we need to set as a trial,
		 * billing_cycle_anchor isn't supported by Checkout.
		 * (https://stripe.com/docs/api/checkout/sessions/create)
		 */
		if ($this->order->get_cart_type() === 'downgrade') {
			$next_charge      = $this->order->get_billing_next_charge_date();
			$next_charge_date = \DateTime::createFromFormat('U', $next_charge);
			$current_time     = new \DateTime();

			if ($current_time < $next_charge_date) {

				// The `trial_end` date has to be at least 2 days in the future.
				$next_charge = $next_charge_date->diff($current_time)->days > 2 ? $next_charge : strtotime('+2 days');

				$subscription_data['subscription_data']['trial_end'] = $next_charge;
			}
		}

		/*
		 * Handle trial periods.
		 */
		if ($this->order->has_trial() && $this->order->has_recurring()) {
			$subscription_data['subscription_data']['trial_end'] = $this->order->get_billing_start_date();
		}

		$session = $this->get_stripe_client()->checkout->sessions->create($subscription_data);

		// Add the client secret to the JSON success data.
		return [
			'stripe_session_id' => sanitize_text_field($session->id),
		];
	}

	/**
	 * Handles confirmation windows and extra processing.
	 *
	 * This endpoint gets called when we get to the
	 * /confirm/ URL on the registration page.
	 *
	 * For example, PayPal needs a confirmation screen.
	 * And it uses this method to handle that.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_confirmation(): void {

		$saved_swap = $this->get_saved_swap(wu_request('swap'));

		$membership = $this->payment ? $this->payment->get_membership() : wu_get_membership_by_hash(wu_request('membership'));

		if ($saved_swap && $membership) {
			if ($saved_swap->get_cart_type() === 'downgrade') {
				$membership->schedule_swap($saved_swap);
			} else {
				$membership->swap($saved_swap);
			}

			$membership->save();

			$redirect_url = $this->get_return_url();

			wp_safe_redirect($redirect_url);

			exit;
		}
	}

	/**
	 * Add credit card fields.
	 *
	 * @since 2.0.0
	 */
	public function fields(): string {

		$message = __('You will be redirected to a checkout to complete the purchase.', 'multisite-ultimate');

		return sprintf('<p class="wu-p-4 wu-bg-yellow-200">%s</p>', $message);
	}

	/**
	 * Returns the payment methods.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function payment_methods() {

		$fields = [];

		$card_options = $this->get_saved_card_options();

		if ($card_options) {
			foreach ($card_options as $payment_method => $card) {
				$fields = [
					"payment_method_{$payment_method}" => [
						'type'          => 'text-display',
						'title'         => __('Saved Cards', 'multisite-ultimate'),
						'display_value' => $card,
					],
				];
			}
		}

		return $fields;
	}

	/**
	 * Get the saved Stripe payment methods for a given user ID.
	 *
	 * @since 2.0.0
	 *
	 * @throws \Exception, When info is wrong.
	 * @throws \Exception When info is wrong 2.
	 * @return PaymentMethod[]|array
	 */
	public function get_user_saved_payment_methods() {

		$customer = wu_get_current_customer();

		if ( ! $customer) {
			return [];
		}

		$customer_id = $customer->get_id();

		try {
			/*
			 * Declare static to prevent multiple calls.
			 */
			static $existing_payment_methods;

			if ( ! is_null($existing_payment_methods) && array_key_exists($customer_id, $existing_payment_methods)) {
				return $existing_payment_methods[ $customer_id ];
			}

			$customer_payment_methods = [];

			$stripe_customer_id = \WP_Ultimo\Models\Membership::query(
				[
					'customer_id' => $customer_id,
					'search'      => 'cus_*',
					'fields'      => ['gateway_customer_id'],
				]
			);

			$stripe_customer_id = current(array_column($stripe_customer_id, 'gateway_customer_id'));

			/**
			 * Ensure the correct api keys are set
			 */
			$this->setup_api_keys();

			$payment_methods = $this->get_stripe_client()->paymentMethods->all(
				[
					'customer' => $stripe_customer_id,
					'type'     => 'card',
				]
			);

			foreach ($payment_methods->data as $payment_method) {
				$customer_payment_methods[ $payment_method->id ] = $payment_method;
			}

			$existing_payment_methods[ $customer_id ] = $customer_payment_methods;

			return $existing_payment_methods[ $customer_id ];
		} catch (\Throwable $exception) {
			return [];
		}
	}
}
