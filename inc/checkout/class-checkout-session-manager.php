<?php
/**
 * Handles checkout session management.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Contracts\Session;
use WP_Ultimo\Database\Payments\Payment_Status;

/**
 * Manages checkout sessions, draft payments, and related operations.
 *
 * @since 2.4.8
 */
class Checkout_Session_Manager {

	/**
	 * Session object.
	 *
	 * @var Session
	 */
	protected Session $session;

	/**
	 * Stored request data.
	 *
	 * @var array|null
	 */
	protected ?array $request_data = null;

	/**
	 * Constructor.
	 *
	 * @param Session|null $session The session object.
	 */
	public function __construct(?Session $session = null) {

		$this->session = $session ?? wu_get_session('signup');
	}

	/**
	 * Gets the session object.
	 *
	 * @return Session
	 */
	public function get_session(): Session {

		return $this->session;
	}

	/**
	 * Sets the request data to use instead of wu_request().
	 *
	 * @param array $request_data The request data.
	 * @return void
	 */
	public function set_request_data(array $request_data): void {

		$this->request_data = $request_data;
	}

	/**
	 * Gets the stored request data.
	 *
	 * @return array|null
	 */
	public function get_request_data(): ?array {

		return $this->request_data;
	}

	/**
	 * Gets a value from stored request data or falls back to wu_request().
	 *
	 * @param string $key The key to retrieve.
	 * @param mixed  $default_value The default value if not found.
	 *
	 * @return mixed
	 */
	public function get_request_value(string $key, $default_value = false) {

		if (null !== $this->request_data && array_key_exists($key, $this->request_data)) {
			return $this->request_data[ $key ];
		}

		return wu_request($key, $default_value);
	}

	/**
	 * Gets the info either from the request or session.
	 *
	 * @param string $key Key to retrieve the value for.
	 * @param mixed  $default_value The default value to return when nothing is found.
	 *
	 * @return mixed
	 */
	public function request_or_session(string $key, $default_value = false) {

		$value = $default_value;

		$session = $this->session->get('signup');

		if (isset($session[ $key ])) {
			$value = $session[ $key ];
		}

		return $this->get_request_value($key, $value);
	}

	/**
	 * Creates a draft payment for incomplete checkouts.
	 *
	 * @param array $products List of products.
	 *
	 * @return void
	 */
	public function create_draft_payment(array $products): void {

		$cart = new Cart(
			[
				'products'      => $products,
				'cart_type'     => 'new',
				'duration'      => $this->request_or_session('duration'),
				'duration_unit' => $this->request_or_session('duration_unit'),
			]
		);

		if ($cart->is_valid() === false) {
			return; // Don't create draft if cart is invalid
		}

		$payment_data           = $cart->to_payment_data();
		$payment_data['status'] = Payment_Status::DRAFT;

		$payment = wu_create_payment($payment_data);

		if ($payment && ! is_wp_error($payment)) {
			$this->session->set('draft_payment_id', $payment->get_id());

			// Filter sensitive data before storing
			$session_data = $this->get_filtered_session_data();
			$payment->update_meta('checkout_session', $session_data);

			$this->session->commit();
		} else {
			wu_log_add('checkout', 'Unable to create draft payment');
		}
	}

	/**
	 * Gets session data with sensitive information filtered out.
	 *
	 * @return array
	 */
	protected function get_filtered_session_data(): array {

		$session_data = $this->session->get('signup') ?? [];

		// Remove sensitive data
		$sensitive_keys = ['password', 'password_conf', 'card_number', 'cvv', 'cvc'];

		foreach ($sensitive_keys as $key) {
			unset($session_data[ $key ]);
		}

		return $session_data;
	}

	/**
	 * Saves the current checkout progress to the draft payment.
	 *
	 * @return void
	 */
	public function save_draft_progress(): void {

		$draft_payment_id = $this->session->get('draft_payment_id');
		if (! $draft_payment_id) {
			return;
		}

		$draft_payment = wu_get_payment($draft_payment_id);
		if (! $draft_payment || $draft_payment->get_status() !== Payment_Status::DRAFT) {
			return;
		}

		// Filter sensitive data before storing
		$session_data = $this->get_filtered_session_data();
		$draft_payment->update_meta('checkout_session', $session_data);
	}

