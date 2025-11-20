<?php
/**
 * Handles checkout setup and configuration.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Sites\Site_Type;
use WP_Ultimo\Database\Payments\Payment_Status;

/**
 * Handles checkout setup, session management, and rewrite rules.
 *
 * @since 2.4.8
 */
class Checkout_Setup_Handler {

	/**
	 * Session manager object.
	 *
	 * @var Checkout_Session_Manager
	 */
	protected Checkout_Session_Manager $session_manager;

	/**
	 * The current checkout form being used.
	 *
	 * @var \WP_Ultimo\Models\Checkout_Form|null
	 */
	protected ?\WP_Ultimo\Models\Checkout_Form $checkout_form = null;

	/**
	 * List of steps for the signup flow.
	 *
	 * @var array
	 */
	protected array $steps = [];

	/**
	 * Current step of the signup flow.
	 *
	 * @var array
	 */
	protected array $step = [];

	/**
	 * The name of the current step.
	 *
	 * @var string
	 */
	protected string $step_name = '';

	/**
	 * Checks if a list of fields has an auto-submittable field.
	 *
	 * @var false|string
	 */
	protected $auto_submittable_field;

	/**
	 * Constructor.
	 *
	 * @param Checkout_Session_Manager $session_manager The session manager.
	 */
	public function __construct(Checkout_Session_Manager $session_manager) {

		$this->session_manager = $session_manager;
	}

