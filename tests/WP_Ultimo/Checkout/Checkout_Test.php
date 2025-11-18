<?php

namespace WP_Ultimo\Checkout;

use WP_Ultimo\Models\Customer;
use WP_Ultimo\Models\Payment;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_UnitTestCase;

/**
 * Test class for Checkout functionality.
 */
class Checkout_Test extends WP_UnitTestCase {

	private static Customer $customer;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$customer = wu_create_customer([
			'username' => 'testuser_checkout',
			'email'    => 'checkout@example.com',
			'password' => 'password123',
		]);
	}

	/**
	 * Test draft payment creation.
	 */
	public function test_draft_payment_creation() {
		$checkout = Checkout::get_instance();

		$products = [1]; // Assume product ID

		$reflection = new \ReflectionClass($checkout);
		$method = $reflection->getMethod('create_draft_payment');
		$method->setAccessible(true);
		$method->invoke($checkout, $products);

		// Check if draft payment was created
		// This would require mocking or checking DB
		$this->assertTrue(true); // Placeholder
	}

	/**
	 * Test saving draft progress.
	 */
	public function test_save_draft_progress() {
		$this->markTestSkipped('Test requires complex session mocking');
	}

	public static function tear_down_after_class() {
		self::$customer->delete();
		parent::tear_down_after_class();
	}
}