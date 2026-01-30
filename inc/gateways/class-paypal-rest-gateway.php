<?php
/**
 * PayPal REST Gateway.
 *
 * Modern PayPal integration using the REST API with OAuth authentication.
 * Supports PayPal Commerce Platform (PPCP) with "Connect with PayPal" onboarding.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use Psr\Log\LogLevel;
use WP_Ultimo\Gateways\Base_PayPal_Gateway;
use WP_Ultimo\Gateways\PayPal_OAuth_Handler;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * PayPal REST API Gateway
 *
 * @since 2.0.0
 */
class PayPal_REST_Gateway extends Base_PayPal_Gateway {

	/**
	 * Holds the ID of this gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'paypal-rest';

	/**
	 * Backwards compatibility for the old notify ajax url.
	 *
	 * @since 2.0.0
	 * @var bool|string
	 */
	protected $backwards_compatibility_v1_id = false;

	/**
	 * Client ID for REST API authentication.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $client_id = '';

	/**
	 * Client secret for REST API authentication.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $client_secret = '';

	/**
	 * Merchant ID from OAuth onboarding.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $merchant_id = '';

	/**
	 * Cached access token.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $access_token = '';

	/**
	 * Initialization code.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->test_mode = (bool) (int) wu_get_setting('paypal_rest_sandbox_mode', true);

		$this->load_credentials();
	}

	/**
	 * Load credentials from settings.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_credentials(): void {

		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';

		// First check for OAuth-connected merchant
		$this->merchant_id = wu_get_setting("paypal_rest_{$mode_prefix}_merchant_id", '');

		// Load client credentials (either from OAuth or manual entry)
		$this->client_id     = wu_get_setting("paypal_rest_{$mode_prefix}_client_id", '');
		$this->client_secret = wu_get_setting("paypal_rest_{$mode_prefix}_client_secret", '');
	}

	/**
	 * Set the test mode and reload credentials.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $test_mode Whether to use sandbox mode.
	 * @return void
	 */
	public function set_test_mode(bool $test_mode): void {

		$this->test_mode    = $test_mode;
		$this->access_token = ''; // Clear cached token

		$this->load_credentials();
	}

	/**
	 * Adds the necessary hooks for the REST PayPal gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks(): void {

		parent::hooks();

		// Initialize OAuth handler
		PayPal_OAuth_Handler::get_instance()->init();

		// Handle webhook installation after settings save
		add_action('wu_after_save_settings', [$this, 'maybe_install_webhook'], 10, 3);

		// AJAX handler for manual webhook installation
		add_action('wp_ajax_wu_paypal_install_webhook', [$this, 'ajax_install_webhook']);

		// Display OAuth notices
		add_action('admin_notices', [PayPal_OAuth_Handler::get_instance(), 'display_oauth_notices']);
	}

	/**
	 * Checks if PayPal REST is properly configured.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_configured(): bool {

		// Either OAuth connected OR manual credentials
		$has_oauth  = ! empty($this->merchant_id);
		$has_manual = ! empty($this->client_id) && ! empty($this->client_secret);

		return $has_oauth || $has_manual;
	}

	/**
	 * Returns the connection status for display in settings.
	 *
	 * @since 2.0.0
	 * @return array{connected: bool, message: string, details: array}
	 */
	public function get_connection_status(): array {

		$oauth_handler = PayPal_OAuth_Handler::get_instance();
		$is_sandbox    = $this->test_mode;

		if ($oauth_handler->is_merchant_connected($is_sandbox)) {
			$merchant_details = $oauth_handler->get_merchant_details($is_sandbox);

			return [
				'connected' => true,
				'message'   => __('Connected via PayPal', 'ultimate-multisite'),
				'details'   => [
					'mode'         => $is_sandbox ? 'sandbox' : 'live',
					'merchant_id'  => $merchant_details['merchant_id'],
					'email'        => $merchant_details['merchant_email'],
					'connected_at' => $merchant_details['connection_date'],
					'method'       => 'oauth',
				],
			];
		}

		if (! empty($this->client_id) && ! empty($this->client_secret)) {
			return [
				'connected' => true,
				'message'   => __('Connected via API credentials', 'ultimate-multisite'),
				'details'   => [
					'mode'      => $is_sandbox ? 'sandbox' : 'live',
					'client_id' => substr($this->client_id, 0, 20) . '...',
					'method'    => 'manual',
				],
			];
		}

		return [
			'connected' => false,
			'message'   => __('Not connected', 'ultimate-multisite'),
			'details'   => [
				'mode' => $is_sandbox ? 'sandbox' : 'live',
			],
		];
	}

