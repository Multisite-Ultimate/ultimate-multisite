<?php

namespace WP_Ultimo\Checkout;

use WP_Error;
use WP_Ultimo\Models\Customer;
use WP_UnitTestCase;


class Cart_Test extends WP_UnitTestCase {

	private static Customer $customer;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		global $wpdb;
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}wu_customers");

		self::$customer = wu_create_customer(
			[
				'username' => 'testuser2',
				'email'    => 'test2@example.com',
				'password' => 'password123',
			]
		);
	}
	/**
	 * Test if the constructor correctly initializes the default attributes.
	 */
	public function test_constructor_initializes_defaults() {
		$args = [];
		$cart = new Cart($args);

		$this->assertEquals('new', $cart->get_cart_type());
		$this->assertEmpty($cart->get_country());
		$this->assertEmpty($cart->get_currency());
		$this->assertEmpty($cart->get_customer());
	}

	/**
	 * Test if the constructor correctly assigns custom attributes passed in an array.
	 */
	public function test_constructor_assigns_custom_attributes() {
		$args = [
			'cart_type' => 'new',
			'country'   => 'US',
			'state'     => 'CA',
			'city'      => 'Los Angeles',
			'currency'  => 'USD',
		];
		$cart = new Cart($args);

		$this->assertEquals('new', $cart->get_cart_type());
		$this->assertEquals('US', $cart->get_country());
		$this->assertEquals('USD', $cart->get_currency());
	}

	/**
	 * Test if the constructor initializes the errors property as an instance of WP_Error.
	 */
	public function test_constructor_initializes_errors() {
		$args = [];
		$cart = new Cart($args);

		$this->assertInstanceOf(WP_Error::class, $cart->get_errors());
	}

	/**
	 * Test if the constructor triggers the setup actions.
	 */
	public function test_constructor_triggers_setup_actions() {
		$args          = [];
		$action_called = false;

		add_action(
			'wu_cart_setup',
			function () use (&$action_called) {
				$action_called = true;
			}
		);

		new Cart($args);

		$this->assertTrue($action_called);
	}

	/**
	 * Test handling invalid cart type in input arguments.
	 */
	public function test_constructor_handles_invalid_cart_type() {
		$args = [
			'cart_type' => 'invalid_type',
		];
		$cart = new Cart($args);

		$this->assertEquals('new', $cart->get_cart_type()); // Fallback to default value
	}

	/**
	 * Test if the constructor correctly sets the attributes field.
	 */
	public function test_constructor_sets_attributes_field() {
		$args = [
			'cart_type' => 'upgrade',
			'currency'  => 'EUR',
		];
		$cart = new Cart($args);

		$attributes = $cart->get_param('cart_type');
		$this->assertEquals('upgrade', $attributes);

		$attributes = $cart->get_param('currency');
		$this->assertEquals('EUR', $attributes);
	}

	public function test_create_from_membership() {
		$customer = self::$customer;
		wp_set_current_user($customer->get_user_id(), $customer->get_username());
		$product = wu_create_product(
			[
				'name'                => 'Test Product',
				'slug'                => 'test-product',
				'amount'              => 50.00,
				'recurring'           => true,
				'duration'            => 1,
				'duration_unit'       => 'month',
				'trial_duration'      => 14,
				'trial_duration_unit' => 'day',
				'type'                => 'plan',
				'pricing_type'        => 'paid',
				'active'              => true,
			]
		);

		// Create a second product for upgrade/downgrade scenarios
		$second_product = wu_create_product(
			[
				'name'                => 'Second Product',
				'slug'                => 'second-product',
				'amount'              => 75.00,
				'recurring'           => true,
				'duration'            => 1,
				'duration_unit'       => 'month',
				'trial_duration'      => 14,
				'trial_duration_unit' => 'day',
				'type'                => 'plan',
				'pricing_type'        => 'paid',
				'active'              => true,
			]
		);

		// Create membership
		$membership = wu_create_membership(
			[
				'customer_id'     => self::$customer->get_id(),
				'plan_id'         => $product->get_id(),
				'status'          => 'active',
				'recurring'       => true,
				'date_expiration' => gmdate('Y-m-d 23:59:59', strtotime('+30 days')),
				'amount'          => 50.0,
				'initial_amount'  => 50.0,
			]
		);

		$cart_args = [
			'cart_type'     => 'upgrade',
			'products'      => [$second_product->get_id()],
			'duration'      => 1,
			'duration_unit' => 'month',
			'membership_id' => $membership->get_id(),
			'payment_id'    => false,
			'discount_code' => false,
			'auto_renew'    => true,
			'country'       => 'US',
			'state'         => 'NY',
			'city'          => 'New York',
			'currency'      => 'USD',
		];

		$cart       = new \WP_Ultimo\Checkout\Cart($cart_args);
		$line_items = $cart->get_line_items();

		$this->assertNotEmpty($line_items);
		$this->assertContainsOnlyInstancesOf(Line_Item::class, $line_items);
		$this->assertCount(2, $line_items);
		foreach ($line_items as $line_item) {
			$this->assertTrue(in_array($line_item->get_type(), ['product', 'credit']));
			if ( 'credit' === $line_item->get_type() ) {
				$this->assertEquals(-50.0, $line_item->get_total());
			}
		}
	}

	public static function tear_down_after_class() {
		global $wpdb;
		self::$customer->delete();
	}
}