	/**
	 * Cleans up expired draft and pending payments (older than 30 days).
	 *
	 * @return void
	 */
	public function cleanup_expired_drafts(): void {

		$expired_date = gmdate('Y-m-d H:i:s', strtotime('-30 days'));

		$expired_drafts = wu_get_payments(
			[
				'status'           => Payment_Status::DRAFT,
				'date_created__lt' => $expired_date,
			]
		);

		$expired_pendings = wu_get_payments(
			[
				'status'           => Payment_Status::PENDING,
				'date_created__lt' => $expired_date,
			]
		);

		foreach (array_merge($expired_drafts, $expired_pendings) as $payment) {
			if ($payment->get_status() === Payment_Status::PENDING) {
				$payment->set_status(Payment_Status::CANCELLED);
				$payment->save();
			} else {
				$payment->delete();
			}
		}
	}

	/**
	 * Handles cancel payment requests.
	 *
	 * @return void
	 */
	public function handle_cancel_payment(): void {

		$payment_id = $this->get_request_value('cancel_payment');
		if (! $payment_id) {
			return;
		}

		if (! wp_verify_nonce($this->get_request_value('_wpnonce'), 'cancel_payment_' . $payment_id)) {
			return;
		}

		$payment = wu_get_payment($payment_id);
		if (! $payment || $payment->get_status() !== Payment_Status::PENDING) {
			return;
		}

		if (! $this->can_user_cancel_payment($payment)) {
			return;
		}

		$payment->set_status(Payment_Status::CANCELLED);
		$payment->save();

		// Redirect back
		wp_safe_redirect(remove_query_arg(['cancel_payment', '_wpnonce']));
		exit;
	}

	/**
	 * Checks if the current user can cancel a payment.
	 *
	 * @param \WP_Ultimo\Models\Payment $payment The payment object.
	 *
	 * @return bool
	 */
	public function can_user_cancel_payment(\WP_Ultimo\Models\Payment $payment): bool {

		if (! is_user_logged_in()) {
			return false;
		}

		$customer = wu_get_current_customer();
		return $customer && $customer->get_id() === $payment->get_customer_id();
	}

	/**
	 * Clears the signup session.
	 *
	 * @return void
	 */
	public function clear_session(): void {

		$this->session->set('signup', []);
		$this->session->commit();
	}

	/**
	 * Adds values to the signup session.
	 *
	 * @param array $values The values to add.
	 * @return void
	 */
	public function add_to_session(array $values): void {

		$this->session->add_values('signup', $values);
		$this->session->commit();
	}

	/**
	 * Sets errors in the session.
	 *
	 * @param \WP_Error $errors The errors.
	 * @return void
	 */
	public function set_errors(\WP_Error $errors): void {

		$this->session->set('errors', $errors);
	}

	/**
	 * Gets the signup data from the session.
	 *
	 * @return array|null
	 */
	public function get_signup_data(): ?array {

		return $this->session->get('signup');
	}

	/**
	 * Sets the signup data in session.
	 *
	 * @param array $data The signup data.
	 * @return void
	 */
	public function set_signup_data(array $data): void {

		$this->session->set('signup', $data);
	}

	/**
	 * Gets a value from a session.
	 *
	 * @param string $key The session key.
	 * @return mixed
	 */
	public function get(string $key) {

		return $this->session->get($key);
	}

	/**
	 * Sets a value in session.
	 *
	 * @param string $key The session key.
	 * @param mixed  $value The value to set.
	 * @return void
	 */
	public function set(string $key, $value): void {

		$this->session->set($key, $value);
	}

	/**
	 * Gets the draft payment ID from the session.
	 *
	 * @return int|null
	 */
	public function get_draft_payment_id(): ?int {

		return $this->session->get('draft_payment_id');
	}

	/**
	 * Sets the draft payment ID in session.
	 *
	 * @param int|null $payment_id The payment ID.
	 *
	 * @return void
	 */
	public function set_draft_payment_id(?int $payment_id): void {

		$this->session->set('draft_payment_id', $payment_id);
	}

	/**
	 * Gets the payment ID from the session.
	 *
	 * @return int|null
	 */
	public function get_payment_id(): ?int {

		return $this->session->get('payment_id');
	}

	/**
	 * Sets the payment ID in session.
	 *
	 * @param int|null $payment_id The payment ID.
	 *
	 * @return void
	 */
	public function set_payment_id(?int $payment_id): void {

		$this->session->set('payment_id', $payment_id);
	}

	/**
	 * Adds values to a session key using add_values.
	 *
	 * @param string $key The session key.
	 * @param array  $values The values to add.
	 *
	 * @return void
	 */
	public function add_values(string $key, array $values): void {

		$this->session->add_values($key, $values);
	}

	/**
	 * Commits the session.
	 *
	 * @return void
	 */
	public function commit(): void {

		$this->session->commit();
	}
}