	/**
	 * Get an access token for API requests.
	 *
	 * @since 2.0.0
	 * @return string|\WP_Error Access token or error.
	 */
	protected function get_access_token() {

		if (! empty($this->access_token)) {
			return $this->access_token;
		}

		// Check for cached token
		$cache_key    = 'wu_paypal_rest_access_token_' . ($this->test_mode ? 'sandbox' : 'live');
		$cached_token = get_site_transient($cache_key);

		if ($cached_token) {
			$this->access_token = $cached_token;
			return $this->access_token;
		}

		if (empty($this->client_id) || empty($this->client_secret)) {
			return new \WP_Error(
				'wu_paypal_missing_credentials',
				__('PayPal API credentials not configured.', 'ultimate-multisite')
			);
		}

		$response = wp_remote_post(
			$this->get_api_base_url() . '/v1/oauth2/token',
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				],
				'body'    => 'grant_type=client_credentials',
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			$this->log('Failed to get access token: ' . $response->get_error_message(), LogLevel::ERROR);
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$code = wp_remote_retrieve_response_code($response);

		if (200 !== $code || empty($body['access_token'])) {
			$error_msg = $body['error_description'] ?? __('Failed to obtain access token', 'ultimate-multisite');
			$this->log('Failed to get access token: ' . $error_msg, LogLevel::ERROR);
			return new \WP_Error('wu_paypal_token_error', $error_msg);
		}

		// Cache the token (expires_in is in seconds, subtract 5 minutes for safety)
		$expires_in = isset($body['expires_in']) ? (int) $body['expires_in'] - 300 : 3300;
		set_site_transient($cache_key, $body['access_token'], $expires_in);

		$this->access_token = $body['access_token'];

		return $this->access_token;
	}

	/**
	 * Make an API request to PayPal REST API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint API endpoint (relative to base URL).
	 * @param array  $data     Request data.
	 * @param string $method   HTTP method.
	 * @return array|\WP_Error Response data or error.
	 */
	protected function api_request(string $endpoint, array $data = [], string $method = 'POST') {

		$access_token = $this->get_access_token();

		if (is_wp_error($access_token)) {
			return $access_token;
		}

		$headers = [
			'Authorization' => 'Bearer ' . $access_token,
			'Content-Type'  => 'application/json',
		];

		$headers = $this->add_partner_attribution_header($headers);

		$args = [
			'headers' => $headers,
			'method'  => $method,
			'timeout' => 45,
		];

		if (! empty($data) && in_array($method, ['POST', 'PATCH', 'PUT'], true)) {
			$args['body'] = wp_json_encode($data);
		}

		$url = $this->get_api_base_url() . $endpoint;

		$this->log(sprintf('API Request: %s %s', $method, $endpoint));

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			$this->log('API Request failed: ' . $response->get_error_message(), LogLevel::ERROR);
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$code = wp_remote_retrieve_response_code($response);

		if ($code >= 400) {
			$error_msg = $body['message'] ?? ($body['error_description'] ?? __('API request failed', 'ultimate-multisite'));
			$this->log(sprintf('API Error (%d): %s', $code, wp_json_encode($body)), LogLevel::ERROR);
			return new \WP_Error(
				'wu_paypal_api_error',
				$error_msg,
				[
					'status'   => $code,
					'response' => $body,
				]
			);
		}

		return $body ?? [];
	}

