<?php
/**
 * Gateway Manager
 *
 * Manages the registering and activation of gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Gateway
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use Psr\Log\LogLevel;
use WP_Ultimo\Gateways\Base_Gateway;
use WP_Ultimo\Gateways\Ignorable_Exception;

use WP_Ultimo\Gateways\Free_Gateway;
use WP_Ultimo\Gateways\Stripe_Gateway;
use WP_Ultimo\Gateways\Stripe_Checkout_Gateway;
use WP_Ultimo\Gateways\PayPal_Gateway;
use WP_Ultimo\Gateways\Manual_Gateway;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manages the registering and activation of gateways.
 *
 * @since 2.0.0
 */
class Gateway_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Lists the registered gateways.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $registered_gateways = [];

	/**
	 * Lists the gateways that are enabled.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $enabled_gateways = [];

	/**
	 * Keeps a list of the gateways with auto-renew.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $auto_renewable_gateways = [];

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('plugins_loaded', [$this, 'on_load']);
	}

	/**
	 * Runs after all plugins have been loaded to allow for add-ons to hook into it correctly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function on_load(): void {
		/*
		 * Adds our own default gateways.
		 */
		add_action('wu_register_gateways', [$this, 'add_default_gateways'], 5);

		/*
		 * Allow developers to add new gateways.
		 */
		add_action(
			'init',
			function () {
				do_action('wu_register_gateways');
			},
			19
		);

		/*
		 * Adds the Gateway selection fields
		 */
		add_action('init', [$this, 'add_gateway_selector_field']);

		/*
		 * Handle gateway confirmations.
		 * We need it both on the front-end and the back-end.
		 */
		add_action('template_redirect', [$this, 'process_gateway_confirmations'], -99999);
		add_action('load-admin_page_wu-checkout', [$this, 'process_gateway_confirmations'], -99999);

		/*
		 * Waits for webhook signals and deal with them.
		 */
		add_action('init', [$this, 'maybe_process_webhooks'], 21);

		/*
		 * Waits for webhook signals and deal with them.
		 */
		add_action('admin_init', [$this, 'maybe_process_v1_webhooks'], 21);
	}

	/**
	 * Checks if we need to process webhooks received by gateways.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_process_webhooks(): void {

		$gateway = wu_request('wu-gateway');

		if ($gateway && ! is_admin() && is_main_site()) {
			/*
			 * Do not cache this!
			 */
			!defined('DONOTCACHEPAGE') && define('DONOTCACHEPAGE', true); // phpcs:ignore

			try {
				/*
				 * Passes it down to Gateways.
				 *
				 * Gateways will hook into here
				 * to handle their respective webhook
				 * calls.
				 *
				 * We also wrap it inside a try/catch
				 * to make sure we log errors,
				 * tell network admins, and make sure
				 * the gateway tries again by sending back
				 * a non-200 HTTP code.
				 */
				do_action("wu_{$gateway}_process_webhooks");

				http_response_code(200);

				die('Thanks!');
			} catch (Ignorable_Exception $e) {
				$message = sprintf('We failed to handle a webhook call, but in this case, no further action is necessary. Message: %s', $e->getMessage());

				wu_log_add("wu-{$gateway}-webhook-errors", $message);

				/*
				 * Send the error back, but with a 200.
				 */
				wp_send_json_error(new \WP_Error('webhook-error', $message), 200);
			} catch (\Throwable $e) {
				$file = $e->getFile();
				$line = $e->getLine();

				$message = sprintf('We failed to handle a webhook call. Error: %s', $e->getMessage());

				$message .= PHP_EOL . "Location: {$file}:{$line}";
				$message .= PHP_EOL . $e->getTraceAsString();

				wu_log_add("wu-{$gateway}-webhook-errors", $message, LogLevel::ERROR);

				/*
				 * Force a 500.
				 *
				 * Most gateways will try again later when
				 * a non-200 code is returned.
				 */
				wp_send_json_error(new \WP_Error('webhook-error', $message), 500);
			}
		}
	}

	/**
	 * Checks if we need to process webhooks received by legacy gateways.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function maybe_process_v1_webhooks(): void {

		$action = wu_request('action', '');

		if ($action && str_contains((string) $action, 'notify_gateway_')) {
			/*
			 * Get the gateway id from the action.
			 */
			$gateway_id = str_replace(['nopriv_', 'notify_gateway_'], '', (string) $action);

			$gateway = wu_get_gateway($gateway_id);

			if ($gateway) {
				$gateway->before_backwards_compatible_webhook();

				/*
				 * Do not cache this!
				 */
				!defined('DONOTCACHEPAGE') && define('DONOTCACHEPAGE', true); // phpcs:ignore

				try {
					/*
					 * Passes it down to Gateways.
					 *
					 * Gateways will hook into here
					 * to handle their respective webhook
					 * calls.
					 *
					 * We also wrap it inside a try/catch
					 * to make sure we log errors,
					 * tell network admins, and make sure
					 * the gateway tries again by sending back
					 * a non-200 HTTP code.
					 */
					do_action("wu_{$gateway_id}_process_webhooks");

					http_response_code(200);

					die('Thanks!');
				} catch (Ignorable_Exception $e) {
					$message = sprintf('We failed to handle a webhook call, but in this case, no further action is necessary. Message: %s', $e->getMessage());

					wu_log_add("wu-{$gateway_id}-webhook-errors", $message);

					/*
					* Send the error back, but with a 200.
					*/
					wp_send_json_error(new \WP_Error('webhook-error', $message), 200);
				} catch (\Throwable $e) {
					$message = sprintf('We failed to handle a webhook call. Error: %s', $e->getMessage());

					wu_log_add("wu-{$gateway_id}-webhook-errors", $message, LogLevel::ERROR);

					/*
					 * Force a 500.
					 *
					 * Most gateways will try again later when
					 * a non-200 code is returned.
					 */
					http_response_code(500);

					wp_send_json_error(new \WP_Error('webhook-error', $message));
				}
			}
		}
	}

	/**
	 * Let gateways deal with their confirmation steps.
	 *
	 * This is the case for PayPal Express.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_gateway_confirmations(): void {
		/*
		 * First we check for the confirmation parameter.
		 */
		if ( ! wu_request('wu-confirm') || (wu_request('status') && wu_request('status') === 'done')) {
			return;
		}

		ob_start();

		add_filter('body_class', fn($classes) => array_merge($classes, ['wu-process-confirmation']));

		$gateway_id = sanitize_text_field(wu_request('wu-confirm'));

		$gateway = wu_get_gateway($gateway_id);

		if ( ! $gateway) {
			$error = new \WP_Error('missing_gateway', esc_html__('Missing gateway parameter.', 'multisite-ultimate'));

			wp_die(
				$error, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html__('Error', 'multisite-ultimate'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		try {
			$payment_hash = wu_request('payment');

			$payment = wu_get_payment_by_hash($payment_hash);

			if ($payment) {
				$gateway->set_payment($payment);
			}

			/*
			 * Pass it down to the gateway.
			 *
			 * Here you can throw exceptions, that
			 * we will catch it and throw it as a wp_die
			 * message.
			 */
			$results = $gateway->process_confirmation();

			if (is_wp_error($results)) {
				wp_die(
					$results, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_html__('Error', 'multisite-ultimate'),
					[
						'back_link' => true,
						'response'  => '200',
					]
				);
			}
		} catch (\Throwable $e) {
			$error = new \WP_Error('confirm-error-' . $e->getCode(), $e->getMessage());

			wp_die(
				$error, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html__('Error', 'multisite-ultimate'),
				[
					'back_link' => true,
					'response'  => '200',
				]
			);
		}

		$output = ob_get_clean();

		if ( ! empty($output)) {
			/*
			 * Add a filter to bypass the checkout form.
			 * This is used for PayPal confirmation page.
			 */

			add_action(
				'wu_bypass_checkout_form',
				function ($output) {
					return $output;
				},
				10,
				1
			);
		}
	}

	/**
	 * Adds the field that enabled and disables Payment Gateways on the settings.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_gateway_selector_field(): void {

		wu_register_settings_field(
			'payment-gateways',
			'active_gateways',
			[
				'title'   => __('Active Payment Gateways', 'multisite-ultimate'),
				'desc'    => __('Payment gateways are what your customers will use to pay.', 'multisite-ultimate'),
				'type'    => 'multiselect',
				'columns' => 2,
				'options' => [$this, 'get_gateways_as_options'],
				'default' => [],
			]
		);
	}

	/**
	 * Returns the list of registered gateways as options for the gateway selector setting.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_gateways_as_options() {
		/*
		 * We use this to order the options.
		 */
		$active_gateways = wu_get_setting('active_gateways', []);

		$gateways = $this->get_registered_gateways();

		$gateways = array_filter($gateways, fn($item) => false === $item['hidden']);

		return $gateways;
	}

	/**
	 * Loads the default gateways.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_default_gateways(): void {
		/*
		 * Free Payments
		 */
		wu_register_gateway('free', __('Free', 'multisite-ultimate'), '', Free_Gateway::class, true);

		/*
		 * Stripe Payments
		 */
		$stripe_desc = __('Stripe is a suite of payment APIs that powers commerce for businesses of all sizes, including subscription management.', 'multisite-ultimate');
		wu_register_gateway('stripe', __('Stripe', 'multisite-ultimate'), $stripe_desc, Stripe_Gateway::class);

		/*
		 * Stripe Checkout Payments
		 */
		$stripe_checkout_desc = __('Stripe Checkout is the hosted solution for checkouts using Stripe.', 'multisite-ultimate');
		wu_register_gateway('stripe-checkout', __('Stripe Checkout', 'multisite-ultimate'), $stripe_checkout_desc, Stripe_Checkout_Gateway::class);

		/*
		 * PayPal Payments
		 */
		$paypal_desc = __('PayPal is the leading provider in checkout solutions and it is the easier way to get your network subscriptions going.', 'multisite-ultimate');
		wu_register_gateway('paypal', __('PayPal', 'multisite-ultimate'), $paypal_desc, PayPal_Gateway::class);

		/*
		 * Manual Payments
		 */
		$manual_desc = __('Use the Manual Gateway to allow users to pay you directly via bank transfers, checks, or other channels.', 'multisite-ultimate');
		wu_register_gateway('manual', __('Manual', 'multisite-ultimate'), $manual_desc, Manual_Gateway::class);
	}

	/**
	 * Checks if a gateway was already registered.
	 *
	 * @since 2.0.0
	 * @param string $id The id of the gateway.
	 * @return boolean
	 */
	public function is_gateway_registered($id) {

		return is_array($this->registered_gateways) && isset($this->registered_gateways[ $id ]);
	}

	/**
	 * Returns a list of all the registered gateways
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_registered_gateways() {

		return $this->registered_gateways;
	}

	/**
	 * Returns a particular Gateway registered
	 *
	 * @since 2.0.0
	 * @param string $id The id of the gateway.
	 * @return array|false
	 */
	public function get_gateway($id) {

		return $this->is_gateway_registered($id) ? $this->registered_gateways[ $id ] : false;
	}

	/**
	 * Adds a new Gateway to the System. Used by gateways to make themselves visible.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id ID of the gateway. This is how we will identify the gateway in the system.
	 * @param string $title Name of the gateway.
	 * @param string $desc A description of the gateway to help super admins understand what services they integrate with.
	 * @param string $class_name Gateway class name.
	 * @param bool   $hidden If we need to hide this gateway publicly.
	 * @return bool
	 */
	public function register_gateway($id, $title, $desc, $class_name, $hidden = false) {

		// Checks if gateway was already added
		if ($this->is_gateway_registered($id)) {
			return false;
		}

		$active_gateways = (array) wu_get_setting('active_gateways', []);

		// Adds to the global
		$this->registered_gateways[ $id ] = [
			'id'         => $id,
			'title'      => $title,
			'desc'       => $desc,
			'class_name' => $class_name,
			'active'     => in_array($id, $active_gateways, true),
			'hidden'     => (bool) $hidden,
			'gateway'    => $class_name, // Deprecated.
		];

		$this->install_hooks($class_name);

		// Return the value
		return true;
	}

	/**
	 * Adds additional hooks for each of the gateway registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class_name Gateway class name.
	 * @return void
	 */
	public function install_hooks($class_name): void {

		/** @var Base_Gateway $gateway */
		$gateway = new $class_name();

		$gateway_id = $gateway->get_id();

		/*
		 * If the gateway supports recurring
		 * payments, add it to the list.
		 */
		if ($gateway->supports_recurring()) {
			$this->auto_renewable_gateways[] = $gateway_id;
		}

		add_action('wu_checkout_scripts', [$gateway, 'register_scripts']);

		$gateway->hooks();
		add_action('wu_settings_payment_gateways', [$gateway, 'settings']);

		add_action("wu_{$gateway_id}_process_webhooks", [$gateway, 'process_webhooks']);

		add_action("wu_{$gateway_id}_remote_payment_url", [$gateway, 'get_payment_url_on_gateway']);

		add_action("wu_{$gateway_id}_remote_subscription_url", [$gateway, 'get_subscription_url_on_gateway']);

		add_action("wu_{$gateway_id}_remote_customer_url", [$gateway, 'get_customer_url_on_gateway']);

		/*
		 * Renders the gateway fields.
		 */
		add_action(
			'wu_checkout_gateway_fields',
			function () use ($gateway) {

				$field_content = call_user_func([$gateway, 'fields']);

				ob_start();

				?>

				<div v-cloak v-show="gateway == '<?php echo esc_attr($gateway->get_id()); ?>' && order && order.should_collect_payment" class="wu-overflow">
					<?php echo $field_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

				<?php

				echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		);
	}

	/**
	 * Returns an array with the list of gateways that support auto-renew.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_auto_renewable_gateways() {

		return (array) $this->auto_renewable_gateways;
	}
}
