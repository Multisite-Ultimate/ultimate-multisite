<?php
/**
 * Handles the processing of new membership purchases.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the processing of new membership purchases.
 *
 * @since 2.0.0
 */
class Checkout {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of steps for the signup flow.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public array $steps;

	/**
	 * Checkout type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected string $type = 'new';

	/**
	 * Check if setup method already runs.
	 *
	 * @since 2.0.18
	 * @var bool
	 */
	protected bool $already_setup = false;

	/**
	 * The gateway id.
	 *
	 * @since 2.1.2
	 * @var string|bool
	 */
	protected $gateway_id;

	/**
	 * The setup handler.
	 *
	 * @since 2.2.0
	 * @var Checkout_Setup_Handler
	 */
	protected Checkout_Setup_Handler $setup_handler;

	/**
	 * The validator.
	 *
	 * @since 2.2.0
	 * @var Checkout_Validator
	 */
	protected Checkout_Validator $validator;

	/**
	 * The entity factory.
	 *
	 * @since 2.2.0
	 * @var Checkout_Entity_Factory
	 */
	protected Checkout_Entity_Factory $entity_factory;

	/**
	 * The order processor.
	 *
	 * @since 2.2.0
	 * @var Checkout_Order_Processor
	 */
	protected Checkout_Order_Processor $order_processor;

	/**
	 * The session manager.
	 *
	 * @since 2.2.0
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	/**
	 * The script handler.
	 *
	 * @since 2.2.0
	 * @var Checkout_Script_Handler
	 */
	protected Checkout_Script_Handler $script_handler;

	/**
	 * Magic getter for backward compatibility.
	 *
	 * Allows access to properties that have been moved to component classes.
	 *
	 * @param string $name The property name.
	 *
	 * @return mixed
	 * @since 2.4.8
	 */
	public function __get(string $name) {

		switch ($name) {
			case 'errors':
				return $this->get_errors();
			case 'order':
				return $this->get_order();
			case 'customer':
				return $this->get_customer();
			case 'membership':
				return $this->get_membership();
			case 'payment':
				return $this->get_payment();
			case 'step':
				return $this->get_step();
			case 'steps':
				return $this->get_steps();
			case 'step_name':
				return $this->get_step_name();
			case 'checkout_form':
				return $this->get_checkout_form();

			default:
				return null;
		}
	}

	/**
	 * Magic method for backwards compatibility.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 *
	 * @return void
	 */
	public function __set(string $name, $value) {
		switch ($name) {
			case 'step':
				$this->set_step($value);
				return;
			case 'steps':
				$this->set_steps($value);
				return;
			case 'step_name':
				$this->set_step_name($value);
				return;
			case 'checkout_form':
				$this->set_checkout_form($value);
				return;
		}
	}

	/**
	 * Get step from Setup Handler.
	 *
	 * @return array
	 */
	public function get_step(): array {
		return $this->setup_handler->get_step();
	}

	/**
	 * Set step in Setup Handler.
	 *
	 * @param array $step The array of the step.
	 *
	 * @return void
	 */
	public function set_step(array $step): void {
		$this->setup_handler->set_step($step);
	}

	/**
	 * Get steps from Setup Handler.
	 *
	 * @return array
	 */
	public function get_steps(): array {
		return $this->setup_handler->get_steps();
	}

	/**
	 * Set steps in Setup Handler.
	 *
	 * @param array $steps The array of the steps.
	 * @return void
	 */
	public function set_steps(array $steps): void {
		$this->setup_handler->set_steps($steps);
	}

	/**
	 * Set the step name in Setup Handler.
	 *
	 * @param string $step_name The step name.
	 * @return void
	 */
	public function set_step_name(string $step_name): void {
		$this->setup_handler->set_step_name($step_name);
	}

	/**
	 * Get step name from Setup Handler.
	 *
	 * @return string
	 */
	public function get_step_name(): string {
		return $this->setup_handler->get_step_name();
	}

