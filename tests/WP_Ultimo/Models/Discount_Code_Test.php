<?php

namespace WP_Ultimo\Models;

use WP_Error;
use WP_UnitTestCase;

/**
 * Test class for Discount Code model functionality.
 *
 * Tests discount code validation, expiration dates, usage limits,
 * product restrictions, and error handling.
 */
class Discount_Code_Test extends WP_UnitTestCase {

	/**
	 * Tests that a valid discount code returns true.
	 */
	public function test_is_valid_active_discount_code(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);

		$result = $discount_code->is_valid();

		$this->assertTrue($result);
	}

	/**
	 * Tests that an inactive discount code returns an error.
	 */
	public function test_is_valid_inactive_discount_code(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(false);

		$result = $discount_code->is_valid();

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('discount_code', $result->get_error_code());
		$this->assertEquals('This coupon code is not valid.', $result->get_error_message());
	}

	/**
	 * Tests that a discount code with max uses returns an error after being used maximum times.
	 */
	public function test_is_valid_max_uses_exceeded(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_max_uses(5);
		$discount_code->set_uses(5);

		$result = $discount_code->is_valid();

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('discount_code', $result->get_error_code());
		$this->assertEquals(
			'This discount code was already redeemed the maximum amount of times allowed.',
			$result->get_error_message()
		);
	}

	/**
	 * Tests that a discount code before the start date is invalid.
	 */
	public function test_is_valid_before_start_date(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_date_start(gmdate('Y-m-d H:i:s', strtotime('+1 day')));

		$result = $discount_code->is_valid();

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('discount_code', $result->get_error_code());
		$this->assertEquals('This coupon code is not valid.', $result->get_error_message());
	}

	/**
	 * Tests that a discount code after the expiration date is invalid.
	 */
	public function test_is_valid_after_expiration_date(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_date_expiration(gmdate('Y-m-d H:i:s', strtotime('-1 day')));

		$result = $discount_code->is_valid();

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('discount_code', $result->get_error_code());
		$this->assertEquals('This coupon code is not valid.', $result->get_error_message());
	}

	/**
	 * Tests that a discount code limited to specific products returns true for allowed products.
	 */
	public function test_is_valid_for_allowed_product(): void {
		$product_id    = 123;
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_limit_products(true);
		$discount_code->set_allowed_products([$product_id]);

		$result = $discount_code->is_valid($product_id);

		$this->assertTrue($result);
	}

	/**
	 * Tests that a discount code limited to specific products returns an error for disallowed products.
	 */
	public function test_is_valid_for_disallowed_product(): void {
		$allowed_product_id    = 123;
		$disallowed_product_id = 456;
		$discount_code         = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_limit_products(true);
		$discount_code->set_allowed_products([$allowed_product_id]);

		$result = $discount_code->is_valid($disallowed_product_id);

		$this->assertInstanceOf(WP_Error::class, $result);
		$this->assertEquals('discount_code', $result->get_error_code());
		$this->assertEquals('This coupon code is not valid.', $result->get_error_message());
	}

	/**
	 * Tests that a discount code with no product limits returns true.
	 */
	public function test_is_valid_no_product_limits(): void {
		$discount_code = new Discount_Code();
		$discount_code->set_active(true);
		$discount_code->set_limit_products(false);

		$result = $discount_code->is_valid();

		$this->assertTrue($result);
	}
}
