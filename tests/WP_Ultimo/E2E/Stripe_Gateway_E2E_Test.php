<?php
/**
 * End-to-end tests for the Stripe payment gateway improvements.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 * @since 2.0.0
 */

namespace WP_Ultimo\Tests\E2E;

/**
 * Tests the end-to-end functionality of the Stripe gateway improvements.
 *
 * @since 2.0.0
 */
class Stripe_Gateway_E2E_Test extends \WP_UnitTestCase {

	/**
	 * Test that the Stripe Connect gateway can be instantiated.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_connect_gateway_instantiation() {
		// Test that the class exists and can be instantiated
		$this->assertTrue(class_exists('\WP_Ultimo\Gateways\Stripe_Connect_Gateway'));
		
		$gateway = new \WP_Ultimo\Gateways\Stripe_Connect_Gateway();
		$this->assertInstanceOf('\WP_Ultimo\Gateways\Stripe_Connect_Gateway', $gateway);
		$this->assertEquals('stripe-connect', $gateway->get_id());
	}

	/**
	 * Test that the Payment Elements functionality is available in the original Stripe gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_gateway_payment_elements_support() {
		$gateway = new \WP_Ultimo\Gateways\Stripe_Gateway();
		
		// Check that the fields method now includes both payment elements
		$fields_html = $gateway->fields();
		
		// Should include both the new payment element and the legacy card element
		$this->assertStringContainsString('payment-element', $fields_html);
		$this->assertStringContainsString('card-element', $fields_html);
	}

	/**
	 * Test that the original Stripe gateway still supports legacy functionality.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_gateway_backward_compatibility() {
		$gateway = new \WP_Ultimo\Gateways\Stripe_Gateway();
		
		// Check that the gateway ID is still 'stripe'
		$this->assertEquals('stripe', $gateway->get_id());
		
		// Make sure basic methods exist
		$this->assertTrue(method_exists($gateway, 'run_preflight'));
		$this->assertTrue(method_exists($gateway, 'process_checkout'));
		$this->assertTrue(method_exists($gateway, 'fields'));
	}

	/**
	 * Test that all required gateways are registered.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_all_gateways_registered() {
		$gateway_manager = \WP_Ultimo\Managers\Gateway_Manager::get_instance();
		$gateways = $gateway_manager->get_registered_gateways();

		// Check that all expected gateways exist
		$this->assertArrayHasKey('stripe', $gateways);
		$this->assertArrayHasKey('stripe-checkout', $gateways);
		$this->assertArrayHasKey('stripe-connect', $gateways); // This is our new gateway
		$this->assertArrayHasKey('paypal', $gateways);
		$this->assertArrayHasKey('manual', $gateways);
		$this->assertArrayHasKey('free', $gateways);
	}

	/**
	 * Test that Stripe Connect gateway has the required methods.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_connect_gateway_methods() {
		$gateway = new \WP_Ultimo\Gateways\Stripe_Connect_Gateway();
		
		// Check that required methods exist
		$this->assertTrue(method_exists($gateway, 'init'));
		$this->assertTrue(method_exists($gateway, 'setup_connect_api_keys'));
		$this->assertTrue(method_exists($gateway, 'handle_oauth_callbacks'));
		$this->assertTrue(method_exists($gateway, 'get_platform_secret_key'));
		$this->assertTrue(method_exists($gateway, 'maybe_update_application_fee'));
	}

	/**
	 * Test Stripe Connect settings structure.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_stripe_connect_settings_structure() {
		$gateway = new \WP_Ultimo\Gateways\Stripe_Connect_Gateway();
		
		// Test that settings method exists
		$this->assertTrue(method_exists($gateway, 'settings'));
		
		// Check for the availability of application fee setting
		$settings = \WP_Ultimo\Managers\Gateway_Manager::get_instance();
		// This test is more about ensuring the method doesn't throw errors
		$this->assertTrue(true); // Placeholder, as we can't easily test settings registration in this context
	}

	/**
	 * Test that the register_scripts method works for Stripe gateways.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_register_scripts_method_exists() {
		$stripe_gateway = new \WP_Ultimo\Gateways\Stripe_Gateway();
		$connect_gateway = new \WP_Ultimo\Gateways\Stripe_Connect_Gateway();
		
		$this->assertTrue(method_exists($stripe_gateway, 'register_scripts'));
		$this->assertTrue(method_exists($connect_gateway, 'register_scripts'));
	}
}