	/**
	 * Sets up the checkout environment.
	 *
	 * @param \WP_Ultimo\UI\Checkout_Element|null $element The checkout element.
	 *
	 * @return void
	 */
	public function setup(?\WP_Ultimo\UI\Checkout_Element $element = null): void {

		// Initialize request_data with all POST/REQUEST data
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		$request_data = array_merge($_GET, $_POST);

		$checkout_form_slug = wu_request('checkout_form');

		if (wu_request('pre-flight')) {
			$checkout_form_slug = false;

			// Store pre-selected data without mutating superglobals
			$request_data['pre_selected'] = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( ! $checkout_form_slug && is_a($element, \WP_Ultimo\UI\Checkout_Element::class)) {
			$pre_loaded_checkout_form_slug = $element->get_pre_loaded_attribute('slug', $checkout_form_slug);

			$checkout_form_slug = $pre_loaded_checkout_form_slug ?: $checkout_form_slug;
		}

		$this->checkout_form = wu_get_checkout_form_by_slug($checkout_form_slug) ?: null;

		// Handle resume checkout from URL
		$this->handle_resume_checkout();

		// Handle cancel pending payment request
		$this->handle_cancel_pending_payment();

		// Load from draft payment if exists
		$this->load_draft_payment();

		if ($this->checkout_form) {
			$this->setup_steps();
		}

		// Store user_id without mutating superglobals
		if (is_user_logged_in()) {
			$request_data['user_id'] = get_current_user_id();
		}
		$this->session_manager->set_request_data($request_data);

		wu_no_cache(); // Prevent the registration page from being cached.
	}

	/**
	 * Sets up the checkout steps.
	 *
	 * @return void
	 */
	protected function setup_steps(): void {

		$this->steps = $this->checkout_form->get_steps_to_show();

		$first_step = current($this->steps);

		$step_name = wu_request('checkout_step', wu_get_isset($first_step, 'id', 'checkout'));

		$this->step_name = $step_name;

		$this->step = $this->checkout_form->get_step($this->step_name, true);

		if (! $this->step) {
			$this->step = [];
		}

		$this->step['fields'] ??= [];

		$this->auto_submittable_field = $this->contains_auto_submittable_field($this->step['fields']);

		$this->step['fields'] = wu_create_checkout_fields($this->step['fields']);
	}

	/**
	 * Handles resuming checkout from a URL parameter.
	 *
	 * @return void
	 */
	protected function handle_resume_checkout(): void {

		$resume_hash = wu_request('resume_checkout');
		if ($resume_hash) {
			$resume_payment = wu_get_payment_by_hash($resume_hash);
			if ($resume_payment && $resume_payment->get_status() === Payment_Status::DRAFT) {
				$this->session_manager->set_draft_payment_id($resume_payment->get_id());
				$saved_session = $resume_payment->get_meta('checkout_session');
				if ($saved_session) {
					$this->session_manager->set_signup_data($saved_session);
				}
			}
		}
	}

	/**
	 * Handles canceling a pending payment from request.
	 *
	 * @return void
	 */
	protected function handle_cancel_pending_payment(): void {

		if (wu_request('cancel_pending_payment')) {
			$payment_id = wu_request('cancel_pending_payment');
			$payment    = wu_get_payment($payment_id);
			if ($payment && $payment->get_status() === Payment_Status::PENDING && $this->can_user_cancel_payment($payment)) {
				$payment->set_status(Payment_Status::CANCELLED);
				$payment->save();
				// Clear session if it was this payment
				if ((string) $this->session_manager->get_payment_id() === (string) $payment_id) {
					$this->session_manager->set_payment_id(null);
				}
			}
		}
	}

	/**
	 * Loads draft payment data from the session.
	 *
	 * @return void
	 */
	protected function load_draft_payment(): void {

		$draft_payment_id = $this->session_manager->get_draft_payment_id();
		if ($draft_payment_id) {
			$draft_payment = wu_get_payment($draft_payment_id);
			if ($draft_payment && $draft_payment->get_status() === Payment_Status::DRAFT) {
				$saved_session = $draft_payment->get_meta('checkout_session');
				if ($saved_session) {
					$current_signup = $this->session_manager->get_signup_data() ?? [];
					$this->session_manager->set_signup_data(array_merge($current_signup, $saved_session));
				}
			} else {
				// Invalid draft, remove
				$this->session_manager->set_draft_payment_id(null);
			}
		}
	}

	/**
	 * Checks if the current user can cancel a payment.
	 *
	 * @param \WP_Ultimo\Models\Payment $payment The payment object.
	 *
	 * @return bool
	 */
	protected function can_user_cancel_payment(\WP_Ultimo\Models\Payment $payment): bool {

		if (! is_user_logged_in()) {
			return false;
		}

		$customer = wu_get_current_customer();
		return $customer && $customer->get_id() === $payment->get_customer_id();
	}

	/**
	 * Add checkout rewrite rules.
	 *
	 * @return void
	 */
	public function add_rewrite_rules(): void {

		$register = Checkout_Pages::get_instance()->get_signup_page('register');

		if ( ! is_a($register, '\WP_Post')) {
			return;
		}

		$register_slug = $register->post_name;

		/*
		 * The first rewrite rule.
		 * Example: site.com/register/premium
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&wu_preselected=products',
			'top'
		);

		/*
		 * This one is here for backwards compatibility.
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)\/([0-9]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&duration=$matches[2]&duration_unit=month&wu_preselected=products',
			'top'
		);

		/*
		 * Full URL structure: /register/premium/1/year
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)\/([0-9]+)[\/]?([a-z]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&duration=$matches[2]&duration_unit=$matches[3]&wu_preselected=products',
			'top'
		);

		/*
		 * Template site pre-selection.
		 */
		$template_slug = apply_filters('wu_template_selection_rewrite_rule_slug', 'template', $register_slug);

		add_rewrite_rule(
			"{$register_slug}\/{$template_slug}\/([0-9a-zA-Z-_]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&template_name=$matches[1]&wu_preselected=template_id',
			'top'
		);
	}

	/**
	 * Filters the wu_request with the query vars.
	 *
	 * @param mixed  $value The value from wu_request.
	 * @param string $key The key value.
	 *
	 * @return mixed
	 */
	public function get_checkout_from_query_vars($value, string $key) {

		if ( ! did_action('wp')) {
			return $value;
		}

		$from_query = get_query_var($key);

		$cart_arguments = apply_filters(
			'wu_get_checkout_from_query_vars',
			[
				'products',
				'duration',
				'duration_unit',
				'template_id',
				'wu_preselected',
				'resume_checkout',
			]
		);

		/**
		 * Deal with site templates in a specific manner.
		 */
		if ('template_id' === $key) {
			$template_name = get_query_var('template_name', null);

			if (null !== $template_name) {
				$d = wu_get_site_domain_and_path($template_name);

				$wp_site = get_site_by_path($d->domain, $d->path);

				$site = $wp_site ? wu_get_site((int) $wp_site->blog_id) : false;

				if ($site && $site->get_type() === Site_Type::SITE_TEMPLATE) {
					return $site->get_id();
				}
			}
		}

		/*
		 * Otherwise, simply check for its existence
		 * on the query object.
		 */
		if (in_array($key, $cart_arguments, true) && $from_query) {
			return $from_query;
		}

		return $value;
	}

	/**
	 * Checks if a list of fields has an auto-submittable field.
	 *
	 * @param array|false $fields The list of fields of a step we need to check.
	 *
	 * @return false|string False if no auto-submittable field is present, the field to watch otherwise.
	 */
	public function contains_auto_submittable_field($fields) {

		$relevant_fields = [];

		$field_types_to_ignore = [
			'hidden',
			'products',
			'submit_button',
			'period_selection',
			'steps',
		];

		// Extra check to prevent error messages from being displayed.
		if ( ! is_array($fields)) {
			$fields = [];
		}

		foreach ($fields as $field) {
			if (in_array($field['type'], $field_types_to_ignore, true)) {
				continue;
			}

			$relevant_fields[] = $field;

			if (count($relevant_fields) > 1) {
				return false;
			}
		}

		if ( ! $relevant_fields) {
			return false;
		}

		$auto_submittable_field = $relevant_fields[0]['type'];

		return wu_get_isset($this->get_auto_submittable_fields(), $auto_submittable_field);
	}

	/**
	 * Returns a list of auto-submittable fields.
	 *
	 * @return array
	 */
	public function get_auto_submittable_fields(): array {

		$auto_submittable_fields = [
			'template_selection' => 'template_id',
			'pricing_table'      => 'products',
		];

		return apply_filters('wu_checkout_get_auto_submittable_fields', $auto_submittable_fields, $this);
	}

	/**
	 * Returns the name of the next step on the flow.
	 *
	 * @return string
	 */
	public function get_next_step_name(): string {

		$steps = $this->steps;

		$keys = array_column($steps, 'id');

		$current_step_index = array_search($this->step_name, array_values($keys), true);

		if (false === $current_step_index) {
			$current_step_index = 0;
		}

		$index = $current_step_index + 1;

		return $keys[ $index ] ?? $keys[ $current_step_index ];
	}

	/**
	 * Checks if we are in the first step of the signup.
	 *
	 * @return boolean
	 */
	public function is_first_step(): bool {

		$step_names = array_column($this->steps, 'id');

		if (empty($step_names)) {
			return true;
		}

		return array_shift($step_names) === $this->step_name;
	}

	/**
	 * Checks if we are in the last step of the signup.
	 *
	 * @return boolean
	 */
	public function is_last_step(): bool {

		if ($this->session_manager->get_request_value('pre-flight')) {
			return false;
		}

		$step_names = array_column($this->steps, 'id');

		if (empty($step_names)) {
			return true;
		}

		return array_pop($step_names) === $this->step_name;
	}

	/**
	 * Gets the checkout form.
	 *
	 * @return \WP_Ultimo\Models\Checkout_Form|null
	 */
	public function get_checkout_form(): ?\WP_Ultimo\Models\Checkout_Form {

		return $this->checkout_form;
	}

	/**
	 * Sets the checkout form.
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form|null $checkout_form The checkout form.
	 *
	 * @return void
	 */
	public function set_checkout_form(?\WP_Ultimo\Models\Checkout_Form $checkout_form): void {

		$this->checkout_form = $checkout_form;
	}

	/**
	 * Gets the steps.
	 *
	 * @return array
	 */
	public function get_steps(): array {

		return $this->steps;
	}

	/**
	 * Sets the steps.
	 *
	 * @param array $steps The steps.
	 *
	 * @return void
	 */
	public function set_steps(array $steps): void {
		$this->steps = $steps;
	}

	/**
	 * Gets the current step.
	 *
	 * @return array
	 */
	public function get_step(): array {

		return $this->step;
	}

	/**
	 * Set the current step.
	 *
	 * @param array $step The Step.
	 *
	 * @return void
	 */
	public function set_step(array $step): void {
		$this->step = $step;
	}

	/**
	 * Gets the step name.
	 *
	 * @return string
	 */
	public function get_step_name(): string {

		return $this->step_name;
	}

	/**
	 * Set's the current step's name.
	 *
	 * @param string $step_name The step name.
	 *
	 * @return void
	 */
	public function set_step_name(string $step_name): void {
		$this->step_name = $step_name;
	}

	/**
	 * Gets the auto-submittable field.
	 *
	 * @return false|string
	 */
	public function get_auto_submittable_field_value() {

		return $this->auto_submittable_field;
	}
}
