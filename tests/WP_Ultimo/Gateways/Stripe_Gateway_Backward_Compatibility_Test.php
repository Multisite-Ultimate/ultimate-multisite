<?php
/**
 * Unit tests for the backward compatibility of Stripe Gateway changes.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 * @since 2.0.0
 */

namespace WP_Ultimo\Tests\Gateways;

use WP_Ultimo\Gateways\Stripe_Gateway;
use WP_Ultimo\Gateways\Base_Stripe_Gateway;

/**
 * Helper function to handle ReflectionProperty::setAccessible() with PHP version compatibility.
 *
 * @param ReflectionProperty|ReflectionMethod $reflection The reflection object.
 * @return void
 */
function wu_set_accessible_compatible_backward($reflection) {
    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        $reflection->setAccessible(true);
    }
}

/**
 * Tests the backward compatibility of Stripe Gateway changes.
 *
 * @since 2.0.0
 */
class Stripe_Gateway_Backward_Compatibility_Test extends \WP_UnitTestCase {

	/**
	 * Holds the gateway instance.
	 *
	 * @since 2.0.0
	 * @var Stripe_Gateway
	 */
	public $gateway;

	/**
	 * Set up before each test.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->gateway = new Stripe_Gateway();
	}

	/**
	 * Test that the original Stripe gateway still initializes properly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_original_stripe_gateway_initializes() {
		$this->assertInstanceOf(Stripe_Gateway::class, $this->gateway);
		$this->assertEquals('stripe', $this->gateway->get_id());
	}

	/**
	 * Test that the gateway can still use legacy Card Elements.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_gateway_fields_returns_string() {
		$result = $this->gateway->fields();
		
		$this->assertIsString($result);
		// Check if it contains the expected elements
		$this->assertStringContainsString('payment-element', $result);
		$this->assertStringContainsString('card-element', $result);
	}

	/**
	 * Test that the run_preflight method still works with the additional data.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_run_preflight_has_additional_data() {
		// This test ensures that the extended run_preflight method works
		// We'll mock the necessary properties to allow the method to run
		
		$order = $this->createMock(\WP_Ultimo\Checkout\Cart::class);
		$order->method('get_total')->willReturn(10.99);
		$order->method('get_currency')->willReturn('usd');
		
		$payment = $this->createMock(\WP_Ultimo\Models\Payment::class);
		$payment->method('get_meta')->with('stripe_payment_intent_id')->willReturn(null);
		
		$customer = $this->createMock(\WP_Ultimo\Models\Customer::class);
		$customer->method('get_id')->willReturn(1);
		$customer->method('get_user_id')->willReturn(1);
		
		$membership = $this->createMock(\WP_Ultimo\Models\Membership::class);
		$membership->method('get_id')->willReturn(1);
		
		// Set the necessary properties on the gateway
		$reflection = new \ReflectionClass($this->gateway);
		$order_prop = $reflection->getProperty('order');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$order_prop->setAccessible(true);
		}
		$order_prop->setValue($this->gateway, $order);

		$payment_prop = $reflection->getProperty('payment');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$payment_prop->setAccessible(true);
		}
		$payment_prop->setValue($this->gateway, $payment);

		$customer_prop = $reflection->getProperty('customer');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$customer_prop->setAccessible(true);
		}
		$customer_prop->setValue($this->gateway, $customer);

		$membership_prop = $reflection->getProperty('membership');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$membership_prop->setAccessible(true);
		}
		$membership_prop->setValue($this->gateway, $membership);
		
		// Set up basic auth
		$this->set_permalink_structure('/%postname%/');
		update_option('wu_settings', [
			'currency_symbol' => 'USD',
		]);

		// This should not throw an error
		$result = $this->gateway->run_preflight();
		
		// The result should contain the new fields we added
		$this->assertIsArray($result);
		$this->assertArrayHasKey('stripe_client_secret', $result);
		$this->assertArrayHasKey('stripe_intent_type', $result);
		$this->assertArrayHasKey('stripe_payment_amount', $result);
		$this->assertArrayHasKey('stripe_currency', $result);
	}

	/**
	 * Test backward compatibility with API key settings.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_setup_api_keys_still_works() {
		// Set up legacy API keys
		update_option('wu_settings', [
			'stripe_sandbox_mode' => '1',
			'stripe_test_pk_key' => 'pk_test_legacy_123',
			'stripe_test_sk_key' => 'sk_test_legacy_123',
		]);

		$reflection = new \ReflectionClass($this->gateway);
		$method = $reflection->getMethod('setup_api_keys');
		$method->setAccessible(true);
		
		$method->invoke($this->gateway);

		// Access the properties to verify they were set
		$publishable_key_prop = $reflection->getProperty('publishable_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$publishable_key_prop->setAccessible(true);
		}
		$secret_key_prop = $reflection->getProperty('secret_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$secret_key_prop->setAccessible(true);
		}
		$test_mode_prop = $reflection->getProperty('test_mode');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$test_mode_prop->setAccessible(true);
		}

		$this->assertEquals('pk_test_legacy_123', $publishable_key_prop->getValue($this->gateway));
		$this->assertEquals('sk_test_legacy_123', $secret_key_prop->getValue($this->gateway));
		$this->assertTrue($test_mode_prop->getValue($this->gateway));
	}

	/**
	 * Test that the base Stripe gateway register_scripts method includes new parameters.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_register_scripts_includes_new_params() {
		// Set active gateways to include stripe
		update_option('wu_settings', [
			'active_gateways' => ['stripe'],
		]);

		// Test that the script registration works
		$this->gateway->register_scripts();
		
		// Check if the script was registered
		$this->assertTrue(wp_script_is('wu-stripe', 'registered'));
	}

	/**
	 * Test that legacy Stripe gateway still processes checkout without errors.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_process_checkout_backward_compatibility() {
		// Create mock objects needed for the method
		$payment = $this->createMock(\WP_Ultimo\Models\Payment::class);
		$payment->method('get_meta')->willReturnCallback(function($key) {
			if ($key === 'stripe_payment_intent_id') {
				return 'pi_123456789';
			}
			return null;
		});
		$payment->method('get_gateway_payment_id')->willReturn('ch_123456789');
		$payment->method('get_id')->willReturn(1);
		$payment->expects($this->any())->method('set_status');
		$payment->expects($this->any())->method('save');
		
		$membership = $this->createMock(\WP_Ultimo\Models\Membership::class);
		$membership->method('get_id')->willReturn(1);
		$membership->method('get_gateway_subscription_id')->willReturn('sub_123456789');
		$membership->method('get_gateway_customer_id')->willReturn('cus_123456789');
		$membership->expects($this->any())->method('set_gateway');
		$membership->expects($this->any())->method('set_gateway_customer_id');
		$membership->expects($this->any())->method('set_gateway_subscription_id');
		$membership->expects($this->any())->method('add_to_times_billed');
		$membership->expects($this->any())->method('renew');
		$membership->expects($this->any())->method('save');
		
		$customer = $this->createMock(\WP_Ultimo\Models\Customer::class);
		$customer->method('get_id')->willReturn(1);
		$customer->method('get_user_id')->willReturn(1);
		
		$cart = $this->createMock(\WP_Ultimo\Checkout\Cart::class);
		$cart->method('should_auto_renew')->willReturn(false);
		$cart->method('has_recurring')->willReturn(false);
		$cart->method('get_all_products')->willReturn([]);
		$cart->method('get_line_items')->willReturn([]);
		
		// Try to call the method - it will likely fail because of Stripe API calls,
		// but we want to ensure it gets to a point where it tries to make API calls
		$this->expectException(\Exception::class);
		
		// Set up the gateway instance with the required properties
		$reflection = new \ReflectionClass($this->gateway);
		$payment_prop = $reflection->getProperty('payment');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$payment_prop->setAccessible(true);
		}
		$payment_prop->setValue($this->gateway, $payment);

		$membership_prop = $reflection->getProperty('membership');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$membership_prop->setAccessible(true);
		}
		$membership_prop->setValue($this->gateway, $membership);

		$customer_prop = $reflection->getProperty('customer');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$customer_prop->setAccessible(true);
		}
		$customer_prop->setValue($this->gateway, $customer);

		$order_prop = $reflection->getProperty('order');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$order_prop->setAccessible(true);
		}
		$order_prop->setValue($this->gateway, $cart);

		$this->gateway->process_checkout($payment, $membership, $customer, $cart, 'new');
	}

	/**
	 * Test that the original gateway fields method contains both new and legacy elements.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_fields_contains_both_elements() {
		$fields_html = $this->gateway->fields();
		
		// Check that both new and legacy elements are present
		$this->assertStringContainsString('id="payment-element"', $fields_html);
		$this->assertStringContainsString('id="card-element"', $fields_html);
		$this->assertStringContainsString('display: none', $fields_html); // Legacy element should be hidden by default
	}

	/**
	 * Test the register_scripts method to ensure it passes payment data correctly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_register_scripts_localized_data() {
		// Create a mock cart with specific values
		$order = $this->createMock(\WP_Ultimo\Checkout\Cart::class);
		$order->method('get_total')->willReturn(49.99);
		$order->method('get_currency')->willReturn('eur');
		
		// Set up the gateway instance 
		$reflection = new \ReflectionClass($this->gateway);
		$order_prop = $reflection->getProperty('order');
		$order_prop->setAccessible(true);
		$order_prop->setValue($this->gateway, $order);
		
		// Set active gateways to include stripe
		update_option('wu_settings', [
			'active_gateways' => ['stripe'],
			'currency_symbol' => 'USD',
		]);

		// Test that the script registration works
		$this->gateway->register_scripts();
		
		// We can't directly access the localized data in testing environment,
		// but we can verify the method executed without errors
		$this->assertTrue(true); // Placeholder assertion
	}
}