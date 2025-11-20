<?php
/**
 * Handles checkout validation.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Objects\Billing_Address;

/**
 * Handles all validation logic for checkout.
 *
 * @since 2.4.8
 */
class Checkout_Validator {

	/**
	 * The checkout type.
	 *
	 * @var string
	 */
	protected string $type = 'new';

	/**
	 * The session manager.
	 *
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	protected Checkout_Setup_Handler $setup_handler;

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
	 * Sets the session manager.
	 *
	 * @param Checkout_Session_Manager $session_manager The session manager.
	 * @param Checkout_Setup_Handler   $setup_handler The Setup Handler.
	 */
	public function __construct(
		Checkout_Session_Manager $session_manager,
		Checkout_Setup_Handler $setup_handler
	) {
		$this->session_manager = $session_manager;
		$this->setup_handler   = $setup_handler;
	}

	/**
	 * Returns the validation rules for the fields.
	 *
	 * @return array
	 */
	public function validation_rules(): array {

		$rules = [
			'email_address'      => 'required_without:user_id|email|unique:\WP_User,email',
			'email_address_conf' => 'same:email_address',
			'username'           => 'required_without:user_id|alpha_dash|min:4|lowercase|unique:\WP_User,login',
			'password'           => 'required_without:user_id|min:6',
			'password_conf'      => 'same:password',
			'template_id'        => 'integer|site_template',
			'products'           => 'products',
			'gateway'            => '',
			'valid_password'     => 'accepted',
			'billing_country'    => 'country|required_with:billing_country',
			'billing_zip_code'   => 'required_with:billing_zip_code',
			'billing_state'      => 'state',
			'billing_city'       => 'city',
		];

		/*
		 * Add rules for the site when creating a new account.
		 */
		if ('new' === $this->type) {
			// char limit according https://datatracker.ietf.org/doc/html/rfc1034#section-3.1
			$rules['site_title'] = 'min:4';
			$rules['site_url']   = 'min:3|max:63|lowercase|unique_site';
		}

		return apply_filters('wu_checkout_validation_rules', $rules, $this);
	}

	/**
	 * Returns the list of validation rules.
	 *
	 * If we are dealing with a step submission, we will return
	 * only the validation rules that refer to the keys sent via POST.
	 *
	 * @return array
	 */
	public function get_validation_rules(): array {

		$validation_rules = $this->validation_rules();

		$pre_flight    = $this->session_manager->get_request_value('pre-flight');
		$checkout_form = $this->session_manager->get_request_value('checkout_form');

		if ($pre_flight || 'wu-finish-checkout' === $checkout_form) {
			return [];
		}

		if ($this->setup_handler->get_step_name() && false === $this->setup_handler->is_last_step()) {
			$fields_available = array_column($this->setup_handler->get_step()['fields'] ?? [], 'id');

			/*
			 * Re-adds the template id check
			 */
			$template_id = $this->session_manager->get_request_value('template_id');
			if (null !== $template_id) {
				$fields_available[] = 'template_id';
			}

			$validation_rules = array_filter($validation_rules, fn($rule) => in_array($rule, $fields_available, true), ARRAY_FILTER_USE_KEY);
		}

		// We'll use this to validate product fields
		$product_fields = [
			'pricing_table',
			'products',
		];

		/**
		 * Add the additional required fields.
		 */
		foreach ($this->setup_handler->get_step()['fields'] ?? [] as $field) {
			/*
			 * General required fields
			 */
			if (wu_get_isset($field, 'required') && wu_get_isset($field, 'id')) {
				if (isset($validation_rules[ $field['id'] ])) {
					$validation_rules[ $field['id'] ] .= '|required';
				} else {
					$validation_rules[ $field['id'] ] = 'required';
				}
			}

			/*
			 * Product fields
			 */
			if (wu_get_isset($field, 'id') && in_array($field['id'], $product_fields, true)) {
				$validation_rules['products'] = 'products|required';
			}
		}

		/**
		 * Allow plugin developers to filter the validation rules.
		 *
		 * @param array                $validation_rules The validation rules to be used.
		 * @param Checkout_Validator $validator The validator class.
		 */
		return apply_filters('wu_checkout_validation_rules', $validation_rules, $this);
	}

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @param array|null $rules Custom rules to use instead of the default ones.
	 *
	 * @return true|\WP_Error
	 */
	public function validate(?array $rules = null) {

		$validator = new \WP_Ultimo\Helpers\Validator();

		$session = $this->session_manager->get_signup_data();

		// Get request data from the session manager or fall back to $_REQUEST
		$request_data = $this->session_manager->get_request_data();

		// Build the data stack from session and request
		if (is_array($session)) {
			$stack = array_merge($session, $request_data);
		} else {
			$stack = $request_data;
		}

		if (null === $rules) {
			$rules = $this->get_validation_rules();
		}

		$base_aliases = [];

		$checkout_form_fields = $this->setup_handler->get_checkout_form()->get_all_fields();

		// Add current form fields
		foreach ($checkout_form_fields as $field) {
			$base_aliases[ $field['id'] ] = wu_get_isset($field, 'name', '');
		}

		// Add Billing Address fields
		foreach (Billing_Address::fields() as $field_key => $field) {
			$base_aliases[ $field_key ] = wu_get_isset($field, 'title', '');
		}

		// Add some hidden or compound fields ids
		$validation_aliases = array_merge(
			[
				'password_conf'      => __('Password confirmation', 'ultimate-multisite'),
				'email_address_conf' => __('Email confirmation', 'ultimate-multisite'),
				'template_id'        => __('Template ID', 'ultimate-multisite'),
				'valid_password'     => __('Valid password', 'ultimate-multisite'),
				'products'           => __('Products', 'ultimate-multisite'),
				'gateway'            => __('Payment Gateway', 'ultimate-multisite'),
			],
			$base_aliases
		);

		/**
		 * Allow plugin developers to add custom aliases in form validator.
		 *
		 * @param array              $validation_aliases The array with id => alias.
		 * @param Checkout_Validator $validator The validator class.
		 */
		$validation_aliases = apply_filters('wu_checkout_validation_aliases', $validation_aliases, $this);

		$validator->validate($stack, $rules, $validation_aliases);

		if ($validator->fails()) {
			$errors = $validator->get_errors();

			$errors->remove('valid_password');

			return $errors;
		}

		return true;
	}
}
