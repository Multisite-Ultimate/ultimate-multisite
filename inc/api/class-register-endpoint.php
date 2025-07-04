<?php
/**
 * The Register API endpoint.
 *
 * @package WP_Ultimo
 * @subpackage API
 * @since 2.0.0
 */

namespace WP_Ultimo\API;

use WP_Ultimo\Checkout\Cart;
use WP_Ultimo\Database\Sites\Site_Type;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Objects\Billing_Address;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The Register API endpoint.
 *
 * @since 2.0.0
 */
class Register_Endpoint {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Loads the initial register route hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		add_action('wu_register_rest_routes', [$this, 'register_route']);
	}

	/**
	 * Adds a new route to the wu namespace, for the register endpoint.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\API $api The API main singleton.
	 * @return void
	 */
	public function register_route($api): void {

		$namespace = $api->get_namespace();

		register_rest_route(
			$namespace,
			'/register',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [$this, 'handle_get'],
				'permission_callback' => \Closure::fromCallable([$api, 'check_authorization']),
			]
		);

		register_rest_route(
			$namespace,
			'/register',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [$this, 'handle_endpoint'],
				'permission_callback' => \Closure::fromCallable([$api, 'check_authorization']),
				'args'                => $this->get_rest_args(),
			]
		);
	}

	/**
	 * Handle the register endpoint get for zapier integration reasons.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return array
	 */
	public function handle_get($request) {

		return [
			'registration_status' => wu_get_setting('enable_registration', true) ? 'open' : 'closed',
		];
	}

	/**
	 * Handle the register endpoint logic.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return array|\WP_Error
	 */
	public function handle_endpoint($request) {

		global $wpdb;

		$params = json_decode($request->get_body(), true);

		if (\WP_Ultimo\API::get_instance()->should_log_api_calls()) {
			wu_log_add('api-calls', wp_json_encode($params, JSON_PRETTY_PRINT));
		}

		$validation_errors = $this->validate($params);

		if (is_wp_error($validation_errors)) {
			$validation_errors->add_data(
				[
					'status' => 400,
				]
			);

			return $validation_errors;
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {
			$customer = $this->maybe_create_customer($params);

			if (is_wp_error($customer)) {
				return $this->rollback_and_return($customer);
			}

			$customer->update_last_login(true, true);

			$customer->add_note(
				[
					'text'      => __('Created via REST API', 'multisite-ultimate'),
					'author_id' => $customer->get_user_id(),
				]
			);

			/*
			 * Payment Method defaults
			 */
			$payment_method = wp_parse_args(
				wu_get_isset($params, 'payment_method'),
				[
					'gateway'                 => '',
					'gateway_customer_id'     => '',
					'gateway_subscription_id' => '',
					'gateway_payment_id'      => '',
				]
			);

			/*
			 * Cart params and creation
			 */
			$cart_params = $params;

			$cart_params = wp_parse_args(
				$cart_params,
				[
					'type' => 'new',
				]
			);

			$cart = new Cart($cart_params);

			/*
			 * Validates if the cart is valid.
			 */
			if ($cart->is_valid() && count($cart->get_line_items()) === 0) {
				return new \WP_Error(
					'invalid_cart',
					__('Products are required.', 'multisite-ultimate'),
					array_merge(
						(array) $cart->done(),
						[
							'status' => 400,
						]
					)
				);
			}

			/*
			 * Get Membership data
			 */
			$membership_data = $cart->to_membership_data();

			$membership_data = array_merge(
				$membership_data,
				wu_get_isset(
					$params,
					'membership',
					[
						'status' => Membership_Status::PENDING,
					]
				)
			);

			$membership_data['customer_id']             = $customer->get_id();
			$membership_data['gateway']                 = wu_get_isset($payment_method, 'gateway');
			$membership_data['gateway_subscription_id'] = wu_get_isset($payment_method, 'gateway_subscription_id');
			$membership_data['gateway_customer_id']     = wu_get_isset($payment_method, 'gateway_customer_id');
			$membership_data['auto_renew']              = wu_get_isset($params, 'auto_renew');

			/*
			 * Unset the status because we are going to transition it later.
			 */
			$membership_status = $membership_data['status'];

			unset($membership_data['status']);

			$membership = wu_create_membership($membership_data);

			if (is_wp_error($membership)) {
				return $this->rollback_and_return($membership);
			}

			$membership->add_note(
				[
					'text'      => __('Created via REST API', 'multisite-ultimate'),
					'author_id' => $customer->get_user_id(),
				]
			);

			$payment_data = $cart->to_payment_data();

			$payment_data = array_merge(
				$payment_data,
				wu_get_isset(
					$params,
					'payment',
					[
						'status' => Payment_Status::PENDING,
					]
				)
			);

			/*
			 * Unset the status because we are going to transition it later.
			 */
			$payment_status = $payment_data['status'];

			unset($payment_data['status']);

			$payment_data['customer_id']        = $customer->get_id();
			$payment_data['membership_id']      = $membership->get_id();
			$payment_data['gateway']            = wu_get_isset($payment_method, 'gateway');
			$payment_data['gateway_payment_id'] = wu_get_isset($payment_method, 'gateway_payment_id');

			$payment = wu_create_payment($payment_data);

			if (is_wp_error($payment)) {
				return $this->rollback_and_return($payment);
			}

			$payment->add_note(
				[
					'text'      => __('Created via REST API', 'multisite-ultimate'),
					'author_id' => $customer->get_user_id(),
				]
			);

			$site = false;

			/*
			 * Site creation.
			 */
			if (wu_get_isset($params, 'site')) {
				$site = $this->maybe_create_site($params, $membership);

				if (is_wp_error($site)) {
					return $this->rollback_and_return($site);
				}
			}

			/*
			 * Deal with status changes.
			 */
			if ($membership->get_status() !== $membership_status) {
				$membership->set_status($membership_status);

				$membership->save();

				/*
				 * The above change might trigger a site publication
				 * to take place, so we need to try to fetch the site
				 * again, this time as a WU Site object.
				 */
				if ($site) {
					$wp_site = get_site_by_path($site['domain'], $site['path']);

					if ($wp_site) {
						$site['id'] = $wp_site->blog_id;
					}
				}
			}

			if ($payment->get_status() !== $payment_status) {
				$payment->set_status($payment_status);

				$payment->save();
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('registration_error', $e->getMessage(), ['status' => 500]);
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		/*
		 * We have everything we need now.
		 */
		return [
			'membership' => $membership->to_array(),
			'customer'   => $customer->to_array(),
			'payment'    => $payment->to_array(),
			'site'       => $site ?: ['id' => 0],
		];
	}

	/**
	 * Returns the list of arguments allowed on to the endpoint.
	 *
	 * This is also used to build the documentation page for the endpoint.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_rest_args() {
		/*
		 * Billing Address Fields
		 */
		$billing_address_fields = Billing_Address::fields_for_rest(false);

		$customer_args = [
			'customer_id' => [
				'description' => __('The customer ID, if the customer already exists. If you also need to create a customer/wp user, use the "customer" property.', 'multisite-ultimate'),
				'type'        => 'integer',
			],
			'customer'    => [
				'description' => __('Customer data. Needs to be present when customer id is not.', 'multisite-ultimate'),
				'type'        => 'object',
				'properties'  => [
					'user_id'         => [
						'description' => __('Existing WordPress user id to attach this customer to. If you also need to create a WordPress user, pass the properties "username", "password", and "email".', 'multisite-ultimate'),
						'type'        => 'integer',
					],
					'username'        => [
						'description' => __('The customer username. This is used to create the WordPress user.', 'multisite-ultimate'),
						'type'        => 'string',
						'minLength'   => 4,
					],
					'password'        => [
						'description' => __('The customer password. This is used to create the WordPress user. Note that no validation is performed here to enforce strength.', 'multisite-ultimate'),
						'type'        => 'string',
						'minLength'   => 6,
					],
					'email'           => [
						'description' => __('The customer email address. This is used to create the WordPress user.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'email',
					],
					'billing_address' => [
						'type'       => 'object',
						'properties' => $billing_address_fields,
					],
				],
			],
		];

		$membership_args = [
			'membership' => [
				'description' => __('The membership data is automatically generated based on the cart info passed (e.g. products) but can be overridden with this property.', 'multisite-ultimate'),
				'type'        => 'object',
				'properties'  => [
					'status'                      => [
						'description' => __('The membership status.', 'multisite-ultimate'),
						'type'        => 'string',
						'enum'        => array_values(Membership_Status::get_allowed_list()),
						'default'     => Membership_Status::PENDING,
					],
					'date_expiration'             => [
						'description' => __('The membership expiration date. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
					'date_trial_end'              => [
						'description' => __('The membership trial end date. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
					'date_activated'              => [
						'description' => __('The membership activation date. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
					'date_renewed'                => [
						'description' => __('The membership last renewed date. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
					'date_cancellation'           => [
						'description' => __('The membership cancellation date. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
					'date_payment_plan_completed' => [
						'description' => __('The membership completion date. Used when the membership is limited to a limited number of billing cycles. Must be a valid PHP date format.', 'multisite-ultimate'),
						'type'        => 'string',
						'format'      => 'date-time',
					],
				],
			],
		];

		$payment_args = [
			'payment'        => [
				'description' => __('The payment data is automatically generated based on the cart info passed (e.g. products) but can be overridden with this property.', 'multisite-ultimate'),
				'type'        => 'object',
				'properties'  => [
					'status' => [
						'description' => __('The payment status.', 'multisite-ultimate'),
						'type'        => 'string',
						'enum'        => array_values(Payment_Status::get_allowed_list()),
						'default'     => Payment_Status::PENDING,
					],
				],
			],
			'payment_method' => [
				'description' => __('Payment method information. Useful when using the REST API to integrate other payment methods.', 'multisite-ultimate'),
				'type'        => 'object',
				'properties'  => [
					'gateway'                 => [
						'description' => __('The gateway name. E.g. stripe.', 'multisite-ultimate'),
						'type'        => 'string',
					],
					'gateway_customer_id'     => [
						'description' => __('The customer ID on the gateway system.', 'multisite-ultimate'),
						'type'        => 'string',
					],
					'gateway_subscription_id' => [
						'description' => __('The subscription ID on the gateway system.', 'multisite-ultimate'),
						'type'        => 'string',
					],
					'gateway_payment_id'      => [
						'description' => __('The payment ID on the gateway system.', 'multisite-ultimate'),
						'type'        => 'string',
					],
				],
			],
		];

		$site_args = [
			'site' => [
				'type'       => 'object',
				'properties' => [
					'site_url'    => [
						'type'        => 'string',
						'description' => __('The site subdomain or subdirectory (depending on your Multisite install). This would be "test" in "test.your-network.com".', 'multisite-ultimate'),
						'minLength'   => 4,
						'required'    => true,
					],
					'site_title'  => [
						'type'        => 'string',
						'description' => __('The site title. E.g. My Amazing Site', 'multisite-ultimate'),
						'minLength'   => 4,
						'required'    => true,
					],
					'publish'     => [
						'description' => __('If we should publish this site regardless of membership/payment status. Sites are created as pending by default, and are only published when a payment is received or the status of the membership changes to "active". This flag allows you to bypass the pending state.', 'multisite-ultimate'),
						'type'        => 'boolean',
						'default'     => false,
					],
					'template_id' => [
						'description' => __('The template ID we should copy when creating this site. If left empty, the value dictated by the products will be used.', 'multisite-ultimate'),
						'type'        => 'integer',
					],
					'site_meta'   => [
						'description' => __('An associative array of key values to be saved as site_meta.', 'multisite-ultimate'),
						'type'        => 'object',
					],
					'site_option' => [
						'description' => __('An associative array of key values to be saved as site_options. Useful for changing plugin settings and other site configurations.', 'multisite-ultimate'),
						'type'        => 'object',
					],
				],
			],
		];

		$cart_args = [
			'products'      => [
				'description' => __('The products to be added to this membership. Takes an array of product ids or slugs.', 'multisite-ultimate'),
				'uniqueItems' => true,
				'type'        => 'array',
			],
			'duration'      => [
				'description' => __('The membership duration.', 'multisite-ultimate'),
				'type'        => 'integer',
				'required'    => false,
			],
			'duration_unit' => [
				'description' => __('The membership duration unit.', 'multisite-ultimate'),
				'type'        => 'string',
				'default'     => 'month',
				'enum'        => [
					'day',
					'week',
					'month',
					'year',
				],
			],
			'discount_code' => [
				'description' => __('A discount code. E.g. PROMO10.', 'multisite-ultimate'),
				'type'        => 'string',
			],
			'auto_renew'    => [
				'description' => __('The membership auto-renew status. Useful when integrating with other payment options via this REST API.', 'multisite-ultimate'),
				'type'        => 'boolean',
				'default'     => false,
				'required'    => true,
			],
			'country'       => [
				'description' => __('The customer country. Used to calculate taxes and check if registration is allowed for that country.', 'multisite-ultimate'),
				'type'        => 'string',
				'default'     => '',
			],
			'currency'      => [
				'description' => __('The currency to be used.', 'multisite-ultimate'),
				'type'        => 'string',
			],
		];

		$args = array_merge($customer_args, $membership_args, $cart_args, $payment_args, $site_args);

		return apply_filters('wu_rest_register_endpoint_args', $args, $this);
	}

	/**
	 * Maybe create a customer, if needed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $p The request parameters.
	 * @return \WP_Ultimo\Models\Customer|\WP_Error
	 */
	public function maybe_create_customer($p) {

		$customer_id = wu_get_isset($p, 'customer_id');

		if ($customer_id) {
			$customer = wu_get_customer($customer_id);

			if ( ! $customer) {
				return new \WP_Error('customer_not_found', __('The customer id sent does not correspond to a valid customer.', 'multisite-ultimate'));
			}
		} else {
			$customer = wu_create_customer($p['customer']);
		}

		return $customer;
	}

	/**
	 * Undocumented function
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $p The request parameters.
	 * @param \WP_Ultimo\Models\Membership $membership The membership created.
	 * @return array|\WP_Ultimo\Models\Site\|\WP_Error
	 */
	public function maybe_create_site($p, $membership) {

		$site_data = $p['site'];

		/*
		 * Let's get a list of membership sites.
		 * This list includes pending sites as well.
		 */
		$sites = $membership->get_sites();

		/*
		 * Decide if we should create a new site or not.
		 *
		 * When should we create a new pending site?
		 * There are a couple of rules:
		 * - The membership must not have a pending site;
		 * - The membership must not have an existing site;
		 *
		 * The get_sites method already includes pending sites,
		 * so we can safely rely on it.
		 */
		if ( ! empty($sites)) {
			/*
			 * Returns the first site on that list.
			 * This is not ideal, but since we'll usually only have
			 * one site here, it's ok. for now.
			 */
			return current($sites);
		}

		$site_url = wu_get_isset($site_data, 'site_url');

		$d = wu_get_site_domain_and_path($site_url);

		/*
		 * Validates the site url.
		 */
		$results = wpmu_validate_blog_signup($site_url, wu_get_isset($site_data, 'site_title'), $membership->get_customer()->get_user());

		if ($results['errors']->has_errors()) {
			return $results['errors'];
		}

		/*
		 * Get the transient data to save with the site
		 * that way we can use it when actually registering
		 * the site on WordPress.
		 */
		$transient = array_merge(
			wu_get_isset($site_data, 'site_meta', []),
			wu_get_isset($site_data, 'site_option', [])
		);

		$template_id = apply_filters('wu_checkout_template_id', (int) wu_get_isset($site_data, 'template_id'), $membership, $this);

		$site_data = [
			'domain'         => $d->domain,
			'path'           => $d->path,
			'title'          => wu_get_isset($site_data, 'site_title'),
			'template_id'    => $template_id,
			'customer_id'    => $membership->get_customer()->get_id(),
			'membership_id'  => $membership->get_id(),
			'transient'      => $transient,
			'signup_meta'    => wu_get_isset($site_data, 'site_meta', []),
			'signup_options' => wu_get_isset($site_data, 'site_option', []),
			'type'           => Site_Type::CUSTOMER_OWNED,
		];

		$membership->create_pending_site($site_data);

		$site_data['id'] = 0;

		if (wu_get_isset($site_data, 'publish')) {
			$membership->publish_pending_site();

			$wp_site = get_site_by_path($site_data['domain'], $site_data['path']);

			if ($wp_site) {
				$site_data['id'] = $wp_site->blog_id;
			}
		}

		return $site_data;
	}

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

		return [
			'customer_id'       => 'required_without:customer',
			'customer'          => 'required_without:customer_id',
			'customer.username' => 'required_without_all:customer_id,customer.user_id',
			'customer.password' => 'required_without_all:customer_id,customer.user_id',
			'customer.email'    => 'required_without_all:customer_id,customer.user_id',
			'customer.user_id'  => 'required_without_all:customer_id,customer.username,customer.password,customer.email',
			'site.site_url'     => 'required_with:site|alpha_num|min:4|lowercase|unique_site',
			'site.site_title'   => 'required_with:site|min:4',
		];
	}

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @param array $args The params to validate.
	 * @return mixed[]|\WP_Error
	 */
	public function validate($args) {

		$validator = new \WP_Ultimo\Helpers\Validator();

		$validator->validate($args, $this->validation_rules());

		if ($validator->fails()) {
			return $validator->get_errors();
		}

		return true;
	}

	/**
	 * Rolls back database changes and returns the error passed.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Error $error The error to return.
	 * @return \WP_Error
	 */
	protected function rollback_and_return($error) {

		global $wpdb;

		$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return $error;
	}
}
