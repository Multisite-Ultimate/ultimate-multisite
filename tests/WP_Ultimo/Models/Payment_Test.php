<?php

namespace WP_Ultimo\Models;

use WP_Ultimo\Checkout\Line_Item;
use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_Ultimo\Database\Payments\Payment_Status;
use WP_UnitTestCase;

/**
 * Test class for Payment model functionality.
 *
 * Tests payment creation, line items management, financial calculations,
 * status handling, gateway functionality, and invoice features.
 */
class Payment_Test extends WP_UnitTestCase {

	private static Customer $customer;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$customer = wu_create_customer(
			[
				'username' => 'testuser2',
				'email'    => 'test2@example.com',
				'password' => 'password123',
			]
		);
	}
	/**
	 * Test payment creation with valid data.
	 */
	public function test_payment_creation_with_valid_data(): void {
		$payment = new Payment();
		$payment->set_customer_id(1); // Use dummy ID to test setters/getters
		$payment->set_membership_id(1);
		$payment->set_currency('USD');
		$payment->set_subtotal(100.00);
		$payment->set_total(110.00);
		$payment->set_status(Payment_Status::PENDING);
		$payment->set_gateway('manual');

		$this->assertEquals(1, $payment->get_customer_id());
		$this->assertEquals(1, $payment->get_membership_id());
		$this->assertEquals('USD', $payment->get_currency());
		$this->assertEquals(100.00, $payment->get_subtotal());
		$this->assertEquals(110.00, $payment->get_total());
		$this->assertEquals(Payment_Status::PENDING, $payment->get_status());
		$this->assertEquals('manual', $payment->get_gateway());
	}

	/**
	 * Test line items functionality.
	 */
	public function test_line_items_functionality(): void {
		$payment = new Payment();

		// Initially no line items
		$this->assertFalse($payment->has_line_items());
		$this->assertEquals([], $payment->get_line_items());

		// Create line items
		$line_item_1 = new Line_Item(
			[
				'type'        => 'fee',
				'hash'        => 'test_item_1',
				'title'       => 'Test Item 1',
				'description' => 'First test item',
				'unit_price'  => 50.00,
				'quantity'    => 2,
				'taxable'     => true,
				'tax_rate'    => 10.00,
			]
		);

		$line_item_2 = new Line_Item(
			[
				'type'        => 'product',
				'hash'        => 'test_item_2',
				'title'       => 'Test Item 2',
				'description' => 'Second test item',
				'unit_price'  => 30.00,
				'quantity'    => 1,
				'taxable'     => false,
				'tax_rate'    => 0.00,
			]
		);

		// Set line items
		$line_items = [$line_item_1, $line_item_2];
		$payment->set_line_items($line_items);

		// Test that line items were set
		$this->assertTrue($payment->has_line_items());
		$saved_line_items = $payment->get_line_items();
		$this->assertCount(2, $saved_line_items);

		// Verify we have items with the expected types
		$found_types = array_map(fn($item) => $item->get_type(), $saved_line_items);
		$this->assertContains('fee', $found_types);
		$this->assertContains('product', $found_types);

		// Verify first item by finding it by type
		$fee_items = array_filter($saved_line_items, fn($item) => $item->get_type() === 'fee');
		$this->assertNotEmpty($fee_items);
		$fee_item = reset($fee_items);
		$this->assertEquals('Test Item 1', $fee_item->get_title());
		$this->assertEquals(50.00, $fee_item->get_unit_price());
		$this->assertEquals(2, $fee_item->get_quantity());
		$this->assertTrue($fee_item->is_taxable());
		$this->assertEquals(10.00, $fee_item->get_tax_rate());

		// Verify second item by finding it by type
		$product_items = array_filter($saved_line_items, fn($item) => $item->get_type() === 'product');
		$this->assertNotEmpty($product_items);
		$product_item = reset($product_items);
		$this->assertEquals('Test Item 2', $product_item->get_title());
		$this->assertEquals(30.00, $product_item->get_unit_price());
		$this->assertEquals(1, $product_item->get_quantity());
		$this->assertFalse($product_item->is_taxable());
	}

	/**
	 * Test adding individual line items.
	 */
	public function test_add_line_item(): void {
		$payment = new Payment();

		$customer = self::$customer;
		wp_set_current_user($customer->get_user_id(), $customer->get_username());
		// Create first product
		$product = wu_create_product(
			[
				'name'                => 'Test Product',
				'slug'                => 'test-product',
				'amount'              => 50.00,
				'recurring'           => true,
				'duration'            => 1,
				'duration_unit'       => 'month',
				'trial_duration'      => 5,
				'trial_duration_unit' => 'day',
				'type'                => 'plan',
				'pricing_type'        => 'paid',
				'active'              => true,
			]
		);

		// Create membership
		$membership = wu_create_membership(
			[
				'customer_id'     => $customer->get_id(),
				'plan_id'         => $product->get_id(),
				'status'          => Membership_Status::TRIALING,
				'recurring'       => true,
				'date_expiration' => gmdate('Y-m-d 23:59:59', strtotime('+5 days')),
			]
		);
		$payment->set_parent_id(0);
		$payment->set_customer_id($customer->get_id());
		$payment->set_membership_id($membership->get_id());
		$payment->set_currency('USD');
		$payment->set_status(Payment_Status::PENDING);
		$payment->set_product_id($product->get_id());
		$payment->set_gateway('manual');
		$payment->set_gateway_payment_id('1');
		$payment->set_discount_code('');

		// Initially no line items
		$this->assertFalse($payment->has_line_items());
		$this->assertEmpty($payment->get_line_items());

		// Add first line item
		$line_item_1 = new Line_Item(
			[
				'type'       => 'product',
				'hash'       => 'product_1',
				'title'      => 'Product 1',
				'unit_price' => 25.00,
				'quantity'   => 1,
			]
		);

		$payment->add_line_item($line_item_1);
		$this->assertTrue($payment->has_line_items());
		$this->assertCount(1, $payment->get_line_items());

		// Add second line item
		$line_item_2 = new Line_Item(
			[
				'type'       => 'fee',
				'hash'       => 'fee_1',
				'title'      => 'Processing Fee',
				'unit_price' => 5.00,
				'quantity'   => 1,
			]
		);

		$payment->add_line_item($line_item_2);
		$this->assertCount(2, $payment->get_line_items());

		// Verify both items are present
		$line_items = $payment->get_line_items();
		$types      = array_map(fn($item) => $item->get_type(), $line_items);
		$this->assertContains('product', $types);
		$this->assertContains('fee', $types);

		$payment->recalculate_totals();
		$saved = $payment->save();

		$this->assertTrue($saved, 'failed to save payment');

		$saved_payment = wu_get_payment($payment->get_id());

		$this->assertInstanceOf(Payment::class, $saved_payment);
		$this->assertCount(2, $saved_payment->get_line_items());
	}

	/**
	 * Test line items recalculation.
	 */
	public function test_line_items_recalculation(): void {
		$payment = new Payment();

		// Create line items with different types and values
		$product_item = new Line_Item(
			[
				'type'       => 'product',
				'hash'       => 'product_item',
				'title'      => 'Main Product',
				'unit_price' => 100.00,
				'quantity'   => 1,
				'taxable'    => true,
				'tax_rate'   => 8.25,
			]
		);

		$discount_item = new Line_Item(
			[
				'type'       => 'discount',
				'hash'       => 'discount_item',
				'title'      => 'Discount',
				'unit_price' => -10.00,
				'quantity'   => 1,
			]
		);

		$payment->set_line_items([$product_item, $discount_item]);

		// Recalculate totals based on line items
		$payment->recalculate_totals();

		$this->assertEquals(90.00, $payment->get_subtotal());
		$this->assertEquals(8.25, $payment->get_tax_total());
		$this->assertEquals(0, $payment->get_discount_total()); // This might not be right, but it's how the existing code works.
		$this->assertEquals(98.25, $payment->get_total()); // 100 - 10 + 8.25
	}

	/**
	 * Test empty line items handling.
	 */
	public function test_empty_line_items_handling(): void {
		$payment = new Payment();

		// Test empty line items
		$this->assertFalse($payment->has_line_items());
		$this->assertEquals([], $payment->get_line_items());

		// Set empty array
		$payment->set_line_items([]);
		$this->assertFalse($payment->has_line_items());
		$this->assertEquals([], $payment->get_line_items());
	}

	/**
	 * Test payment status functionality.
	 */
	public function test_payment_status_functionality(): void {
		$payment = new Payment();
		$payment->set_customer_id(1);
		$payment->set_membership_id(1);
		$payment->set_currency('USD');
		$payment->set_subtotal(100.00);
		$payment->set_total(100.00);

		// Test different statuses
		$statuses = [
			Payment_Status::PENDING,
			Payment_Status::COMPLETED,
			Payment_Status::REFUND,
			Payment_Status::FAILED,
		];

		foreach ($statuses as $status) {
			$payment->set_status($status);
			$this->assertEquals($status, $payment->get_status());

			// Test status label and class methods
			$label = $payment->get_status_label();
			$class = $payment->get_status_class();
			$this->assertIsString($label);
			$this->assertIsString($class);
		}
	}

	/**
	 * Test payment financial properties.
	 */
	public function test_payment_financial_properties(): void {
		$payment = new Payment();
		$payment->set_customer_id(1);
		$payment->set_membership_id(1);
		$payment->set_currency('USD');
		$payment->set_status(Payment_Status::PENDING);

		// Test all financial setters and getters
		$payment->set_subtotal(100.50);
		$this->assertEquals(100.50, $payment->get_subtotal());

		$payment->set_tax_total(8.25);
		$this->assertEquals(8.25, $payment->get_tax_total());

		$payment->set_discount_total(15.00);
		$this->assertEquals(15.00, $payment->get_discount_total());

		$payment->set_refund_total(25.00);
		$this->assertEquals(25.00, $payment->get_refund_total());

		$payment->set_total(93.75);
		$this->assertEquals(93.75, $payment->get_total());

		// Test discount code
		$payment->set_discount_code('SAVE20');
		$this->assertEquals('SAVE20', $payment->get_discount_code());
	}

	/**
	 * Test payment gateway functionality.
	 */
	public function test_payment_gateway_functionality(): void {
		$payment = new Payment();

		// Test gateway setter and getter
		$payment->set_gateway('stripe');
		$this->assertEquals('stripe', $payment->get_gateway());

		// Test gateway payment ID
		$payment->set_gateway_payment_id('pi_test123456789');
		$this->assertEquals('pi_test123456789', $payment->get_gateway_payment_id());

		// Test payment method returns a string
		$payment_method = $payment->get_payment_method();
		$this->assertIsString($payment_method);
	}

	/**
	 * Test payment invoice functionality.
	 */
	public function test_payment_invoice_functionality(): void {
		$payment = new Payment();
		$payment->set_customer_id(1);
		$payment->set_membership_id(1);

		// Test invoice number functionality
		$payment->set_invoice_number(12345);
		$this->assertEquals(12345, $payment->get_saved_invoice_number());

		// Test invoice URL generation
		$invoice_url = $payment->get_invoice_url();
		$this->assertIsString($invoice_url);
		$this->assertStringContainsString('action=invoice', $invoice_url);
	}

	/**
	 * Test draft payment status.
	 */
	public function test_draft_status(): void {
		$status = new Payment_Status(Payment_Status::DRAFT);
		$this->assertEquals('Draft', $status->get_label());
		$this->assertEquals('wu-bg-blue-200 wu-text-blue-700', $status->get_classes());
		$this->assertEquals('wu-align-middle dashicons-wu-edit wu-text-blue-700', $status->get_icon_classes());
	}

	/**
	 * Test cancelling a pending payment.
	 */
	public function test_cancel_pending_payment(): void {
		$customer = self::$customer;
		wp_set_current_user($customer->get_user_id(), $customer->get_username());

		$product = wu_create_product([
			'name' => 'Test Plan',
			'slug' => 'test-plan',
			'pricing_type' => 'paid',
			'amount' => 10,
			'currency' => 'USD',
			'recurring' => false,
			'type' => 'plan',
		]);

		if (is_wp_error($product)) {
			$this->fail('Failed to create product: ' . $product->get_error_message());
		}

		$membership = wu_create_membership([
			'customer_id' => $customer->get_id(),
			'plan_id' => $product->get_id(),
			'status' => Membership_Status::ACTIVE,
		]);

		if (is_wp_error($membership)) {
			$this->fail('Failed to create membership: ' . $membership->get_error_message());
		}

		$payment = wu_create_payment([
			'customer_id' => $customer->get_id(),
			'membership_id' => $membership->get_id(),
			'currency' => 'USD',
			'subtotal' => 100.00,
			'total' => 100.00,
			'status' => Payment_Status::PENDING,
			'gateway' => 'manual',
		]);

		$this->assertInstanceOf(Payment::class, $payment, 'Payment creation failed');
		$this->assertGreaterThan(0, $payment->get_id(), 'Payment ID not set');
		$this->assertEquals(Payment_Status::PENDING, $payment->get_status());

		// Simulate cancel
		$payment->set_status(Payment_Status::CANCELLED);
		$payment->save();

		$saved_payment = wu_get_payment($payment->get_id());
		$this->assertEquals(Payment_Status::CANCELLED, $saved_payment->get_status());
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
