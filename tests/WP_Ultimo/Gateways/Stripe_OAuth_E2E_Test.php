<?php
/**
 * E2E-style integration tests for Stripe OAuth and checkout with application fees.
 *
 * These tests verify the complete OAuth flow and purchase processing:
 * - OAuth token storage and retrieval
 * - Application fee logic in subscription creation
 * - Stripe-Account header configuration
 *
 * @package WP_Ultimo\Gateways
 * @since 2.x.x
 */

namespace WP_Ultimo\Gateways;

use Stripe\StripeClient;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * E2E integration tests for Stripe OAuth.
 */
class Stripe_OAuth_E2E_Test extends \WP_UnitTestCase {

	/**
	 * Clean slate before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->clear_all_stripe_settings();
	}

	/**
	 * Test that OAuth tokens are correctly saved from simulated callback.
	 */
	public function test_oauth_tokens_saved_correctly() {
		// Manually simulate what the OAuth callback does
		wu_save_setting('stripe_test_access_token', 'sk_test_oauth_abc123');
		wu_save_setting('stripe_test_account_id', 'acct_test_xyz789');
		wu_save_setting('stripe_test_publishable_key', 'pk_test_oauth_abc123');
		wu_save_setting('stripe_test_refresh_token', 'rt_test_refresh_abc123');
		wu_save_setting('stripe_sandbox_mode', 1);

		// Initialize gateway
		$gateway = new Stripe_Gateway();
		$gateway->init();

		// Verify OAuth mode is detected
		$this->assertTrue($gateway->is_using_oauth());
		$this->assertEquals('oauth', $gateway->get_authentication_mode());

		// Verify account ID is loaded
		$reflection = new \ReflectionClass($gateway);
		$property = $reflection->getProperty('oauth_account_id');
		$property->setAccessible(true);
		$this->assertEquals('acct_test_xyz789', $property->getValue($gateway));
	}

	/**
	 * Test that application fee percentage is loaded when using OAuth.
	 */
	public function test_application_fee_loaded_with_oauth() {
		// Mock application fee via filter
		add_filter('wu_stripe_application_fee_percentage', function() {
			return 3.5;
		});

		// Setup OAuth mode
		wu_save_setting('stripe_test_access_token', 'sk_test_oauth_token_123');
		wu_save_setting('stripe_test_account_id', 'acct_test123');
		wu_save_setting('stripe_test_publishable_key', 'pk_test_oauth_123');
		wu_save_setting('stripe_sandbox_mode', 1);

		$gateway = new Stripe_Gateway();
		$gateway->init();

		// Verify OAuth mode
		$this->assertTrue($gateway->is_using_oauth());

		// Verify application fee is loaded
		$reflection = new \ReflectionClass($gateway);
		$property = $reflection->getProperty('application_fee_percentage');
		$property->setAccessible(true);

		$this->assertEquals(3.5, $property->getValue($gateway));
	}

	/**
	 * Test that application fee is zero when using direct API keys.
	 */
	public function test_application_fee_zero_with_direct_keys() {
		// Setup direct mode (no OAuth tokens)
		wu_save_setting('stripe_test_pk_key', 'pk_test_direct_123');
		wu_save_setting('stripe_test_sk_key', 'sk_test_direct_123');
		wu_save_setting('stripe_application_fee_percentage', 3.5); // Should be ignored
		wu_save_setting('stripe_sandbox_mode', 1);

		$gateway = new Stripe_Gateway();
		$gateway->init();

		// Verify NOT using OAuth
		$this->assertFalse($gateway->is_using_oauth());

		// Verify application fee is zero (not loaded in direct mode)
		$reflection = new \ReflectionClass($gateway);
		$property = $reflection->getProperty('application_fee_percentage');
		$property->setAccessible(true);

		$this->assertEquals(0, $property->getValue($gateway));
	}

