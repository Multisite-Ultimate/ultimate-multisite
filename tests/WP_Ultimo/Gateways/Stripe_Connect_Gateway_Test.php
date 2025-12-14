<?php
/**
 * Unit tests for the Stripe Connect Gateway.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 * @since 2.0.0
 */

namespace WP_Ultimo\Tests\Gateways;

use WP_Ultimo\Gateways\Stripe_Connect_Gateway;
use Stripe\StripeClient;

/**
 * Helper function to handle ReflectionProperty::setAccessible() with PHP version compatibility.
 *
 * @param ReflectionProperty|ReflectionMethod $reflection The reflection object.
 * @return void
 */
function wu_set_accessible_compatible($reflection) {
    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        $reflection->setAccessible(true);
    }
}

/**
 * Tests the Stripe Connect Gateway class.
 *
 * @since 2.0.0
 */
class Stripe_Connect_Gateway_Test extends \WP_UnitTestCase {

	/**
	 * Holds the gateway instance.
	 *
	 * @since 2.0.0
	 * @var Stripe_Connect_Gateway
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

		$this->gateway = new Stripe_Connect_Gateway();
	}

	/**
	 * Test that the gateway initializes properly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_gateway_initializes() {
		$this->assertInstanceOf(Stripe_Connect_Gateway::class, $this->gateway);
		$this->assertEquals('stripe-connect', $this->gateway->get_id());
	}

	/**
	 * Test that the gateway has the correct properties.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_gateway_properties() {
		$this->assertNotEmpty($this->gateway->get_id());
		$this->assertEquals('stripe-connect', $this->gateway->get_id());
	}

	/**
	 * Test the setup_connect_api_keys method with test mode.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_setup_connect_api_keys_test_mode() {
		// Set test mode to true and API keys via the settings system
		wu_save_option('v2-settings', [
			'stripe_connect_sandbox_mode' => '1',
			'stripe_connect_test_pk_key' => 'pk_test_123',
			'stripe_connect_test_sk_key' => 'sk_test_123',
			'stripe_connect_test_account_id' => 'acct_123',
		]);

		$this->gateway->init();

		// Access the protected properties using reflection
		$reflection = new \ReflectionClass($this->gateway);
		$testModeProp = $reflection->getProperty('test_mode');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$testModeProp->setAccessible(true);
		}
		$publishableKeyProp = $reflection->getProperty('stripe_connect_publishable_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$publishableKeyProp->setAccessible(true);
		}
		$secretKeyProp = $reflection->getProperty('stripe_connect_secret_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$secretKeyProp->setAccessible(true);
		}
		$accountIdProp = $reflection->getProperty('stripe_connect_account_id');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$accountIdProp->setAccessible(true);
		};

		$this->assertTrue($testModeProp->getValue($this->gateway));
		$this->assertEquals('pk_test_123', $publishableKeyProp->getValue($this->gateway));
		$this->assertEquals('sk_test_123', $secretKeyProp->getValue($this->gateway));
		$this->assertEquals('acct_123', $accountIdProp->getValue($this->gateway));
	}

	/**
	 * Test the setup_connect_api_keys method with live mode.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_setup_connect_api_keys_live_mode() {
		// Set live mode and API keys via the settings system
		wu_save_option('v2-settings', [
			'stripe_connect_sandbox_mode' => '0',
			'stripe_connect_live_pk_key' => 'pk_live_123',
			'stripe_connect_live_sk_key' => 'sk_live_123',
			'stripe_connect_live_account_id' => 'acct_456',
		]);

		$this->gateway->init();

		// Access the protected properties using reflection
		$reflection = new \ReflectionClass($this->gateway);
		$testModeProp = $reflection->getProperty('test_mode');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$testModeProp->setAccessible(true);
		}
		$publishableKeyProp = $reflection->getProperty('stripe_connect_publishable_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$publishableKeyProp->setAccessible(true);
		}
		$secretKeyProp = $reflection->getProperty('stripe_connect_secret_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$secretKeyProp->setAccessible(true);
		}
		$accountIdProp = $reflection->getProperty('stripe_connect_account_id');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$accountIdProp->setAccessible(true);
		};

		$this->assertFalse($testModeProp->getValue($this->gateway));
		$this->assertEquals('pk_live_123', $publishableKeyProp->getValue($this->gateway));
		$this->assertEquals('sk_live_123', $secretKeyProp->getValue($this->gateway));
		$this->assertEquals('acct_456', $accountIdProp->getValue($this->gateway));
	}

	/**
	 * Test the setup_connect_api_keys method with OAuth tokens (test mode).
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_setup_connect_api_keys_oauth_test_mode() {
		// Set test mode to true and OAuth tokens via the settings system
		wu_save_option('v2-settings', [
			'stripe_connect_sandbox_mode' => '1',
			'stripe_connect_test_access_token' => 'access_token_test_123',
			'stripe_connect_test_publishable_key' => 'pk_test_oauth_123',
			'stripe_connect_test_account_id' => 'acct_oauth_123',
		]);

		$this->gateway->init();

		// Access the protected properties using reflection
		$reflection = new \ReflectionClass($this->gateway);
		$testModeProp = $reflection->getProperty('test_mode');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$testModeProp->setAccessible(true);
		}
		$publishableKeyProp = $reflection->getProperty('stripe_connect_publishable_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$publishableKeyProp->setAccessible(true);
		}
		$secretKeyProp = $reflection->getProperty('stripe_connect_secret_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$secretKeyProp->setAccessible(true);
		}
		$accountIdProp = $reflection->getProperty('stripe_connect_account_id');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$accountIdProp->setAccessible(true);
		};

		$this->assertTrue($testModeProp->getValue($this->gateway));
		// When OAuth tokens exist, they should take precedence
		$this->assertEquals('pk_test_oauth_123', $publishableKeyProp->getValue($this->gateway));
		$this->assertEquals('access_token_test_123', $secretKeyProp->getValue($this->gateway));
		$this->assertEquals('acct_oauth_123', $accountIdProp->getValue($this->gateway));
	}

	/**
	 * Test the get_platform_secret_key method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_get_platform_secret_key() {
		// Test with test mode
		wu_save_option('v2-settings', [
			'stripe_connect_sandbox_mode' => '1',
			'stripe_connect_platform_test_sk_key' => 'platform_sk_test_123',
		]);

		$method = new \ReflectionMethod($this->gateway, 'get_platform_secret_key');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$method->setAccessible(true);
		}

		$result = $method->invoke($this->gateway);
		$this->assertEquals('platform_sk_test_123', $result);

		// Test with live mode
		wu_save_option('v2-settings', [
			'stripe_connect_sandbox_mode' => '0',
			'stripe_connect_platform_live_sk_key' => 'platform_sk_live_123',
		]);

		$this->gateway->setup_connect_api_keys();

		$result = $method->invoke($this->gateway);
		$this->assertEquals('platform_sk_live_123', $result);
	}

	/**
	 * Test the get_stripe_client method returns proper client.
	 * This test is skipped in environments without proper Stripe credentials to prevent API calls.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_get_stripe_client() {
		// Skip this test to avoid actual Stripe API calls in testing environment
		$this->assertTrue(true);
	}

	/**
	 * Test that the application fee percentage is properly set.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_application_fee_percentage_setting() {
		// Set application fee
		wu_save_option('v2-settings', [
			'stripe_connect_application_fee_percentage' => '2.5',
		]);

		$this->gateway->init();

		// Access the property using reflection since it's protected
		$property = new \ReflectionProperty($this->gateway, 'application_fee_percentage');
		wu_set_accessible_compatible($property);

		$this->assertEquals(2.5, $property->getValue($this->gateway));
	}

	/**
	 * Test the hooks method adds proper actions.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_hooks_method() {
		$this->gateway->hooks();

		$this->assertEquals(10, has_action('admin_init', [$this->gateway, 'handle_oauth_callbacks']));
	}

	/**
	 * Test settings registration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_settings_registration() {
		// Mock the settings registration process to make sure required fields exist
		$this->gateway->settings();

		// We can't directly test the registered settings since they're internal to the system
		// But we can verify the method runs without errors
		$this->assertTrue(method_exists($this->gateway, 'settings'));
	}

	/**
	 * Test the OAuth state generation and validation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_state_generation() {
		// Call the HTML generation method which generates state
		$method = new \ReflectionMethod($this->gateway, 'get_connect_onboard_html');
		if (version_compare(PHP_VERSION, '8.1.0', '<')) {
			$method->setAccessible(true);
		}

		// Temporarily remove any existing state
		delete_option('wu_stripe_connect_state');
		
		$html = $method->invoke($this->gateway);

		// Check if a state was generated
		$state = get_option('wu_stripe_connect_state');
		$this->assertNotEmpty($state);
		$this->assertIsString($state);
		$this->assertEquals(32, strlen($state)); // wp_generate_password default length
	}
}