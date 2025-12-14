<?php
/**
 * Stripe Connect Gateway.
 *
 * Extends the base Stripe gateway to add Stripe Connect functionality.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use Stripe;
use Stripe\StripeClient;
use WP_Ultimo\Gateways\Base_Stripe_Gateway;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Stripe Connect Gateway class.
 *
 * @since 2.0.0
 */
class Stripe_Connect_Gateway extends Base_Stripe_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'stripe-connect';

	/**
	 * Holds the Stripe Connect client instance.
	 *
	 * @since 2.0.0
	 * @var StripeClient
	 */
	protected StripeClient $stripe_connect_client;

	/**
	 * Holds the Stripe Connect public key.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $stripe_connect_publishable_key;

	/**
	 * Holds the Stripe Connect secret key.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $stripe_connect_secret_key;

	/**
	 * Holds the Stripe Connect account ID.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $stripe_connect_account_id;

	/**
	 * Holds the Stripe Connect application fee percentage.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $application_fee_percentage;

	/**
	 * Check if this is using Stripe Connect.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $is_connect = true;

	/**
	 * Gets or creates the Stripe Connect client instance.
	 *
	 * @return StripeClient
	 */
	protected function get_stripe_connect_client(): StripeClient {
		if (! isset($this->stripe_connect_client)) {
			$this->stripe_connect_client = new StripeClient(
				[
					'api_key' => $this->stripe_connect_secret_key,
				]
			);
		}

		return $this->stripe_connect_client;
	}

	/**
	 * Sets a mock Stripe Connect client for testing purposes.
	 *
	 * @param StripeClient $mock_client Mock Stripe Connect client.
	 * @return void
	 */
	public function set_stripe_connect_client(StripeClient $mock_client): void {
		$this->stripe_connect_client = $mock_client;
	}

	/**
	 * Get things going
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function init(): void {
		$id = wu_replace_dashes($this->get_id());

		$this->request_billing_address = true;

		/**
		 * As the toggle return a string with a int value,
		 * we need to convert this first to int then to bool.
		 */
		$this->test_mode = (bool) (int) wu_get_setting("{$id}_sandbox_mode", true);

		$this->setup_connect_api_keys($id);

		// Set app info for Stripe Connect
		if (method_exists(Stripe\Stripe::class, 'setAppInfo')) {
			Stripe\Stripe::setAppInfo('WordPress Ultimate Multisite', wu_get_version(), esc_url(site_url()));
		}

		$this->application_fee_percentage = (float) wu_get_setting("{$id}_application_fee_percentage", 0);
	}

	/**
	 * Setup Connect API keys for stripe.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id The gateway stripe connect id.
	 * @return void
	 */
	public function setup_connect_api_keys($id = false): void {
		$id = $id ?: wu_replace_dashes($this->get_id());

		if ($this->test_mode) {
			// Check if we have OAuth tokens (preferred) or fallback to API keys
			$access_token = wu_get_setting("{$id}_test_access_token", '');
			if (! empty($access_token)) {
				// Use OAuth access token
				$this->stripe_connect_publishable_key = wu_get_setting("{$id}_test_publishable_key", '');
				$this->stripe_connect_secret_key      = $access_token;
				$this->stripe_connect_account_id      = wu_get_setting("{$id}_test_account_id", '');
			} else {
				// Use direct API keys
				$this->stripe_connect_publishable_key = wu_get_setting("{$id}_test_pk_key", '');
				$this->stripe_connect_secret_key      = wu_get_setting("{$id}_test_sk_key", '');
				$this->stripe_connect_account_id      = wu_get_setting("{$id}_test_account_id", '');
			}
		} else {
			// Check if we have OAuth tokens (preferred) or fallback to API keys
			$access_token = wu_get_setting("{$id}_live_access_token", '');
			if (! empty($access_token)) {
				// Use OAuth access token
				$this->stripe_connect_publishable_key = wu_get_setting("{$id}_live_publishable_key", '');
				$this->stripe_connect_secret_key      = $access_token;
				$this->stripe_connect_account_id      = wu_get_setting("{$id}_live_account_id", '');
			} else {
				// Use direct API keys
				$this->stripe_connect_publishable_key = wu_get_setting("{$id}_live_pk_key", '');
				$this->stripe_connect_secret_key      = wu_get_setting("{$id}_live_sk_key", '');
				$this->stripe_connect_account_id      = wu_get_setting("{$id}_live_account_id", '');
			}
		}

		// Set the global API key for Stripe operations
		if ($this->stripe_connect_secret_key && Stripe\Stripe::getApiKey() !== $this->stripe_connect_secret_key) {
			Stripe\Stripe::setApiKey($this->stripe_connect_secret_key);

			Stripe\Stripe::setApiVersion('2019-05-16');
		}
	}

	/**
	 * Gets or creates the Stripe client instance with Connect support.
	 *
	 * @return StripeClient
	 */
	protected function get_stripe_client(): StripeClient {
		if (! isset($this->stripe_client)) {
			$this->stripe_client = new StripeClient(
				[
					'api_key' => $this->stripe_connect_secret_key,
				]
			);
		}

		// Set the Stripe-Account header if we have a connected account
		if (! empty($this->stripe_connect_account_id)) {
			$this->stripe_client->setStripeAccount($this->stripe_connect_account_id);
		}

		return $this->stripe_client;
	}

	/**
	 * Process a checkout with Connect support.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The checkout type.
	 *
	 * @throws \Exception When a stripe API error is caught.
	 *
	 * @return void
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type) {
		// Use parent's logic but with Connect headers
		$stripe_client = $this->get_stripe_connect_client();

		// Set the stripe account for Connect
		if (! empty($this->stripe_connect_account_id)) {
			$stripe_client->setStripeAccount($this->stripe_connect_account_id);
		}

		// Add application fee if configured
		$application_fee_percent = $this->application_fee_percentage;

		// Call parent method with Connect context
		parent::process_checkout($payment, $membership, $customer, $cart, $type);
	}

	/**
	 * Create a recurring subscription in Stripe with Connect support.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param Stripe\PaymentMethod         $payment_method The save payment method on Stripe.
	 * @param Stripe\Customer              $s_customer The Stripe customer.
	 *
	 * @return Stripe\Subscription|bool The Stripe subscription object or false if the creation is running in another process.
	 */
	protected function create_recurring_payment($membership, $cart, $payment_method, $s_customer) {
		// First we need to ensure that this process is not running in another place.
		$internal_key = "wu_stripe_recurring_creation_{$membership->get_id()}";

		$has_transient = get_site_transient($internal_key);

		if ($has_transient) {
			/**
			 * Process already start at another point (webhook or sync call).
			 */
			return false;
		}

		/**
		 * Set transient to avoid multiple calls.
		 */
		set_site_transient($internal_key, true, 120);

		/*
		 * We need to create a cart description that Stripe understands.
		 */
		$stripe_cart = $this->build_stripe_cart($cart);

		/*
		 * The cart creation process might run into
		 * errors, and in that case, it will
		 * return a WP_Error object.
		 */
		if (is_object($stripe_cart) && is_wp_error($stripe_cart)) {
			throw new \Exception(esc_html($stripe_cart->get_error_message()));
		}

		// Otherwise, use the calculated expiration date of the membership, modified to current time instead of 23:59.
		$billing_date = $cart->get_billing_start_date();
		$base_date    = $billing_date ?: $cart->get_billing_next_charge_date();
		$datetime     = \DateTime::createFromFormat('U', $base_date);
		$current_time = getdate();

		$datetime->setTime($current_time['hours'], $current_time['minutes'], $current_time['seconds']);

		$start_date = $datetime->getTimestamp() - HOUR_IN_SECONDS; // Reduce by 60 seconds to account for inaccurate server times.

		if (empty($payment_method)) {
			throw new \Exception(esc_html__('Invalid payment method', 'ultimate-multisite'));
		}

		/*
		 * Subscription arguments for Stripe
		 */
		$sub_args = [
			'customer'               => $s_customer->id,
			'items'                  => array_values($stripe_cart),
			'default_payment_method' => $payment_method->id,
			'proration_behavior'     => 'none',
			'metadata'               => $this->get_customer_metadata(),
		];

		// Add application fee if configured for Connect
		if ($this->is_connect && $this->application_fee_percentage > 0) {
			$sub_args['application_fee_percent'] = $this->application_fee_percentage;
		}

		/*
		 * Now determine if we use `trial_end` or `billing_cycle_anchor` to schedule the start of the
		 * subscription.
		 *
		 * If this is an actual trial, then we use `trial_end`.
		 *
		 * Otherwise, billing cycle anchor is preferable because that works with Stripe MRR.
		 * However, the anchor date cannot be further in the future than a normal billing cycle duration.
		 * If that's the case, then we have to use trial end instead.
		 */
		$stripe_max_anchor = $this->get_stripe_max_billing_cycle_anchor($cart->get_duration(), $cart->get_duration_unit(), 'now');

		if ($cart->has_trial() || $start_date > $stripe_max_anchor->getTimestamp()) {
			$sub_args['trial_end'] = $start_date;
		} else {
			$sub_args['billing_cycle_anchor'] = $start_date;
		}

		/*
		 * Sets the billing anchor.
		 */
		$set_anchor = isset($sub_args['billing_cycle_anchor']);

		/**
		 *  If we have a nun recurring discount code we need to add here to use in first payment.
		 */
		if ($cart->has_trial()) {
			/**
			 * If we have pro-rata credit (in case of an upgrade, for example)
			 * try to create a custom coupon.
			 */
			$s_coupon = $this->get_credit_coupon($cart);

			if ($s_coupon) {
				$sub_args['discounts'] = [['coupon' => $s_coupon]];
			}
		}

		/*
		 * Filters the Stripe subscription arguments.
		 */
		$sub_args = apply_filters('wu_stripe_create_subscription_args', $sub_args, $this);

		/*
		 * If we have a `billing_cycle_anchor` AND a `trial_end`, then we need to unset whichever one
		 * we set, and leave the customer's custom one in tact.
		 *
		 * This is done to account for people who filter the arguments to customize the next bill
		 * date. If `trial_end` is used in conjunction with `billing_cycle_anchor` then it will create
		 * unexpected results and the next bill date will not be what they want.
		 *
		 * This may not be completely perfect but it's the best way to try to account for any errors.
		 */
		if ( ! empty($sub_args['trial_end']) && ! empty($sub_args['billing_cycle_anchor'])) {
			/*
			 * If we set an anchor, remove that, because
			 * this means the customer has set their own `trial_end`.
			 */
			if ($set_anchor) {
				unset($sub_args['billing_cycle_anchor']);
			} else {
				/*
				 * We set a trial, which means the customer
				 * has set their own `billing_cycle_anchor`.
				 */
				unset($sub_args['trial_end']);
			}
		}

		$sub_options = apply_filters(
			'wu_stripe_create_subscription_options',
			[
				'idempotency_key' => wu_stripe_generate_idempotency_key($sub_args),
			]
		);

		// Initialize the Stripe client with Connect account if available
		$stripe_client = $this->get_stripe_connect_client();

		if (! empty($this->stripe_connect_account_id)) {
			$stripe_client->setStripeAccount($this->stripe_connect_account_id);
		}

		try {
			/*
			 * Tries to create the subscription
			 * on Stripe!
			 */
			$subscription = $stripe_client->subscriptions->create($sub_args, $sub_options);
		} catch (Stripe\Exception\IdempotencyException $exception) {
			/**
			 * In this case, the subscription is being created by another call.
			 */
			return false;
		}

		// If we have a trial we need to add fees to next invoice.
		if ($cart->has_trial()) {
			$currency = strtolower($cart->get_currency());

			$fees = array_filter($cart->get_line_items_by_type('fee'), fn($fee) => ! $fee->is_recurring());

			$s_fees = [];

			foreach ($fees as $fee) {
				$amount = $fee->get_quantity() * $fee->get_unit_price();

				$tax_behavior = '';
				$s_tax_rate   = false;

				if ($fee->is_taxable() && ! empty($fee->get_tax_rate())) {
					$tax_behavior = $fee->get_tax_inclusive() ? 'inclusive' : 'exclusive';

					$tax_args = [
						'country'   => $membership->get_billing_address()->billing_country,
						'tax_rate'  => $fee->get_tax_rate(),
						'type'      => $fee->get_tax_type(),
						'title'     => $fee->get_tax_label(),
						'inclusive' => $fee->get_tax_inclusive(),
					];

					$s_tax_rate = $this->maybe_create_tax_rate($tax_args);
				}

				$s_price = $this->maybe_create_price(
					$fee->get_title(),
					$amount,
					$currency,
					1,
					false,
					false,
					$tax_behavior,
				);

				$s_fee = [
					'price' => $s_price,
				];

				if ($s_price && $s_tax_rate) {
					$s_fee['tax_rates'] = [$s_tax_rate];
				}

				$s_fees[] = $s_fee;
			}

			if ( ! empty($s_fees)) {
				$options = [
					'add_invoice_items' => $s_fees,
				];

				$sub_options = [
					'idempotency_key' => wu_stripe_generate_idempotency_key(array_merge(['s_subscription' => $subscription->id], $options)),
				];

				try {
					$subscription = $stripe_client->subscriptions->update($subscription->id, $options, $sub_options);
				} catch (Stripe\Exception\IdempotencyException $exception) {
					/**
					 * In this case, the subscription is being updated by another call.
					 */
					return false;
				}
			}
		}

		return $subscription;
	}

	/**
	 * Adds additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks(): void {

		parent::hooks();

		// Handle OAuth callbacks
		add_action('admin_init', [$this, 'handle_oauth_callbacks']);
	}

	/**
	 * Handle OAuth callbacks for Stripe Connect.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_oauth_callbacks(): void {
		// Check if this is a Stripe Connect OAuth callback
		if (isset($_GET['code'], $_GET['state'])) {
			// Sanitize inputs
			$code = sanitize_text_field(wp_unslash($_GET['code']));
			$state = sanitize_text_field(wp_unslash($_GET['state']));

			// Check if it's a Stripe Connect callback by verifying parameters
			if (get_option('wu_stripe_connect_state') === $state) {
				// Process the OAuth callback
				$this->process_oauth_callback($code, $state);
			}
		}

		// Handle disconnect requests
		if (isset($_GET['stripe_connect_disconnect'])) {
			$this->handle_disconnect();
		}
	}

	/**
	 * Process the OAuth callback from Stripe.
	 *
	 * @since 2.0.0
	 * @param string $code Authorization code from Stripe
	 * @param string $state State parameter for CSRF protection
	 * @return void
	 */
	private function process_oauth_callback($code, $state): void {
		// Verify the state parameter to prevent CSRF attacks
		$expected_state = get_option('wu_stripe_connect_state');
		if (empty($expected_state) || $expected_state !== $state) {
			wp_die(__('Invalid state parameter', 'ultimate-multisite'));
		}

		// Clear the state after verification
		delete_option('wu_stripe_connect_state');

		// Exchange the authorization code for an access token
		// $code is already passed as a parameter and sanitized

		// Use the platform's secret key for the OAuth exchange, not the connected account's
		$platform_secret_key = $this->get_platform_secret_key();

		$response = wp_remote_post(
			'https://connect.stripe.com/oauth/token',
			[
				'body' => [
					'grant_type'    => 'authorization_code',
					'client_secret' => $platform_secret_key,
					'code'          => $code,
					'redirect_uri'  => admin_url('admin.php?page=wu-settings&tab=payment-gateways'),
				],
			]
		);

		if (is_wp_error($response)) {
			wp_die(__('Error connecting to Stripe: ', 'ultimate-multisite') . $response->get_error_message());
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_die(__('Error parsing Stripe response', 'ultimate-multisite'));
		}

		if (isset($body['error'])) {
			wp_die(__('Stripe error: ', 'ultimate-multisite') . $body['error_description']);
		}

		// Save the tokens
		$id           = wu_replace_dashes($this->get_id());
		$is_test_mode = (bool) wu_get_setting("{$id}_sandbox_mode", true);

		if ($is_test_mode) {
			wu_save_setting("{$id}_test_access_token", $body['access_token']);
			wu_save_setting("{$id}_test_refresh_token", $body['refresh_token'] ?? '');
			wu_save_setting("{$id}_test_account_id", $body['stripe_user_id']);
			wu_save_setting("{$id}_test_publishable_key", $body['stripe_publishable_key']);
		} else {
			wu_save_setting("{$id}_live_access_token", $body['access_token']);
			wu_save_setting("{$id}_live_refresh_token", $body['refresh_token'] ?? '');
			wu_save_setting("{$id}_live_account_id", $body['stripe_user_id']);
			wu_save_setting("{$id}_live_publishable_key", $body['stripe_publishable_key']);
		}

		// Update the current instance properties
		$this->setup_connect_api_keys($id);

		// Redirect back to settings page with success message
		$redirect_url = add_query_arg(
			[
				'page'   => 'wu-settings',
				'tab'    => 'payment-gateways',
				'notice' => 'stripe_connect_success',
			],
			admin_url('admin.php')
		);

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Get the platform's secret key for OAuth exchange.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function get_platform_secret_key(): string {
		$id           = wu_replace_dashes($this->get_id());
		$is_test_mode = (bool) wu_get_setting("{$id}_sandbox_mode", true);

		// Use the regular Stripe Connect API keys as the platform credentials
		// These would need to be configured separately in the settings
		if ($is_test_mode) {
			return wu_get_setting("{$id}_platform_test_sk_key", '');
		} else {
			return wu_get_setting("{$id}_platform_live_sk_key", '');
		}
	}

	/**
	 * Handle disconnecting the Stripe Connect account.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function handle_disconnect(): void {
		// Verify nonce
		$nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
		if (! wp_verify_nonce($nonce, 'wu_disconnect_stripe_connect')) {
			wp_die(__('Invalid nonce', 'ultimate-multisite'));
		}

		$id = wu_replace_dashes($this->get_id());

		// Delete the saved tokens
		wu_save_setting("{$id}_test_access_token", '');
		wu_save_setting("{$id}_test_refresh_token", '');
		wu_save_setting("{$id}_test_account_id", '');
		wu_save_setting("{$id}_test_publishable_key", '');
		wu_save_setting("{$id}_live_access_token", '');
		wu_save_setting("{$id}_live_refresh_token", '');
		wu_save_setting("{$id}_live_account_id", '');
		wu_save_setting("{$id}_live_publishable_key", '');

		// Update the current instance properties
		$this->setup_connect_api_keys($id);

		// Redirect back to settings page with success message
		$redirect_url = add_query_arg(
			[
				'page'   => 'wu-settings',
				'tab'    => 'payment-gateways',
				'notice' => 'stripe_connect_disconnected',
			],
			admin_url('admin.php')
		);

		wp_safe_redirect($redirect_url);
		exit;
	}

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
			'stripe_connect_header',
			[
				'title'           => __('Stripe Connect', 'ultimate-multisite'),
				'desc'            => __('Use the settings section below to configure Stripe Connect as a payment method.', 'ultimate-multisite'),
				'type'            => 'header',
				'show_as_submenu' => true,
				'require'         => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_public_title',
			[
				'title'   => __('Stripe Public Name', 'ultimate-multisite'),
				'tooltip' => __('The name to display on the payment method selection field. By default, "Credit Card" is used.', 'ultimate-multisite'),
				'type'    => 'text',
				'default' => __('Credit Card', 'ultimate-multisite'),
				'require' => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_sandbox_mode',
			[
				'title'     => __('Stripe Connect Sandbox Mode', 'ultimate-multisite'),
				'desc'      => __('Toggle this to put Stripe Connect on sandbox mode. This is useful for testing and making sure Stripe is correctly setup to handle your payments.', 'ultimate-multisite'),
				'type'      => 'toggle',
				'default'   => 1,
				'html_attr' => [
					'v-model' => 'stripe_connect_sandbox_mode',
				],
				'require'   => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		// Stripe Connect OAuth onboarding button
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_onboard_button',
			[
				'title'   => __('Stripe Connect Onboarding', 'ultimate-multisite'),
				'desc'    => __('Connect your Stripe account using the OAuth flow for secure payment processing.', 'ultimate-multisite'),
				'type'    => 'html',
				'content' => $this->get_connect_onboard_html(),
				'require' => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		// Advanced settings toggle to show API keys
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_show_api_keys',
			[
				'title'   => __('Show API Keys (Advanced)', 'ultimate-multisite'),
				'desc'    => __('Show direct API key configuration options. Only for advanced users.', 'ultimate-multisite'),
				'type'    => 'toggle',
				'default' => 0,
				'require' => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		// Platform API keys for OAuth (shown only when advanced toggle is on)
		$platform_pk_test_status = wu_get_setting('stripe_connect_platform_test_pk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_platform_test_pk_key',
			[
				'title'       => __('Platform Test Publishable Key', 'ultimate-multisite'),
				'desc'        => ! empty($platform_pk_test_status) ? sprintf($error_message_wrap, $platform_pk_test_status) : '',
				'tooltip'     => __('Platform publishable key for OAuth flow', 'ultimate-multisite'),
				'placeholder' => __('pk_test_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 1,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$platform_sk_test_status = wu_get_setting('stripe_connect_platform_test_sk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_platform_test_sk_key',
			[
				'title'       => __('Platform Test Secret Key', 'ultimate-multisite'),
				'desc'        => ! empty($platform_sk_test_status) ? sprintf($error_message_wrap, $platform_sk_test_status) : '',
				'tooltip'     => __('Platform secret key for OAuth flow. This is used to exchange authorization codes for access tokens.', 'ultimate-multisite'),
				'placeholder' => __('sk_test_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 1,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$platform_pk_status = wu_get_setting('stripe_connect_platform_live_pk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_platform_live_pk_key',
			[
				'title'       => __('Platform Live Publishable Key', 'ultimate-multisite'),
				'desc'        => ! empty($platform_pk_status) ? sprintf($error_message_wrap, $platform_pk_status) : '',
				'tooltip'     => __('Platform publishable key for OAuth flow', 'ultimate-multisite'),
				'placeholder' => __('pk_live_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 0,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$platform_sk_status = wu_get_setting('stripe_connect_platform_live_sk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_platform_live_sk_key',
			[
				'title'       => __('Platform Live Secret Key', 'ultimate-multisite'),
				'desc'        => ! empty($platform_sk_status) ? sprintf($error_message_wrap, $platform_sk_status) : '',
				'tooltip'     => __('Platform secret key for OAuth flow. This is used to exchange authorization codes for access tokens.', 'ultimate-multisite'),
				'placeholder' => __('sk_live_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 0,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		// Test API keys (shown only when advanced toggle is on)
		$pk_test_status = wu_get_setting('stripe_connect_test_pk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_test_pk_key',
			[
				'title'       => __('Stripe Test Publishable Key', 'ultimate-multisite'),
				'desc'        => ! empty($pk_test_status) ? sprintf($error_message_wrap, $pk_test_status) : '',
				'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'ultimate-multisite'),
				'placeholder' => __('pk_test_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 1,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$sk_test_status = wu_get_setting('stripe_connect_test_sk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_test_sk_key',
			[
				'title'       => __('Stripe Test Secret Key', 'ultimate-multisite'),
				'desc'        => ! empty($sk_test_status) ? sprintf($error_message_wrap, $sk_test_status) : '',
				'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'ultimate-multisite'),
				'placeholder' => __('sk_test_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 1,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$acc_test_status = wu_get_setting('stripe_connect_test_account_id_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_test_account_id',
			[
				'title'       => __('Stripe Test Account ID', 'ultimate-multisite'),
				'desc'        => ! empty($acc_test_status) ? sprintf($error_message_wrap, $acc_test_status) : '',
				'tooltip'     => __('Enter your test Stripe Connect account ID', 'ultimate-multisite'),
				'placeholder' => __('acct_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 1,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		// Live API keys (shown only when advanced toggle is on)
		$pk_status = wu_get_setting('stripe_connect_live_pk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_live_pk_key',
			[
				'title'       => __('Stripe Live Publishable Key', 'ultimate-multisite'),
				'desc'        => ! empty($pk_status) ? sprintf($error_message_wrap, $pk_status) : '',
				'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'ultimate-multisite'),
				'placeholder' => __('pk_live_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 0,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$sk_status = wu_get_setting('stripe_connect_live_sk_key_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_live_sk_key',
			[
				'title'       => __('Stripe Live Secret Key', 'ultimate-multisite'),
				'desc'        => ! empty($sk_status) ? sprintf($error_message_wrap, $sk_status) : '',
				'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'ultimate-multisite'),
				'placeholder' => __('sk_live_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 0,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		$acc_status = wu_get_setting('stripe_connect_live_account_id_status', '');
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_live_account_id',
			[
				'title'       => __('Stripe Live Account ID', 'ultimate-multisite'),
				'desc'        => ! empty($acc_status) ? sprintf($error_message_wrap, $acc_status) : '',
				'tooltip'     => __('Enter your live Stripe Connect account ID', 'ultimate-multisite'),
				'placeholder' => __('acct_***********', 'ultimate-multisite'),
				'type'        => 'text',
				'default'     => '',
				'capability'  => 'manage_api_keys',
				'require'     => [
					'active_gateways'              => 'stripe-connect',
					'stripe_connect_sandbox_mode'  => 0,
					'stripe_connect_show_api_keys' => 1,
				],
			]
		);

		// Application fee percentage setting
		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_application_fee_percentage',
			[
				'title'       => __('Application Fee Percentage', 'ultimate-multisite'),
				'desc'        => __('Percentage of each transaction that goes to your platform. This is only applied when using Stripe Connect.', 'ultimate-multisite'),
				'tooltip'     => __('Set the percentage you want to take from each transaction. For example, 2.5 for 2.5%.', 'ultimate-multisite'),
				'placeholder' => __('2.5', 'ultimate-multisite'),
				'type'        => 'number',
				'min'         => 0,
				'max'         => 100,
				'step'        => 0.1,
				'default'     => 0,
				'require'     => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		$webhook_message = sprintf('<span class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-mt-3 wu-mb-0 wu-block wu-text-xs">%s</span>', __('Whenever you change your Stripe settings, Ultimate Multisite will automatically check the webhook URLs on your Stripe account to make sure we get notified about changes in subscriptions and payments.', 'ultimate-multisite'));

		wu_register_settings_field(
			'payment-gateways',
			'stripe_connect_webhook_listener_explanation',
			[
				'title'           => __('Webhook Listener URL', 'ultimate-multisite'),
				'desc'            => $webhook_message,
				'tooltip'         => __('This is the URL Stripe should send webhook calls to.', 'ultimate-multisite'),
				'type'            => 'text-display',
				'copy'            => true,
				'default'         => $this->get_webhook_listener_url(),
				'wrapper_classes' => '',
				'require'         => [
					'active_gateways' => 'stripe-connect',
				],
			]
		);

		parent::settings();
	}

	/**
	 * Get the HTML for the Connect onboarding button.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function get_connect_onboard_html() {
		$connected_account_id = $this->stripe_connect_account_id;
		$is_connected         = ! empty($connected_account_id);

		if ($is_connected) {
			$html  = '<div class="wu-mb-4">';
			$html .= '<p class="wu-text-green-600 wu-font-medium">' . __('Your Stripe account is connected!', 'ultimate-multisite') . '</p>';
			$html .= '<p class="wu-text-sm wu-text-gray-600">' . sprintf(__('Connected Account ID: %s', 'ultimate-multisite'), esc_html($connected_account_id)) . '</p>';
			$html .= '<a href="' . esc_url(
				add_query_arg(
					[
						'stripe_connect_disconnect' => 1,
						'_wpnonce'                  => wp_create_nonce('wu_disconnect_stripe_connect'),
					],
					admin_url('admin.php?page=wu-settings&tab=payment-gateways')
				)
			) . '" class="button button-secondary wu-mt-2">' . __('Disconnect Account', 'ultimate-multisite') . '</a>';
			$html .= '</div>';
		} else {
			// Generate a state parameter for security
			$state = wp_generate_password(32, false);
			update_option('wu_stripe_connect_state', $state);

			$html  = '<div class="wu-mb-4">';
			$html .= '<p class="wu-text-gray-600 wu-mb-3">' . __('Connect your Stripe account to start accepting payments through Stripe Connect.', 'ultimate-multisite') . '</p>';
			$html .= '<a href="' . esc_url($this->get_connect_authorization_url($state)) . '" class="button button-primary">' . __('Connect with Stripe', 'ultimate-multisite') . '</a>';
			$html .= '<p class="wu-text-xs wu-text-gray-500 wu-mt-2">' . __('This will redirect you to Stripe to authorize your account connection.', 'ultimate-multisite') . '</p>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Get the Stripe Connect authorization URL.
	 *
	 * @since 2.0.0
	 * @param string $state State parameter for security.
	 * @return string
	 */
	private function get_connect_authorization_url($state = '') {
		// This would need to be configured with your Stripe Connect application
		$client_id    = wu_get_setting('stripe_connect_client_id', '');
		$redirect_uri = admin_url('admin.php?page=wu-settings&tab=payment-gateways');

		$scope = 'read_write'; // Appropriate scope for payment processing

		$params = [
			'response_type' => 'code',
			'client_id'     => $client_id,
			'scope'         => $scope,
			'redirect_uri'  => $redirect_uri,
		];

		if (! empty($state)) {
			$params['state'] = $state;
		}

		$auth_url = add_query_arg($params, 'https://connect.stripe.com/oauth/authorize');

		return $auth_url;
	}

	/**
	 * Process webhook for Stripe Connect.
	 *
	 * @since 2.0.0
	 * @throws \Exception When the webhook should be ignored.
	 * @return void
	 */
	public function process_webhooks() {
		// Call parent to maintain existing functionality
		parent::process_webhooks();
	}

	/**
	 * Update the application fee for a subscription if applicable.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @return void
	 */
	public function maybe_update_application_fee($membership) {
		if (! $this->is_connect || $this->application_fee_percentage <= 0) {
			return;
		}

		$subscription_id = $membership->get_gateway_subscription_id();
		if (empty($subscription_id)) {
			return;
		}

		$stripe_client = $this->get_stripe_connect_client();

		if (! empty($this->stripe_connect_account_id)) {
			$stripe_client->setStripeAccount($this->stripe_connect_account_id);
		}

		try {
			// Update subscription to include application fee
			$stripe_client->subscriptions->update(
				$subscription_id,
				[
					'application_fee_percent' => $this->application_fee_percentage,
				]
			);
		} catch (\Exception $e) {
			wu_log_add('stripe_connect', sprintf('Failed to update application fee for subscription %s: %s', $subscription_id, $e->getMessage()));
		}
	}
}