	/**
	 * Process a checkout.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment    The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart       The cart object.
	 * @param string                       $type       The checkout type.
	 * @return void
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type): void {

		$should_auto_renew = $cart->should_auto_renew();
		$is_recurring      = $cart->has_recurring();

		if ($should_auto_renew && $is_recurring) {
			$this->create_subscription($payment, $membership, $customer, $cart, $type);
		} else {
			$this->create_order($payment, $membership, $customer, $cart, $type);
		}
	}

	/**
	 * Create a PayPal subscription for recurring payments.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment    The payment.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer.
	 * @param \WP_Ultimo\Checkout\Cart     $cart       The cart object.
	 * @param string                       $type       The checkout type.
	 * @return void
	 */
	protected function create_subscription($payment, $membership, $customer, $cart, $type): void {

		$currency    = strtoupper($payment->get_currency());
		$description = $this->get_subscription_description($cart);

		// First, create or get the billing plan
		$plan_id = $this->get_or_create_plan($cart, $currency);

		if (is_wp_error($plan_id)) {
			wp_die(
				esc_html($plan_id->get_error_message()),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		// Create the subscription
		$subscription_data = [
			'plan_id'             => $plan_id,
			'subscriber'          => [
				'name'          => [
					'given_name' => $customer->get_display_name(),
				],
				'email_address' => $customer->get_email_address(),
			],
			'application_context' => [
				'brand_name'          => wu_get_setting('company_name', get_network_option(null, 'site_name')),
				'locale'              => str_replace('_', '-', get_locale()),
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'SUBSCRIBE_NOW',
				'return_url'          => $this->get_confirm_url(),
				'cancel_url'          => $this->get_cancel_url(),
			],
			'custom_id'           => sprintf('%s|%s|%s', $payment->get_id(), $membership->get_id(), $customer->get_id()),
		];

		// Handle initial payment if different from recurring
		$initial_amount   = $payment->get_total();
		$recurring_amount = $cart->get_recurring_total();

		if ($initial_amount > 0 && abs($initial_amount - $recurring_amount) > 0.01) {
			// Add setup fee for the difference
			$setup_fee = $initial_amount - $recurring_amount;
			if ($setup_fee > 0) {
				$subscription_data['plan'] = [
					'payment_preferences' => [
						'setup_fee' => [
							'value'         => number_format($setup_fee, 2, '.', ''),
							'currency_code' => $currency,
						],
					],
				];
			}
		}

		// Handle trial periods
		if ($membership->is_trialing()) {
			$trial_end = $membership->get_date_trial_end();
			if ($trial_end) {
				$subscription_data['start_time'] = gmdate('Y-m-d\TH:i:s\Z', strtotime($trial_end));
			}
		}

		/**
		 * Filter subscription data before creating.
		 *
		 * @since 2.0.0
		 *
		 * @param array $subscription_data The subscription data.
		 * @param \WP_Ultimo\Models\Membership $membership The membership.
		 * @param \WP_Ultimo\Checkout\Cart $cart The cart.
		 */
		$subscription_data = apply_filters('wu_paypal_rest_subscription_data', $subscription_data, $membership, $cart);

		$result = $this->api_request('/v1/billing/subscriptions', $subscription_data);

		if (is_wp_error($result)) {
			wp_die(
				esc_html($result->get_error_message()),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		// Find approval URL
		$approval_url = '';
		foreach ($result['links'] ?? [] as $link) {
			if ('approve' === $link['rel']) {
				$approval_url = $link['href'];
				break;
			}
		}

		if (empty($approval_url)) {
			wp_die(
				esc_html__('Failed to get PayPal approval URL', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		// Store subscription ID for confirmation
		$membership->set_gateway_subscription_id($result['id']);
		$membership->save();

		$this->log(sprintf('Subscription created: %s. Redirecting to approval.', $result['id']));

		// Redirect to PayPal for approval
		wp_redirect($approval_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- PayPal external URL
		exit;
	}

	/**
	 * Get or create a billing plan for the subscription.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart     The cart object.
	 * @param string                   $currency The currency code.
	 * @return string|\WP_Error Plan ID or error.
	 */
	protected function get_or_create_plan($cart, string $currency) {

		// Generate a unique plan key based on cart contents
		$plan_key = 'wu_paypal_plan_' . md5(
			wp_json_encode(
				[
					'amount'        => $cart->get_recurring_total(),
					'currency'      => $currency,
					'duration'      => $cart->get_duration(),
					'duration_unit' => $cart->get_duration_unit(),
				]
			)
		);

		// Check if we already have this plan
		$existing_plan_id = get_site_option($plan_key);
		if ($existing_plan_id) {
			// Verify the plan still exists
			$plan = $this->api_request('/v1/billing/plans/' . $existing_plan_id, [], 'GET');
			if (! is_wp_error($plan) && isset($plan['id'])) {
				return $plan['id'];
			}
		}

		// First create a product
		$product_name = wu_get_setting('company_name', get_network_option(null, 'site_name')) . ' - ' . $cart->get_cart_descriptor();

		$product_data = [
			'name'        => substr($product_name, 0, 127),
			'description' => substr($this->get_subscription_description($cart), 0, 256),
			'type'        => 'SERVICE',
			'category'    => 'SOFTWARE',
		];

		$product = $this->api_request('/v1/catalogs/products', $product_data);

		if (is_wp_error($product)) {
			return $product;
		}

		// Convert duration unit to PayPal format
		$interval_unit   = strtoupper($cart->get_duration_unit());
		$interval_map    = [
			'DAY'   => 'DAY',
			'WEEK'  => 'WEEK',
			'MONTH' => 'MONTH',
			'YEAR'  => 'YEAR',
		];
		$paypal_interval = $interval_map[ $interval_unit ] ?? 'MONTH';

		// Create the billing plan
		$plan_data = [
			'product_id'          => $product['id'],
			'name'                => substr($product_name, 0, 127),
			'description'         => substr($this->get_subscription_description($cart), 0, 127),
			'billing_cycles'      => [
				[
					'frequency'      => [
						'interval_unit'  => $paypal_interval,
						'interval_count' => $cart->get_duration(),
					],
					'tenure_type'    => 'REGULAR',
					'sequence'       => 1,
					'total_cycles'   => 0, // 0 = unlimited
					'pricing_scheme' => [
						'fixed_price' => [
							'value'         => number_format($cart->get_recurring_total(), 2, '.', ''),
							'currency_code' => $currency,
						],
					],
				],
			],
			'payment_preferences' => [
				'auto_bill_outstanding'     => true,
				'payment_failure_threshold' => 3,
			],
		];

		$plan = $this->api_request('/v1/billing/plans', $plan_data);

		if (is_wp_error($plan)) {
			return $plan;
		}

		// Cache the plan ID
		update_site_option($plan_key, $plan['id']);

		$this->log(sprintf('Billing plan created: %s', $plan['id']));

		return $plan['id'];
	}

	/**
	 * Create a PayPal order for one-time payments.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment    The payment.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer.
	 * @param \WP_Ultimo\Checkout\Cart     $cart       The cart object.
	 * @param string                       $type       The checkout type.
	 * @return void
	 */
	protected function create_order($payment, $membership, $customer, $cart, $type): void {

		$currency    = strtoupper($payment->get_currency());
		$description = $this->get_subscription_description($cart);

		$order_data = [
			'intent'              => 'CAPTURE',
			'purchase_units'      => [
				[
					'reference_id' => $payment->get_hash(),
					'description'  => substr($description, 0, 127),
					'custom_id'    => sprintf('%s|%s|%s', $payment->get_id(), $membership->get_id(), $customer->get_id()),
					'amount'       => [
						'currency_code' => $currency,
						'value'         => number_format($payment->get_total(), 2, '.', ''),
						'breakdown'     => [
							'item_total' => [
								'currency_code' => $currency,
								'value'         => number_format($payment->get_subtotal(), 2, '.', ''),
							],
							'tax_total'  => [
								'currency_code' => $currency,
								'value'         => number_format($payment->get_tax_total(), 2, '.', ''),
							],
						],
					],
					'items'        => $this->build_order_items($cart, $currency),
				],
			],
			'application_context' => [
				'brand_name'          => wu_get_setting('company_name', get_network_option(null, 'site_name')),
				'locale'              => str_replace('_', '-', get_locale()),
				'shipping_preference' => 'NO_SHIPPING',
				'user_action'         => 'PAY_NOW',
				'return_url'          => $this->get_confirm_url(),
				'cancel_url'          => $this->get_cancel_url(),
			],
		];

		/**
		 * Filter order data before creating.
		 *
		 * @since 2.0.0
		 *
		 * @param array $order_data The order data.
		 * @param \WP_Ultimo\Models\Payment $payment The payment.
		 * @param \WP_Ultimo\Checkout\Cart $cart The cart.
		 */
		$order_data = apply_filters('wu_paypal_rest_order_data', $order_data, $payment, $cart);

		$result = $this->api_request('/v2/checkout/orders', $order_data);

		if (is_wp_error($result)) {
			wp_die(
				esc_html($result->get_error_message()),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		// Find approval URL
		$approval_url = '';
		foreach ($result['links'] ?? [] as $link) {
			if ('approve' === $link['rel']) {
				$approval_url = $link['href'];
				break;
			}
		}

		if (empty($approval_url)) {
			wp_die(
				esc_html__('Failed to get PayPal approval URL', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		// Store order ID for confirmation
		$payment->set_gateway_payment_id($result['id']);
		$payment->save();

		$this->log(sprintf('Order created: %s. Redirecting to approval.', $result['id']));

		// Redirect to PayPal for approval
		wp_redirect($approval_url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- PayPal external URL
		exit;
	}

	/**
	 * Build order items array for PayPal.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart     The cart object.
	 * @param string                   $currency The currency code.
	 * @return array
	 */
	protected function build_order_items($cart, string $currency): array {

		$items = [];

		foreach ($cart->get_line_items() as $line_item) {
			$items[] = [
				'name'        => substr($line_item->get_title(), 0, 127),
				'description' => substr($line_item->get_description(), 0, 127) ?: null,
				'unit_amount' => [
					'currency_code' => $currency,
					'value'         => number_format($line_item->get_unit_price(), 2, '.', ''),
				],
				'quantity'    => (string) $line_item->get_quantity(),
				'category'    => 'DIGITAL_GOODS',
			];
		}

		return $items;
	}

	/**
	 * Process confirmation after PayPal approval.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_confirmation(): void {

		$token           = sanitize_text_field(wu_request('token', ''));
		$subscription_id = sanitize_text_field(wu_request('subscription_id', ''));

		if (! empty($subscription_id)) {
			$this->confirm_subscription($subscription_id);
		} elseif (! empty($token)) {
			$this->confirm_order($token);
		} else {
			wp_die(
				esc_html__('Invalid PayPal confirmation', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}
	}

	/**
	 * Confirm a subscription after PayPal approval.
	 *
	 * @since 2.0.0
	 *
	 * @param string $subscription_id The PayPal subscription ID.
	 * @return void
	 */
	protected function confirm_subscription(string $subscription_id): void {

		// Get subscription details
		$subscription = $this->api_request('/v1/billing/subscriptions/' . $subscription_id, [], 'GET');

		if (is_wp_error($subscription)) {
			wp_die(
				esc_html($subscription->get_error_message()),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		// Parse custom_id to get our IDs
		$custom_parts = explode('|', $subscription['custom_id'] ?? '');
		if (count($custom_parts) !== 3) {
			wp_die(
				esc_html__('Invalid subscription data', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		[$payment_id, $membership_id, $customer_id] = $custom_parts;

		$payment    = wu_get_payment($payment_id);
		$membership = wu_get_membership($membership_id);

		if (! $payment || ! $membership) {
			wp_die(
				esc_html__('Payment or membership not found', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		// Check subscription status
		if ('ACTIVE' === $subscription['status'] || 'APPROVED' === $subscription['status']) {
			// Update membership
			$membership->set_gateway('paypal-rest');
			$membership->set_gateway_subscription_id($subscription_id);
			$membership->set_gateway_customer_id($subscription['subscriber']['payer_id'] ?? '');
			$membership->set_auto_renew(true);

			// Handle based on status
			if ('ACTIVE' === $subscription['status']) {
				// Payment already processed
				$payment->set_status(Payment_Status::COMPLETED);
				$membership->renew(false);
			} else {
				// Will be activated on first payment webhook
				$payment->set_status(Payment_Status::PENDING);
			}

			$payment->set_gateway('paypal-rest');
			$payment->save();
			$membership->save();

			$this->log(sprintf('Subscription confirmed: %s, Status: %s', $subscription_id, $subscription['status']));

			$this->payment = $payment;
			wp_safe_redirect($this->get_return_url());
			exit;
		}

		wp_die(
			// translators: %s is the subscription status
			esc_html(sprintf(__('Subscription not approved. Status: %s', 'ultimate-multisite'), $subscription['status'])),
			esc_html__('PayPal Error', 'ultimate-multisite'),
			['back_link' => true]
		);
	}

	/**
	 * Confirm an order after PayPal approval.
	 *
	 * @since 2.0.0
	 *
	 * @param string $token The PayPal order token.
	 * @return void
	 */
	protected function confirm_order(string $token): void {

		// Capture the order
		$capture = $this->api_request('/v2/checkout/orders/' . $token . '/capture', []);

		if (is_wp_error($capture)) {
			wp_die(
				esc_html($capture->get_error_message()),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		if ('COMPLETED' !== $capture['status']) {
			wp_die(
				// translators: %s is the order status
				esc_html(sprintf(__('Order not completed. Status: %s', 'ultimate-multisite'), $capture['status'])),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		// Parse custom_id
		$purchase_unit = $capture['purchase_units'][0] ?? [];
		$custom_parts  = explode('|', $purchase_unit['payments']['captures'][0]['custom_id'] ?? '');

		if (count($custom_parts) !== 3) {
			wp_die(
				esc_html__('Invalid order data', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		[$payment_id, $membership_id, $customer_id] = $custom_parts;

		$payment    = wu_get_payment($payment_id);
		$membership = wu_get_membership($membership_id);

		if (! $payment || ! $membership) {
			wp_die(
				esc_html__('Payment or membership not found', 'ultimate-multisite'),
				esc_html__('PayPal Error', 'ultimate-multisite'),
				['back_link' => true]
			);
		}

		// Get transaction ID from capture
		$transaction_id = $purchase_unit['payments']['captures'][0]['id'] ?? $token;

		// Update payment
		$payment->set_gateway('paypal-rest');
		$payment->set_gateway_payment_id($transaction_id);
		$payment->set_status(Payment_Status::COMPLETED);
		$payment->save();

		// Update membership
		$membership->set_gateway('paypal-rest');
		$membership->set_gateway_customer_id($capture['payer']['payer_id'] ?? '');
		$membership->add_to_times_billed(1);
		$membership->renew(false);

		$this->log(sprintf('Order captured: %s, Transaction: %s', $token, $transaction_id));

		$this->payment = $payment;
		wp_safe_redirect($this->get_return_url());
		exit;
	}

	/**
	 * Process cancellation of a subscription.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer.
	 * @return void
	 */
	public function process_cancellation($membership, $customer): void {

		$subscription_id = $membership->get_gateway_subscription_id();

		if (empty($subscription_id)) {
			return;
		}

		$result = $this->api_request(
			'/v1/billing/subscriptions/' . $subscription_id . '/cancel',
			['reason' => __('Cancelled by user', 'ultimate-multisite')]
		);

		if (is_wp_error($result)) {
			$this->log('Failed to cancel subscription: ' . $result->get_error_message(), LogLevel::ERROR);
			return;
		}

		$this->log(sprintf('Subscription cancelled: %s', $subscription_id));
	}

	/**
	 * Process refund.
	 *
	 * @since 2.0.0
	 *
	 * @param float                        $amount     The amount to refund.
	 * @param \WP_Ultimo\Models\Payment    $payment    The payment.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer.
	 * @return void
	 * @throws \Exception When refund fails.
	 */
	public function process_refund($amount, $payment, $membership, $customer): void {

		$capture_id = $payment->get_gateway_payment_id();

		if (empty($capture_id)) {
			throw new \Exception(esc_html__('No capture ID found for this payment.', 'ultimate-multisite'));
		}

		$refund_data = [];

		// Only include amount for partial refunds
		if ($amount < $payment->get_total()) {
			$refund_data['amount'] = [
				'value'         => number_format($amount, 2, '.', ''),
				'currency_code' => strtoupper($payment->get_currency()),
			];
		}

		$result = $this->api_request('/v2/payments/captures/' . $capture_id . '/refund', $refund_data);

		if (is_wp_error($result)) {
			throw new \Exception(esc_html($result->get_error_message()));
		}

		$this->log(sprintf('Refund processed: %s for capture %s', $result['id'] ?? 'unknown', $capture_id));
	}

	/**
	 * Reflects membership changes on the gateway.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer   The customer.
	 * @return bool|\WP_Error
	 */
	public function process_membership_update(&$membership, $customer) {

		$subscription_id = $membership->get_gateway_subscription_id();

		if (empty($subscription_id)) {
			return new \WP_Error(
				'wu_paypal_no_subscription',
				__('No subscription ID found for this membership.', 'ultimate-multisite')
			);
		}

		// Note: PayPal subscription updates are limited
		// For significant changes, may need to cancel and recreate
		$this->log(sprintf('Membership update requested for subscription: %s', $subscription_id));

		return true;
	}

	/**
	 * Adds the PayPal REST Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings(): void {

		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_header',
			[
				'title'           => __('PayPal', 'ultimate-multisite'),
				'desc'            => __('Modern PayPal integration with Connect with PayPal onboarding.', 'ultimate-multisite'),
				'type'            => 'header',
				'show_as_submenu' => true,
				'require'         => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Connection status display
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_connection_status',
			[
				'title'   => __('Connection Status', 'ultimate-multisite'),
				'type'    => 'note',
				'desc'    => [$this, 'render_connection_status'],
				'require' => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Sandbox mode toggle
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_sandbox_mode',
			[
				'title'     => __('PayPal Sandbox Mode', 'ultimate-multisite'),
				'desc'      => __('Enable sandbox mode for testing.', 'ultimate-multisite'),
				'type'      => 'toggle',
				'default'   => 1,
				'html_attr' => [
					'v-model' => 'paypal_rest_sandbox_mode',
				],
				'require'   => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Connect with PayPal button
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_connect_button',
			[
				'title'   => __('Connect with PayPal', 'ultimate-multisite'),
				'type'    => 'note',
				'desc'    => [$this, 'render_connect_button'],
				'require' => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Advanced/Manual credentials header
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_manual_header',
			[
				'title'   => __('Manual Configuration', 'ultimate-multisite'),
				'desc'    => __('Advanced: Enter API credentials manually if Connect with PayPal is not available.', 'ultimate-multisite'),
				'type'    => 'header',
				'require' => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Sandbox Client ID
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_sandbox_client_id',
			[
				'title'       => __('Sandbox Client ID', 'ultimate-multisite'),
				'placeholder' => __('e.g. AX7MV...', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'          => 'paypal-rest',
					'paypal_rest_sandbox_mode' => 1,
				],
			]
		);

		// Sandbox Client Secret
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_sandbox_client_secret',
			[
				'title'       => __('Sandbox Client Secret', 'ultimate-multisite'),
				'placeholder' => __('e.g. EK4jT...', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'          => 'paypal-rest',
					'paypal_rest_sandbox_mode' => 1,
				],
			]
		);

		// Live Client ID
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_live_client_id',
			[
				'title'       => __('Live Client ID', 'ultimate-multisite'),
				'placeholder' => __('e.g. AX7MV...', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'          => 'paypal-rest',
					'paypal_rest_sandbox_mode' => 0,
				],
			]
		);

		// Live Client Secret
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_live_client_secret',
			[
				'title'       => __('Live Client Secret', 'ultimate-multisite'),
				'placeholder' => __('e.g. EK4jT...', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'          => 'paypal-rest',
					'paypal_rest_sandbox_mode' => 0,
				],
			]
		);

		// Webhook URL display
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_webhook_url',
			[
				'title'      => __('Webhook URL', 'ultimate-multisite'),
				'desc'       => __('Webhooks are automatically configured when you connect your PayPal account.', 'ultimate-multisite'),
				'type'       => 'text-display',
				'copy'       => true,
				'value'      => $this->get_webhook_listener_url(),
				'capability' => 'manage_api_keys',
				'require'    => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);

		// Webhook status
		wu_register_settings_field(
			'payment-gateways',
			'paypal_rest_webhook_status',
			[
				'title'   => __('Webhook Status', 'ultimate-multisite'),
				'type'    => 'note',
				'desc'    => [$this, 'render_webhook_status'],
				'require' => [
					'active_gateways' => 'paypal-rest',
				],
			]
		);
	}

	/**
	 * Render connection status HTML.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_connection_status(): string {

		$status = $this->get_connection_status();

		if ($status['connected']) {
			$mode_label = 'sandbox' === $status['details']['mode']
				? __('Sandbox', 'ultimate-multisite')
				: __('Live', 'ultimate-multisite');

			$email = $status['details']['email'] ?? ($status['details']['client_id'] ?? '');

			return sprintf(
				'<div class="wu-p-4 wu-bg-green-100 wu-rounded wu-text-green-800">
					<span class="dashicons dashicons-yes-alt wu-mr-2"></span>
					<strong>%s</strong> (%s)<br>
					<span class="wu-text-sm">%s</span>
				</div>',
				esc_html($status['message']),
				esc_html($mode_label),
				esc_html($email)
			);
		}

		return sprintf(
			'<div class="wu-p-4 wu-bg-yellow-100 wu-rounded wu-text-yellow-800">
				<span class="dashicons dashicons-warning wu-mr-2"></span>
				%s
			</div>',
			esc_html__('Not connected. Use Connect with PayPal below or enter credentials manually.', 'ultimate-multisite')
		);
	}

	/**
	 * Render the Connect with PayPal button.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_connect_button(): string {

		$nonce        = wp_create_nonce('wu_paypal_oauth');
		$is_sandbox   = $this->test_mode;
		$oauth        = PayPal_OAuth_Handler::get_instance();
		$is_connected = $oauth->is_merchant_connected($is_sandbox);

		if ($is_connected) {
			return sprintf(
				'<button type="button" class="button button-secondary wu-paypal-disconnect" data-nonce="%s">
					%s
				</button>
				<p class="description">%s</p>',
				esc_attr($nonce),
				esc_html__('Disconnect PayPal', 'ultimate-multisite'),
				esc_html__('This will remove the PayPal connection. Existing subscriptions will continue to work.', 'ultimate-multisite')
			);
		}

		return sprintf(
			'<button type="button" class="button button-primary wu-paypal-connect" data-nonce="%s" data-sandbox="%d">
				<span class="dashicons dashicons-paypal wu-mr-1"></span>
				%s
			</button>
			<p class="description">%s</p>
			<script>
			jQuery(function($) {
				$(".wu-paypal-connect").on("click", function(e) {
					e.preventDefault();
					var $btn = $(this);
					$btn.prop("disabled", true).text("%s");

					$.post(ajaxurl, {
						action: "wu_paypal_connect",
						nonce: $btn.data("nonce"),
						sandbox_mode: $btn.data("sandbox")
					}, function(response) {
						if (response.success) {
							window.location.href = response.data.redirect_url;
						} else {
							alert(response.data.message || "%s");
							$btn.prop("disabled", false).html(\'<span class="dashicons dashicons-paypal wu-mr-1"></span> %s\');
						}
					}).fail(function() {
						alert("%s");
						$btn.prop("disabled", false).html(\'<span class="dashicons dashicons-paypal wu-mr-1"></span> %s\');
					});
				});

				$(".wu-paypal-disconnect").on("click", function(e) {
					e.preventDefault();
					if (!confirm("%s")) return;

					var $btn = $(this);
					$btn.prop("disabled", true);

					$.post(ajaxurl, {
						action: "wu_paypal_disconnect",
						nonce: $btn.data("nonce")
					}, function(response) {
						window.location.reload();
					});
				});
			});
			</script>',
			esc_attr($nonce),
			$is_sandbox ? 1 : 0,
			esc_html__('Connect with PayPal', 'ultimate-multisite'),
			esc_html__('Click to securely connect your PayPal account.', 'ultimate-multisite'),
			esc_js(__('Connecting...', 'ultimate-multisite')),
			esc_js(__('Connection failed. Please try again.', 'ultimate-multisite')),
			esc_js(__('Connect with PayPal', 'ultimate-multisite')),
			esc_js(__('Connection failed. Please try again.', 'ultimate-multisite')),
			esc_js(__('Connect with PayPal', 'ultimate-multisite')),
			esc_js(__('Are you sure you want to disconnect PayPal?', 'ultimate-multisite'))
		);
	}

	/**
	 * Render webhook status HTML.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_webhook_status(): string {

		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';
		$mode_label  = $this->test_mode
			? __('Sandbox', 'ultimate-multisite')
			: __('Live', 'ultimate-multisite');

		$webhook_id = wu_get_setting("paypal_rest_{$mode_prefix}_webhook_id", '');

		if (! empty($webhook_id)) {
			return sprintf(
				'<div class="wu-p-4 wu-bg-green-100 wu-rounded wu-text-green-800">
					<span class="dashicons dashicons-yes-alt wu-mr-2"></span>
					<strong>%s</strong><br>
					<span class="wu-text-sm">%s: %s</span>
				</div>',
				esc_html__('Webhook configured', 'ultimate-multisite'),
				esc_html($mode_label),
				esc_html($webhook_id)
			);
		}

		// Check if we have credentials but no webhook
		if ($this->is_configured()) {
			return sprintf(
				'<div class="wu-p-4 wu-bg-yellow-100 wu-rounded wu-text-yellow-800">
					<span class="dashicons dashicons-warning wu-mr-2"></span>
					%s<br>
					<button type="button" class="button button-secondary wu-mt-2 wu-paypal-install-webhook" data-nonce="%s">
						%s
					</button>
				</div>
				<script>
				jQuery(function($) {
					$(".wu-paypal-install-webhook").on("click", function(e) {
						e.preventDefault();
						var $btn = $(this);
						$btn.prop("disabled", true).text("%s");

						$.post(ajaxurl, {
							action: "wu_paypal_install_webhook",
							nonce: $btn.data("nonce")
						}, function(response) {
							if (response.success) {
								window.location.reload();
							} else {
								alert(response.data.message || "%s");
								$btn.prop("disabled", false).text("%s");
							}
						}).fail(function() {
							alert("%s");
							$btn.prop("disabled", false).text("%s");
						});
					});
				});
				</script>',
				esc_html__('Webhook not configured. Click below to configure automatically.', 'ultimate-multisite'),
				esc_attr(wp_create_nonce('wu_paypal_webhook')),
				esc_html__('Configure Webhook', 'ultimate-multisite'),
				esc_js(__('Configuring...', 'ultimate-multisite')),
				esc_js(__('Failed to configure webhook. Please try again.', 'ultimate-multisite')),
				esc_js(__('Configure Webhook', 'ultimate-multisite')),
				esc_js(__('Failed to configure webhook. Please try again.', 'ultimate-multisite')),
				esc_js(__('Configure Webhook', 'ultimate-multisite'))
			);
		}

		return sprintf(
			'<div class="wu-p-4 wu-bg-gray-100 wu-rounded wu-text-gray-600">
				<span class="dashicons dashicons-info wu-mr-2"></span>
				%s
			</div>',
			esc_html__('Connect with PayPal to automatically configure webhooks.', 'ultimate-multisite')
		);
	}

	/**
	 * Maybe install webhook after settings save.
	 *
	 * Automatically creates a webhook in PayPal when credentials are configured.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings         The final settings array.
	 * @param array $settings_to_save Settings being updated.
	 * @param array $saved_settings   Original settings.
	 * @return void
	 */
	public function maybe_install_webhook($settings, $settings_to_save, $saved_settings): void {

		$active_gateways = (array) wu_get_isset($settings_to_save, 'active_gateways', []);

		if (! in_array('paypal-rest', $active_gateways, true)) {
			return;
		}

		// Check if settings changed
		$changed_settings = [
			$settings['paypal_rest_sandbox_mode'] ?? '',
			$settings['paypal_rest_sandbox_client_id'] ?? '',
			$settings['paypal_rest_sandbox_client_secret'] ?? '',
			$settings['paypal_rest_live_client_id'] ?? '',
			$settings['paypal_rest_live_client_secret'] ?? '',
		];

		$original_settings = [
			$saved_settings['paypal_rest_sandbox_mode'] ?? '',
			$saved_settings['paypal_rest_sandbox_client_id'] ?? '',
			$saved_settings['paypal_rest_sandbox_client_secret'] ?? '',
			$saved_settings['paypal_rest_live_client_id'] ?? '',
			$saved_settings['paypal_rest_live_client_secret'] ?? '',
		];

		// Only install if settings changed
		if ($changed_settings === $original_settings) {
			return;
		}

		// Reload credentials with new settings
		$this->test_mode = (bool) (int) ($settings['paypal_rest_sandbox_mode'] ?? true);
		$this->load_credentials();

		// Check if we have credentials
		if (! $this->is_configured()) {
			return;
		}

		$this->install_webhook();
	}

	/**
	 * Install webhook in PayPal.
	 *
	 * @since 2.0.0
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	public function install_webhook() {

		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';

		// Check if we already have a webhook installed
		$existing_webhook_id = wu_get_setting("paypal_rest_{$mode_prefix}_webhook_id", '');

		if (! empty($existing_webhook_id)) {
			// Verify it still exists
			$existing = $this->api_request('/v1/notifications/webhooks/' . $existing_webhook_id, [], 'GET');

			if (! is_wp_error($existing) && ! empty($existing['id'])) {
				$this->log(sprintf('Webhook already exists: %s', $existing_webhook_id));
				return true;
			}

			// Webhook was deleted, clear the setting
			wu_save_setting("paypal_rest_{$mode_prefix}_webhook_id", '');
		}

		$webhook_url = $this->get_webhook_listener_url();

		// Define the events we want to receive
		$event_types = [
			['name' => 'BILLING.SUBSCRIPTION.CREATED'],
			['name' => 'BILLING.SUBSCRIPTION.ACTIVATED'],
			['name' => 'BILLING.SUBSCRIPTION.UPDATED'],
			['name' => 'BILLING.SUBSCRIPTION.CANCELLED'],
			['name' => 'BILLING.SUBSCRIPTION.SUSPENDED'],
			['name' => 'BILLING.SUBSCRIPTION.PAYMENT.FAILED'],
			['name' => 'PAYMENT.SALE.COMPLETED'],
			['name' => 'PAYMENT.CAPTURE.COMPLETED'],
			['name' => 'PAYMENT.CAPTURE.REFUNDED'],
		];

		$webhook_data = [
			'url'         => $webhook_url,
			'event_types' => $event_types,
		];

		$this->log(sprintf('Creating webhook for URL: %s', $webhook_url));

		$result = $this->api_request('/v1/notifications/webhooks', $webhook_data);

		if (is_wp_error($result)) {
			$this->log(sprintf('Failed to create webhook: %s', $result->get_error_message()), LogLevel::ERROR);
			return $result;
		}

		if (empty($result['id'])) {
			$this->log('Webhook created but no ID returned', LogLevel::ERROR);
			return new \WP_Error('wu_paypal_webhook_no_id', __('Webhook created but no ID returned', 'ultimate-multisite'));
		}

		// Save the webhook ID
		wu_save_setting("paypal_rest_{$mode_prefix}_webhook_id", $result['id']);

		$this->log(sprintf('Webhook created successfully: %s', $result['id']));

		return true;
	}

	/**
	 * Check if webhook is installed.
	 *
	 * @since 2.0.0
	 * @return bool|array False if not installed, webhook data if installed.
	 */
	public function has_webhook_installed() {

		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';
		$webhook_id  = wu_get_setting("paypal_rest_{$mode_prefix}_webhook_id", '');

		if (empty($webhook_id)) {
			return false;
		}

		$webhook = $this->api_request('/v1/notifications/webhooks/' . $webhook_id, [], 'GET');

		if (is_wp_error($webhook)) {
			return false;
		}

		return $webhook;
	}

	/**
	 * Delete webhook from PayPal.
	 *
	 * @since 2.0.0
	 * @return bool True on success.
	 */
	public function delete_webhook(): bool {

		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';
		$webhook_id  = wu_get_setting("paypal_rest_{$mode_prefix}_webhook_id", '');

		if (empty($webhook_id)) {
			return true;
		}

		$result = $this->api_request('/v1/notifications/webhooks/' . $webhook_id, [], 'DELETE');

		// Clear the stored webhook ID regardless of result
		wu_save_setting("paypal_rest_{$mode_prefix}_webhook_id", '');

		if (is_wp_error($result)) {
			$this->log(sprintf('Failed to delete webhook: %s', $result->get_error_message()), LogLevel::WARNING);
			return false;
		}

		$this->log(sprintf('Webhook deleted: %s', $webhook_id));

		return true;
	}

	/**
	 * AJAX handler to manually install webhook.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function ajax_install_webhook(): void {

		check_ajax_referer('wu_paypal_webhook', 'nonce');

		if (! current_user_can('manage_network_options')) {
			wp_send_json_error(
				[
					'message' => __('You do not have permission to do this.', 'ultimate-multisite'),
				]
			);
		}

		// Reload credentials to ensure we have the latest
		$this->load_credentials();

		if (! $this->is_configured()) {
			wp_send_json_error(
				[
					'message' => __('PayPal credentials are not configured.', 'ultimate-multisite'),
				]
			);
		}

		$result = $this->install_webhook();

		if (true === $result) {
			wp_send_json_success(
				[
					'message' => __('Webhook configured successfully.', 'ultimate-multisite'),
				]
			);
		} elseif (is_wp_error($result)) {
			wp_send_json_error(
				[
					'message' => $result->get_error_message(),
				]
			);
		} else {
			wp_send_json_error(
				[
					'message' => __('Failed to configure webhook. Please try again.', 'ultimate-multisite'),
				]
			);
		}
	}
}
