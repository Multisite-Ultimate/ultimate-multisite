<?php
/**
 * Handles checkout script registration.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manages checkout frontend scripts and variables.
 *
 * @since 2.4.8
 */
class Checkout_Script_Handler {

	/**
	 * The session manager.
	 *
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	/**
	 * The setup handler.
	 *
	 * @var Checkout_Setup_Handler
	 */
	protected Checkout_Setup_Handler $setup_handler;

	/**
	 * Constructor.
	 *
	 * @param Checkout_Session_Manager $session_manager The session manager.
	 * @param Checkout_Setup_Handler   $setup_handler The setup handler.
	 */
	public function __construct(Checkout_Session_Manager $session_manager, Checkout_Setup_Handler $setup_handler) {

		$this->session_manager = $session_manager;
		$this->setup_handler   = $setup_handler;

		/**
		 * Adds the necessary scripts
		 */
		add_action('wu_checkout_scripts', [$this, 'register_scripts']);
	}

	/**
	 * Adds the checkout scripts.
	 *
	 * @return void
	 */
	public function register_scripts(): void {

		$custom_css = apply_filters('wu_checkout_custom_css', '');

		if ($custom_css) {
			wp_add_inline_style('wu-checkout', $custom_css);
		}

		wp_enqueue_style('wu-checkout');

		wp_enqueue_style('wu-admin');

		wp_register_script('wu-checkout', wu_get_asset('checkout.js', 'js'), ['jquery-core', 'wu-vue', 'moment', 'wu-block-ui', 'wu-functions', 'password-strength-meter', 'underscore', 'wp-polyfill', 'wp-hooks', 'wu-cookie-helpers'], wu_get_version(), true);

		wp_localize_script('wu-checkout', 'wu_checkout', $this->get_checkout_variables());

		wp_enqueue_script('wu-checkout');
	}

	/**
	 * Returns the checkout variables.
	 *
	 * @return array
	 */
	public function get_checkout_variables(): array {
		/**
		 * Localized strings.
		 */
		$i18n = [
			'loading'        => __('Loading...', 'ultimate-multisite'),
			'added_to_order' => __('The item was added!', 'ultimate-multisite'),
			'weak_password'  => __('The Password entered is too weak.', 'ultimate-multisite'),
		];

		/*
		 * Get the default gateway.
		 */
		$default_gateway = current(array_keys(wu_get_active_gateway_as_options()));

		$d = wu_get_site_domain_and_path('replace');

		$site_domain = str_replace('replace.', '', (string) $d->domain);

		$duration      = $this->session_manager->request_or_session('duration');
		$duration_unit = $this->session_manager->request_or_session('duration_unit');

		// If duration is not set, we check for a previous period_selection field in form to use
		$steps = $this->setup_handler->get_steps();
		if (empty($duration) && $steps) {
			foreach ($steps as $step) {
				foreach ($step['fields'] as $field) {
					if ('period_selection' === $field['type']) {
						$duration      = $field['period_options'][0]['duration'];
						$duration_unit = $field['period_options'][0]['duration_unit'];

						break;
					}
				}

				if ($duration) {
					break;
				}
			}
		}

		// Deduplicate products early to fix the bug
		$session_products = $this->session_manager->request_or_session('products', []);
		$request_products = $this->session_manager->get_request_value('products', []);
		$products         = array_unique(array_merge($session_products, $request_products));

		$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

		/*
		 * Set the default variables.
		 */
		$variables = [
			'i18n'               => $i18n,
			'ajaxurl'            => wu_ajax_url(),
			'late_ajaxurl'       => wu_ajax_url('init'),
			'baseurl'            => remove_query_arg('pre-flight', wu_get_current_url()),
			'country'            => $this->session_manager->request_or_session('billing_country', $geolocation['country']),
			'state'              => $this->session_manager->request_or_session('billing_state', $geolocation['state']),
			'city'               => $this->session_manager->request_or_session('billing_city'),
			'duration'           => $duration,
			'duration_unit'      => $duration_unit,
			'site_url'           => $this->session_manager->request_or_session('site_url'),
			'site_domain'        => $this->session_manager->request_or_session('site_domain', preg_replace('#^https?://#', '', $site_domain)),
			'is_subdomain'       => is_subdomain_install(),
			'gateway'            => $this->session_manager->get_request_value('gateway', $default_gateway),
			'needs_billing_info' => true,
			'auto_renew'         => true,
			'products'           => $products,
		];

		/*
		 * Check for a payment parameter.
		 */
		$payment_hash = $this->session_manager->get_request_value('payment');

		$payment    = wu_get_payment_by_hash($payment_hash);
		$payment_id = $payment ? $payment->get_id() : 0;

		if ($payment_id) {
			$variables['payment_id'] = $payment_id;
		}

		/*
		 * Handle addons, upgrades and downgrades.
		 */
		$membership_hash = $this->session_manager->get_request_value('membership');

		$membership    = wu_get_membership_by_hash($membership_hash);
		$membership_id = $membership ? $membership->get_id() : 0;

		if ($membership_id) {
			$variables['membership_id'] = $membership_id;
		}

		[$plan, $other_products] = wu_segregate_products($variables['products']);

		$variables['plan'] = $plan ? $plan->get_id() : 0;

		/*
		 * Try to fetch the template_id
		 */
		$variables['template_id'] = $this->session_manager->request_or_session('template_id', 0);

		/*
		 * Create a cart object for the front-end.
		 */
		$variables['order'] = (new Cart($variables))->done();

		if ( ! empty($variables['order']->discount_code)) {
			$variables['discount_code'] = $variables['order']->discount_code->get_code();
		}

		/**
		 * Allow plugin developers to filter the pre-sets of a checkout page.
		 *
		 * @param array               $variables Localized variables.
		 * @param Checkout_Script_Handler $handler The script handler class.
		 * @return array The new variables array.
		 */
		return apply_filters('wu_get_checkout_variables', $variables, $this);
	}
}
