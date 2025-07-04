<?php

namespace WP_Ultimo\Gateways;

use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_Ultimo\Models\Customer;

class Manual_Gateway_Process_Checkout_Test extends \WP_UnitTestCase {
	/**
	 * @var \WP_Ultimo\Gateways\Manual_Gateway
	 */
	private $gateway;

	private static Customer $customer;


	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$customer = wu_create_customer(
			[
				'username' => 'testuser',
				'email'    => 'test@example.com',
				'password' => 'password123',
			]
		);
	}
	public function setUp(): void {
		parent::setUp();
		$this->gateway = new \WP_Ultimo\Gateways\Manual_Gateway();
		// Create customer
	}

	/**
	 * Data provider for test cases
	 */
	public function checkoutDataProvider(): array {
		return [
			'new_subscription'     => [
				'type'            => 'new',
				'recurring'       => true,
				'trial'           => false,
				'expected_status' => Membership_Status::ON_HOLD,
			],
			'new_with_trial'       => [
				'type'            => 'new',
				'recurring'       => true,
				'trial'           => true,
				'expected_status' => Membership_Status::TRIALING,
			],
			'retry_payment'        => [
				'type'            => 'retry',
				'recurring'       => true,
				'trial'           => false,
				'expected_status' => Membership_Status::ON_HOLD,
			],
			'upgrade_membership'   => [
				'type'            => 'upgrade',
				'recurring'       => true,
				'trial'           => false,
				'expected_status' => Membership_Status::ON_HOLD,
			],
			'downgrade_membership' => [
				'type'            => 'downgrade',
				'recurring'       => true,
				'trial'           => false,
				'expected_status' => Membership_Status::ON_HOLD,
			],
			'addon_purchase'       => [
				'type'            => 'addon',
				'recurring'       => true,
				'trial'           => false,
				'expected_status' => Membership_Status::ON_HOLD,
			],
		];
	}

	/**
	 * @dataProvider checkoutDataProvider
	 */
	public function test_process_checkout(string $type, bool $recurring, bool $trial, string $expected_status): void {
		$customer = self::$customer;
		wp_set_current_user($customer->get_user_id(), $customer->get_username());
		// Create first product
		$product = wu_create_product(
			[
				'name'                => 'Test Product',
				'slug'                => 'test-product',
				'amount'              => 50.00,
				'recurring'           => $recurring,
				'duration'            => 1,
				'duration_unit'       => 'month',
				'trial_duration'      => $trial ? 14 : 0,
				'trial_duration_unit' => 'day',
				'type'                => 'plan',
				'pricing_type'        => 'paid',
				'active'              => true,
			]
		);

		// Create a second product for upgrade/downgrade scenarios
		$second_product = wu_create_product(
			[
				'name'          => 'Second Product',
				'slug'          => 'second-product',
				'amount'        => 75.00,
				'recurring'     => $recurring,
				'duration'      => 1,
				'duration_unit' => 'month',
				'type'          => 'plan',
				'pricing_type'  => 'paid',
				'active'        => true,
			]
		);

		// Create membership
		$membership = wu_create_membership(
			[
				'customer_id' => $customer->get_id(),
				'plan_id'     => $product->get_id(),
				'status'      => 'active',
				'recurring'   => $recurring,
			]
		);

		// Create cart with appropriate settings
		$cart_args = [
			'cart_type'     => $type,
			'products'      => [$product->get_id()],
			'duration'      => 1,
			'duration_unit' => 'month',
			'membership_id' => ($type !== 'new') ? $membership->get_id() : false,
			'payment_id'    => false,
			'discount_code' => false,
			'auto_renew'    => $recurring,
			'country'       => 'US',
			'state'         => 'NY',
			'city'          => 'New York',
			'currency'      => 'USD',
		];

		$cart = new \WP_Ultimo\Checkout\Cart($cart_args);

		// Create a payment
		$payment = wu_create_payment(
			[
				'customer_id'   => $customer->get_id(),
				'membership_id' => $membership->get_id(),
				'gateway'       => 'manual',
				'status'        => 'pending',
				'total'         => $trial ? 0 : $product->get_amount(),
			]
		);

		// Process the checkout
		$this->gateway->process_checkout($payment, $membership, $customer, $cart, $type);

		// Check payment status
		if ($trial) {
			$this->assertEquals(
				Payment_Status::COMPLETED,
				$payment->get_status(),
				'Payment should be completed for trials'
			);
			$this->assertEquals(
				Membership_Status::TRIALING,
				$membership->get_status(),
				'Membership should be in trial status'
			);
		} elseif ($payment->get_total() === 0.00) {
			$this->assertEquals(
				Payment_Status::COMPLETED,
				$payment->get_status(),
				'Payment should be completed for zero amount'
			);
		} elseif ($type === 'upgrade' || $type === 'addon') {
			$this->assertEquals(
				Payment_Status::PENDING,
				$payment->get_status(),
				'Payment should be pending for upgrades and addons'
			);
		} else {
			$this->assertEquals(
				Payment_Status::PENDING,
				$payment->get_status(),
				'Payment should be pending for regular transactions'
			);
		}

		// Assert membership status
		if ($type === 'downgrade') {
			// For downgrades, verify scheduled swap
			$this->assertTrue((bool) $membership->get_scheduled_swap());
			$scheduled_swap = $membership->get_scheduled_swap();
			$this->assertEquals($product->get_id(), $scheduled_swap->order->get_plan_id());
		} elseif ($type === 'upgrade' || $type === 'addon') {
			// For upgrades and addons, verify immediate swap
			$this->assertEquals($product->get_id(), $membership->get_plan_id());
			$this->assertEquals($expected_status, $membership->get_status());
		}

		// Cleanup
		$payment->delete();
		$membership->delete();
		$product->delete();
		$second_product->delete();
	}

	public function tearDown(): void {

		parent::tearDown();
	}

	public static function tear_down_after_class() {
		global $wpdb;
		self::$customer->delete();
		// Clean up the test data
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wu_memberships");
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wu_products");
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wu_customers");
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wu_payments");
		parent::tear_down_after_class();
	}
}