	/**
	 * Get checkout form from Setup Handler.
	 *
	 * @return \WP_Ultimo\Models\Checkout_Form|null
	 */
	public function get_checkout_form(): ?\WP_Ultimo\Models\Checkout_Form {
		return $this->setup_handler->get_checkout_form();
	}

	/**
	 * Set checkout form in Setup Handler.
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form|null $checkout_form The checkout form.
	 *
	 * @return void
	 */
	public function set_checkout_form(?\WP_Ultimo\Models\Checkout_Form $checkout_form): void {
		$this->setup_handler->set_checkout_form($checkout_form);
	}

	/**
	 * Gets the checkout errors.
	 *
	 * @since 2.2.0
	 * @return \WP_Error|null
	 */
	public function get_errors(): ?\WP_Error {

		return $this->order_processor->get_errors();
	}

	/**
	 * Gets the order/cart object.
	 *
	 * @since 2.2.0
	 * @return Cart|null
	 */
	public function get_order(): ?Cart {

		return $this->order_processor->get_order();
	}

	/**
	 * Gets the customer object.
	 *
	 * @since 2.2.0
	 * @return \WP_Ultimo\Models\Customer|null
	 */
	public function get_customer(): ?\WP_Ultimo\Models\Customer {

		return $this->order_processor->get_customer();
	}

	/**
	 * Gets the membership object.
	 *
	 * @since 2.2.0
	 * @return \WP_Ultimo\Models\Membership|null
	 */
	public function get_membership(): ?\WP_Ultimo\Models\Membership {

		return $this->order_processor->get_membership();
	}

	/**
	 * Gets the payment object.
	 *
	 * @since 2.2.0
	 * @return \WP_Ultimo\Models\Payment|null
	 */
	public function get_payment(): ?\WP_Ultimo\Models\Payment {

		return $this->order_processor->get_payment();
	}

	/**
	 * Initializes the Checkout singleton and adds hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		// Initialize component classes
		$this->session_manager = new Checkout_Session_Manager();
		$this->setup_handler   = new Checkout_Setup_Handler($this->session_manager);
		$this->validator       = new Checkout_Validator(
			$this->session_manager,
			$this->setup_handler
		);
		$this->entity_factory  = new Checkout_Entity_Factory(
			$this->session_manager,
			$this->setup_handler
		);
		$this->order_processor = new Checkout_Order_Processor(
			$this->session_manager,
			$this->entity_factory,
			$this->validator
		);
		$this->script_handler  = new Checkout_Script_Handler(
			$this->session_manager,
			$this->setup_handler
		);

		/*
		 * Setup and handle checkout
		 */
		add_action('wu_setup_checkout', [$this, 'setup_checkout']);

		add_action('wu_setup_checkout', [$this, 'maybe_process_checkout'], 20);

		/*
		 * Add the rewrite rules.
		 */
		add_action('init', [$this, 'add_rewrite_rules'], 20);

		// Schedule draft cleanup
		if (! wp_next_scheduled('wu_cleanup_draft_payments')) {
			wp_schedule_event(time(), 'daily', 'wu_cleanup_draft_payments');
		}
		add_action('wu_cleanup_draft_payments', [$this, 'cleanup_expired_drafts']);

		add_action('init', [$this, 'handle_cancel_payment']);

		add_filter('wu_request', [$this, 'get_checkout_from_query_vars'], 10, 2);

		/*
		 * Creates the order object to display to the customer
		 */
		add_action('wu_ajax_wu_create_order', [$this, 'create_order']);

		add_action('wu_ajax_nopriv_wu_create_order', [$this, 'create_order']);

		/*
		 * Validates form and process preflight.
		 */
		add_action('wu_ajax_wu_validate_form', [$this, 'maybe_handle_order_submission']);

