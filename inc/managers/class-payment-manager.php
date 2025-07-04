<?php
/**
 * Payment Manager
 *
 * Handles processes related to payments.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Payment_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Payment;
use WP_Ultimo\Logger;
use WP_Ultimo\Invoices\Invoice;
use WP_Ultimo\Checkout\Cart;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to payments.
 *
 * @since 2.0.0
 */
class Payment_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api;
	use \WP_Ultimo\Apis\WP_CLI;
	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'payment';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = \WP_Ultimo\Models\Payment::class;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		$this->register_forms();

		add_action(
			'init',
			function () {
				Event_Manager::register_model_events(
					'payment',
					__('Payment', 'multisite-ultimate'),
					['created', 'updated']
				);
			}
		);
		add_action('wp_login', [$this, 'check_pending_payments'], 10);

		add_action('wp_enqueue_scripts', [$this, 'show_pending_payments'], 10);

		add_action('admin_enqueue_scripts', [$this, 'show_pending_payments'], 10);

		add_action('init', [$this, 'invoice_viewer']);

		add_action('wu_async_transfer_payment', [$this, 'async_transfer_payment'], 10, 2);

		add_action('wu_async_delete_payment', [$this, 'async_delete_payment'], 10);

		add_action('wu_gateway_payment_processed', [$this, 'handle_payment_success'], 10, 3);

		add_action('wu_transition_payment_status', [$this, 'transition_payment_status'], 10, 3);
	}

	/**
	 * Triggers the do_event of the payment successful.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment        $payment The payment.
	 * @param \WP_Ultimo\Models\Membership     $membership The membership.
	 * @param \WP_Ultimo\Gateways\Base_Gateway $gateway The gateway.
	 * @return void
	 */
	public function handle_payment_success($payment, $membership, $gateway): void {

		$payload = array_merge(
			wu_generate_event_payload('payment', $payment),
			wu_generate_event_payload('membership', $membership),
			wu_generate_event_payload('customer', $membership->get_customer())
		);

		wu_do_event('payment_received', $payload);
	}

	/**
	 * Check if current customer haves pending payments
	 *
	 * @param \WP_User|string $user The WordPress user instance or user login.
	 * @return void
	 */
	public function check_pending_payments($user): void {

		if ( ! is_main_site()) {
			return;
		}

		if ( ! is_a($user, '\WP_User')) {
			$user = get_user_by('login', $user);
		}

		if ( ! $user) {
			return;
		}

		$customer = wu_get_customer_by_user_id($user->ID);

		if ( ! $customer) {
			return;
		}

		foreach ($customer->get_memberships() as $membership) {
			$pending_payment = $membership->get_last_pending_payment();

			if ($pending_payment) {
				add_user_meta($user->ID, 'wu_show_pending_payment_popup', true, true);

				break;
			}
		}
	}

	/**
	 * Add and trigger a popup in screen with the pending payments
	 *
	 * @return void
	 */
	public function show_pending_payments(): void {

		if ( ! is_user_logged_in()) {
			return;
		}

		$user_id = get_current_user_id();

		$show_pending_payment = get_user_meta($user_id, 'wu_show_pending_payment_popup', true);

		if ( ! $show_pending_payment) {
			return;
		}

		wp_enqueue_style('dashicons');
		wp_enqueue_style('wu-admin');
		add_wubox();

		$form_title = __('Pending Payments', 'multisite-ultimate');
		$form_url   = wu_get_form_url('pending_payments');

		wp_add_inline_script('wubox', "document.addEventListener('DOMContentLoaded', function(){wubox.show('$form_title', '$form_url');});");

		// Show only after user login
		delete_user_meta($user_id, 'wu_show_pending_payment_popup');
	}

	/**
	 * Register the form showing the pending payments of current customer
	 *
	 * @return void
	 */
	public function register_forms(): void {

		if (function_exists('wu_register_form')) {
			wu_register_form(
				'pending_payments',
				[
					'render'     => [$this, 'render_pending_payments'],
					'capability' => 'exist',
				]
			);
		}
	}

	/**
	 * Add customerr pending payments
	 *
	 * @return void
	 */
	public function render_pending_payments(): void {

		if ( ! is_user_logged_in()) {
			return;
		}

		$user_id = get_current_user_id();

		$customer = wu_get_customer_by_user_id($user_id);

		if ( ! $customer) {
			return;
		}

		$pending_payments = [];

		foreach ($customer->get_memberships() as $membership) {
			$pending_payment = $membership->get_last_pending_payment();

			if ($pending_payment) {
				$pending_payments[] = $pending_payment;
			}
		}

		$message = ! empty($pending_payments) ? __('You have pending payments on your account!', 'multisite-ultimate') : __('You do not have pending payments on your account!', 'multisite-ultimate');

		/**
		 * Allow user to change the message about the pending payments.
		 *
		 * @since 2.0.19
		 *
		 * @param string                      $message          The message to print.
		 * @param \WP_Ultimo\Models\Customer  $customer         The current customer.
		 * @param array                       $pending_payments A list with pending payments.
		 */
		$message = apply_filters('wu_pending_payment_message', $message, $customer, $pending_payments);

		$fields = [
			'alert_text' => [
				'type'            => 'note',
				'desc'            => $message,
				'classes'         => '',
				'wrapper_classes' => '',
			],
		];

		foreach ($pending_payments as $payment) {
			$slug = $payment->get_hash();

			$url = $payment->get_payment_url();

			$html = sprintf('<a href="%s" class="button-primary">%s</a>', $url, __('Pay Now', 'multisite-ultimate'));

			$title = $slug;

			$fields[] = [
				'type'  => 'note',
				'title' => $title,
				'desc'  => $html,
			];
		}

		$form = new \WP_Ultimo\UI\Form(
			'pending-payments',
			$fields,
			[
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			]
		);

		$form->render();
	}

	/**
	 * Adds an init endpoint to render the invoices.
	 *
	 * @todo rewrite this to use rewrite rules.
	 * @since 2.0.0
	 * @return void
	 */
	public function invoice_viewer(): void {

		if (wu_request('action') === 'invoice' && wu_request('reference') && wu_request('key')) {
			/*
			 * Validates nonce.
			 */
			if ( ! wp_verify_nonce(wu_request('key'), 'see_invoice')) {
				wp_die(esc_html__('You do not have permissions to access this file.', 'multisite-ultimate'));
			}

			$payment = wu_get_payment_by_hash(wu_request('reference'));

			if ( ! $payment) {
				wp_die(esc_html__('This invoice does not exist.', 'multisite-ultimate'));
			}

			$invoice = new Invoice($payment);

			/*
			 * Displays the PDF on the screen.
			 */
			$invoice->print_file();

			exit;
		}
	}

	/**
	 * Transfer a payment from a user to another.
	 *
	 * @since 2.0.0
	 *
	 * @param int $payment_id The ID of the payment being transferred.
	 * @param int $target_customer_id The new owner.
	 * @return mixed
	 */
	public function async_transfer_payment($payment_id, $target_customer_id) {

		global $wpdb;

		$payment = wu_get_payment($payment_id);

		$target_customer = wu_get_customer($target_customer_id);

		if ( ! $payment || ! $target_customer || $payment->get_customer_id() === $target_customer->get_id()) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {

			/**
			 * Change the payment
			 */
			$payment->set_customer_id($target_customer_id);

			$saved = $payment->save();

			if (is_wp_error($saved)) {
				$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				return $saved;
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('exception', $e->getMessage());
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return true;
	}

	/**
	 * Delete a payment.
	 *
	 * @since 2.0.0
	 *
	 * @param int $payment_id The ID of the payment being deleted.
	 * @return mixed
	 */
	public function async_delete_payment($payment_id) {

		global $wpdb;

		$payment = wu_get_payment($payment_id);

		if ( ! $payment) {
			return new \WP_Error('error', __('An unexpected error happened.', 'multisite-ultimate'));
		}

		$wpdb->query('START TRANSACTION'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		try {

			/**
			 * Change the payment
			 */
			$saved = $payment->delete();

			if (is_wp_error($saved)) {
				$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				return $saved;
			}
		} catch (\Throwable $e) {
			$wpdb->query('ROLLBACK'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return new \WP_Error('exception', $e->getMessage());
		}

		$wpdb->query('COMMIT'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		return true;
	}

	/**
	 * Watches the change in payment status to take action when needed.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $old_status The old status of the payment.
	 * @param string  $new_status The new status of the payment.
	 * @param integer $payment_id Payment ID.
	 * @return void
	 */
	public function transition_payment_status($old_status, $new_status, $payment_id) {

		$completable_statuses = [
			'completed',
		];

		if ( ! in_array($new_status, $completable_statuses, true)) {
			return;
		}

		$payment = wu_get_payment($payment_id);

		if ( ! $payment || $payment->get_saved_invoice_number()) {
			return;
		}

		$current_invoice_number = absint(wu_get_setting('next_invoice_number', 1));

		$payment->set_invoice_number($current_invoice_number);

		$payment->save();

		wu_save_setting('next_invoice_number', $current_invoice_number + 1);
	}
}