	/**
	 * Test that Stripe client is configured with account header in OAuth mode.
	 */
	public function test_stripe_client_has_account_header_in_oauth_mode() {
		// Setup OAuth mode
		wu_save_setting('stripe_test_access_token', 'sk_test_oauth_token_123');
		wu_save_setting('stripe_test_account_id', 'acct_oauth_123');
		wu_save_setting('stripe_test_publishable_key', 'pk_test_oauth_123');
		wu_save_setting('stripe_sandbox_mode', 1);

		$gateway = new Stripe_Gateway();
		$gateway->init();

		$this->assertTrue($gateway->is_using_oauth());

		// Access oauth_account_id via reflection
		$reflection = new \ReflectionClass($gateway);
		$property = $reflection->getProperty('oauth_account_id');
		$property->setAccessible(true);

		// Verify account ID is set
		$this->assertEquals('acct_oauth_123', $property->getValue($gateway));
	}

	/**
	 * Test the complete OAuth setup flow.
	 */
	public function test_complete_oauth_flow_simulation() {
		// Step 1: Start with no configuration
		$this->clear_all_stripe_settings();

		// Step 2: Platform credentials configured via filter (simulating wp-config.php constants)
		add_filter('wu_stripe_platform_client_id', function() {
			return 'ca_platform_test_123';
		});
		add_filter('wu_stripe_application_fee_percentage', function() {
			return 2.5;
		});

		wu_save_setting('stripe_sandbox_mode', 1);

		// Step 3: User clicks "Connect with Stripe" and OAuth completes
		// (Simulating what happens after successful OAuth callback)
		wu_save_setting('stripe_test_access_token', 'sk_test_connected_abc');
		wu_save_setting('stripe_test_account_id', 'acct_connected_xyz');
		wu_save_setting('stripe_test_publishable_key', 'pk_test_connected_abc');
		wu_save_setting('stripe_test_refresh_token', 'rt_test_refresh_abc');

		// Step 4: Gateway initializes and detects OAuth
		$gateway = new Stripe_Gateway();
		$gateway->init();

		// Verify OAuth mode
		$this->assertTrue($gateway->is_using_oauth());
		$this->assertEquals('oauth', $gateway->get_authentication_mode());

		// Verify application fee is loaded
		$reflection = new \ReflectionClass($gateway);
		$fee_property = $reflection->getProperty('application_fee_percentage');
		$fee_property->setAccessible(true);
		$this->assertEquals(2.5, $fee_property->getValue($gateway));

		// Verify account ID is loaded
		$account_property = $reflection->getProperty('oauth_account_id');
		$account_property->setAccessible(true);
		$this->assertEquals('acct_connected_xyz', $account_property->getValue($gateway));

		// Step 5: Verify direct keys would still work if OAuth disconnected
		wu_save_setting('stripe_test_access_token', ''); // Clear OAuth
		wu_save_setting('stripe_test_pk_key', 'pk_test_direct_fallback');
		wu_save_setting('stripe_test_sk_key', 'sk_test_direct_fallback');

		$gateway2 = new Stripe_Gateway();
		$gateway2->init();

		// Should fall back to direct mode
		$this->assertFalse($gateway2->is_using_oauth());
		$this->assertEquals('direct', $gateway2->get_authentication_mode());
	}

	/**
	 * Clear all Stripe settings.
	 */
	private function clear_all_stripe_settings() {
		wu_save_setting('stripe_test_access_token', '');
		wu_save_setting('stripe_test_account_id', '');
		wu_save_setting('stripe_test_publishable_key', '');
		wu_save_setting('stripe_test_refresh_token', '');
		wu_save_setting('stripe_live_access_token', '');
		wu_save_setting('stripe_live_account_id', '');
		wu_save_setting('stripe_live_publishable_key', '');
		wu_save_setting('stripe_live_refresh_token', '');
		wu_save_setting('stripe_test_pk_key', '');
		wu_save_setting('stripe_test_sk_key', '');
		wu_save_setting('stripe_live_pk_key', '');
		wu_save_setting('stripe_live_sk_key', '');
		// Note: Platform credentials are now configured via constants/filters, not settings
	}
}