		add_action('wu_ajax_nopriv_wu_validate_form', [$this, 'maybe_handle_order_submission']);
	}

	/**
	 * Add checkout rewrite rules.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_rewrite_rules(): void {

		$this->setup_handler->add_rewrite_rules();
	}

	/**
	 * Filters the wu_request with the query vars.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value The value from wu_request.
	 * @param string $key The key value.
	 * @return mixed
	 */
	public function get_checkout_from_query_vars($value, string $key) {

		return $this->setup_handler->get_checkout_from_query_vars($value, $key);
	}

	/**
	 * Sets up the necessary boilerplate code to have checkouts work.
	 *
	 * @param \WP_Ultimo\UI\Checkout_Element|null $element The checkout element.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function setup_checkout(?\WP_Ultimo\UI\Checkout_Element $element = null): void {

		if ($this->already_setup) {
			return;
		}

		$this->setup_handler->setup($element);

		$this->already_setup = true;

		// Create draft payment if products selected and no draft exists
		$products = $this->request_or_session('products', []);
		if (! empty($products) && ! $this->session_manager->get_draft_payment_id()) {
			$this->session_manager->create_draft_payment($products);
		}
	}

	/**
	 * Checks if a list of fields has an auto-submittable field.
	 *
	 * @since 2.0.4
	 *
	 * @param array $fields The list of fields of a step we need to check.
	 * @return false|string False if no auto-submittable field is present, the field to watch otherwise.
	 */
	public function contains_auto_submittable_field(array $fields) {

		return $this->setup_handler->contains_auto_submittable_field($fields);
	}

	/**
	 * Returns a list of auto-submittable fields.
	 *
	 * @since 2.0.4
	 * @return array
	 */
	public function get_auto_submittable_fields() {

		return $this->setup_handler->get_auto_submittable_fields();
	}

	/**
	 * Decides if we want to handle a step submission or a full checkout submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_handle_order_submission(): void {

		$this->setup_checkout();

		check_ajax_referer('wu_checkout');

		if ($this->is_last_step()) {
			$this->handle_order_submission();
		} else {
			$validation = $this->validator->validate();

			if (is_wp_error($validation)) {
				wp_send_json_error($validation);
			}

			// Auto-save progress to draft payment
			$this->session_manager->save_draft_progress();

			wp_send_json_success([]);
		}
	}

	/**
	 * Validates the order submission, and then delegates the processing to the gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_order_submission(): void {
		$this->order_processor->handle_order_submission($this);
	}

	/**
	 * Process an order.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error
	 */
	public function process_order() {
		return $this->order_processor->process_order($this);
	}

	/**
	 * Validates the checkout form to see if it's valid por not.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function validate_form(): void {

		$validation = $this->validate();

		if (is_wp_error($validation)) {
			wp_send_json_error($validation);
		}

		wp_send_json_success();
	}

	/**
	 * Creates an order object to display the order summary tables.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function create_order(): void {

		$this->setup_checkout();

		$this->order_processor->create_order();
	}

	/**
	 * Returns the checkout variables.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_checkout_variables() {
		return $this->script_handler->get_checkout_variables();
	}

	/**
	 * Returns the validation rules for the fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function validation_rules() {

		$this->validator->set_type($this->type);

		return $this->validator->validation_rules();
	}

	/**
	 * Returns the list of validation rules.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_validation_rules() {
		$this->validator->set_type($this->type);

		return $this->validator->get_validation_rules();
	}

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @param array $rules Custom rules to use instead of the default ones.
	 * @return true|\WP_Error
	 */
	public function validate($rules = null) {

		$this->validator->set_type($this->type);

		return $this->validator->validate($rules);
	}

	/**
	 * Decides if we are to process a checkout.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_process_checkout(): void {

		$this->setup_checkout();

		if ( ! $this->should_process_checkout()) {
			return;
		}

		if ($this->is_last_step()) {
			$results = $this->process_checkout();

			if (is_wp_error($results)) {
				$redirect_url = wu_get_current_url();

				$this->session_manager->set_errors($results);

				$payment = wu_get_isset($results->get_error_data(), 'payment');

				if ($payment) {
					$redirect_url = add_query_arg(
						[
							'payment' => $payment->get_hash(),
							'status'  => 'error',
						],
						$redirect_url
					);
				}

				wp_safe_redirect($redirect_url);

				exit;
			}
		} else {
			// Clean data and add it to the session - filter out checkout_ and _ prefixed keys
			$request_data = $this->session_manager->get_request_data() ?? $_POST; // phpcs:ignore WordPress.Security.NonceVerification
			$to_save      = array_filter($request_data, fn($item) => ! str_starts_with((string) $item, 'checkout_') && ! str_starts_with((string) $item, '_'), ARRAY_FILTER_USE_KEY);

			if (isset($to_save['pre-flight'])) {
				unset($to_save['pre-flight']);
				$this->session_manager->add_values('signup', ['pre_selected' => $to_save]);
			}

			$this->session_manager->add_to_session($to_save);

			if ( ! $this->session_manager->get_request_value('pre-flight')) {
				$next_step = $this->get_next_step_name();

				wp_safe_redirect(add_query_arg('step', $next_step));

				exit;
			}
		}
	}

	/**
	 * Runs pre-checks to see if we should process the checkout.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_process_checkout() {

		return $this->session_manager->get_request_value('checkout_action') === 'wu_checkout' && ! wp_doing_ajax();
	}

	/**
	 * Handles the checkout submission.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function process_checkout() {

		$this->setup_checkout();

		return $this->order_processor->process_checkout($this);
	}

	/**
	 * Handle user display names, if first and last names are available.
	 *
	 * @since 2.0.4
	 *
	 * @param string $display_name The current display name.
	 * @return string
	 */
	public function handle_display_name($display_name) {

		$first_name = $this->request_or_session('first_name', '');

		$last_name = $this->request_or_session('last_name', '');

		if ($first_name || $last_name) {
			$display_name = trim("$first_name $last_name");
		}

		return $display_name;
	}

	/*
	 * Helper methods
	 */

	/**
	 * Get thank you page URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_thank_you_page() {

		return wu_get_current_url();
	}

	/**
	 * Checks if the user already exists.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_existing_user() {

		return is_user_logged_in();
	}

	/**
	 * Returns the customer email verification status we want to use depending on the type of checkout.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_customer_email_verification_status() {

		$email_verification_setting = wu_get_setting('enable_email_verification', 'free_only');

		$order = $this->get_order();

		switch ($email_verification_setting) {
			case 'never':
				return 'none';

			case 'always':
				return 'pending';

			case 'free_only':
				return $order && $order->should_collect_payment() === false ? 'pending' : 'none';

			default:
				$should_confirm_email = (bool) $email_verification_setting;
				return $order && $order->should_collect_payment() === false && $should_confirm_email ? 'pending' : 'none';
		}
	}

	/**
	 * Gets the info either from the request or session.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Key to retrieve the value for.
	 * @param mixed  $default_value The default value to return, when nothing is found.
	 * @return mixed
	 */
	public function request_or_session($key, $default_value = false) {

		return $this->session_manager->request_or_session($key, $default_value);
	}

	/**
	 * Returns the name of the next step on the flow.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_next_step_name() {

		return $this->setup_handler->get_next_step_name();
	}

	/**
	 * Checks if we are in the first step of the signup.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_first_step(): bool {
		return $this->setup_handler->is_first_step();
	}

	/**
	 * Checks if we are in the last step of the signup.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_last_step(): bool {
		return $this->setup_handler->is_last_step();
	}

	/**
	 * Cleans up expired draft and pending payments (older than 30 days).
	 *
	 * @since 2.1.4
	 * @return void
	 */
	public function cleanup_expired_drafts(): void {

		$this->session_manager->cleanup_expired_drafts();
	}

	/**
	 * Handles cancel payment requests.
	 *
	 * @since 2.1.4
	 * @return void
	 */
	public function handle_cancel_payment(): void {

		$this->session_manager->handle_cancel_payment();
	}
}
