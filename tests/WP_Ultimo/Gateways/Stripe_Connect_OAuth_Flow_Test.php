<?php
/**
 * Unit tests for the OAuth flow handling in Stripe Connect Gateway.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 * @since 2.0.0
 */

namespace WP_Ultimo\Tests\Gateways;

use WP_Ultimo\Gateways\Stripe_Connect_Gateway;

/**
 * Tests the OAuth flow handling in Stripe Connect Gateway.
 *
 * @since 2.0.0
 */
class Stripe_Connect_OAuth_Flow_Test extends \WP_UnitTestCase {

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
	 * Test that the OAuth callback is properly handled when state matches.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_callback_properly_handled() {
		// Generate a state and store it
		$state = wp_generate_password(32, false);
		update_option('wu_stripe_connect_state', $state);
		
		// Set up $_GET parameters to simulate OAuth callback
		$_GET['code'] = 'auth_code_123';
		$_GET['state'] = $state;
		
		// Mock the HTTP request for token exchange
		add_filter('pre_http_request', function($preempt, $args, $url) {
			if (strpos($url, 'connect.stripe.com/oauth/token') !== false) {
				// Verify the request body contains the expected parameters
				$body = $args['body'];
				$this->assertEquals('authorization_code', $body['grant_type']);
				$this->assertEquals('auth_code_123', $body['code']);
				
				// Return a mock successful response
				return [
					'body' => json_encode([
						'access_token' => 'access_token_123',
						'refresh_token' => 'refresh_token_123',
						'stripe_user_id' => 'acct_123',
						'stripe_publishable_key' => 'pk_123'
					]),
					'response' => ['code' => 200]
				];
			}
			return $preempt;
		}, 10, 3);
		
		// Capture the redirect that would happen
		$redirect_captured = false;
		add_filter('wp_redirect', function($location) use (&$redirect_captured) {
			$redirect_captured = true;
			$this->assertStringContainsString('notice=stripe_connect_success', $location);
			return false; // Prevent actual redirect
		});
		
		// Call the OAuth callback handler - this should trigger a redirect
		$this->expectException(\WPDieException::class);
		
		$this->gateway->handle_oauth_callbacks();
		
		// Verify state was cleared after successful processing
		$this->assertEmpty(get_option('wu_stripe_connect_state'));
		$this->assertTrue($redirect_captured);
		
		// Verify tokens were saved
		$this->assertEquals('access_token_123', get_option('v2-settings')['stripe_connect_test_access_token'] ?? get_option('v2-settings')['stripe_connect_live_access_token'] ?? null);
	}

	/**
	 * Test that OAuth callback fails with invalid state.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_callback_fails_with_invalid_state() {
		// Set up $_GET parameters to simulate OAuth callback with invalid state
		$_GET['code'] = 'auth_code_123';
		$_GET['state'] = 'invalid_state';
		
		// Set a different expected state
		update_option('wu_stripe_connect_state', 'valid_state_123');
		
		// This should trigger wp_die
		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid state parameter');
		
		$this->gateway->handle_oauth_callbacks();
	}

	/**
	 * Test OAuth callback fails with invalid response from Stripe.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_callback_fails_with_stripe_error() {
		// Generate a state and store it
		$state = wp_generate_password(32, false);
		update_option('wu_stripe_connect_state', $state);
		
		// Set up $_GET parameters to simulate OAuth callback
		$_GET['code'] = 'auth_code_123';
		$_GET['state'] = $state;
		
		// Mock the HTTP request to return an error
		add_filter('pre_http_request', function($preempt, $args, $url) {
			if (strpos($url, 'connect.stripe.com/oauth/token') !== false) {
				return [
					'body' => json_encode([
						'error' => 'invalid_grant',
						'error_description' => 'Authorization code expired'
					]),
					'response' => ['code' => 400]
				];
			}
			return $preempt;
		}, 10, 3);
		
		// This should trigger wp_die with the error message
		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('invalid_grant');
		
		$this->gateway->handle_oauth_callbacks();
	}

	/**
	 * Test OAuth disconnect functionality without nonce.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_disconnect_fails_without_nonce() {
		$_GET['stripe_connect_disconnect'] = '1';
		unset($_GET['_wpnonce']); // Ensure no nonce is set
		
		// This should trigger wp_die for invalid nonce
		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid nonce');
		
		$this->gateway->handle_oauth_callbacks();
	}

	/**
	 * Test OAuth disconnect functionality with valid nonce.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_disconnect_with_valid_nonce() {
		// Set up some mock tokens to be deleted
		$settings = get_option('v2-settings', []);
		$settings['stripe_connect_test_access_token'] = 'access_token_123';
		$settings['stripe_connect_test_refresh_token'] = 'refresh_token_123';
		$settings['stripe_connect_test_account_id'] = 'acct_123';
		$settings['stripe_connect_test_publishable_key'] = 'pk_123';
		update_option('v2-settings', $settings);
		
		// Create a valid nonce
		$_GET['stripe_connect_disconnect'] = '1';
		$_GET['_wpnonce'] = wp_create_nonce('wu_disconnect_stripe_connect');
		
		// Capture the redirect that would happen
		$redirect_captured = false;
		add_filter('wp_redirect', function($location) use (&$redirect_captured) {
			$redirect_captured = true;
			$this->assertStringContainsString('notice=stripe_connect_disconnected', $location);
			return false; // Prevent actual redirect
		});
		
		// This should trigger a redirect
		$this->expectException(\WPDieException::class);
		
		$this->gateway->handle_oauth_callbacks();
		
		// Verify tokens were deleted
		$settings = get_option("v2-settings", []);
		$this->assertArrayNotHasKey('stripe_connect_test_access_token', $settings);
		$this->assertArrayNotHasKey('stripe_connect_test_refresh_token', $settings);
		$this->assertArrayNotHasKey('stripe_connect_test_account_id', $settings);
		$this->assertArrayNotHasKey('stripe_connect_test_publishable_key', $settings);
		$this->assertTrue($redirect_captured);
	}

	/**
	 * Test that get_connect_authorization_url includes state parameter.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_get_connect_authorization_url_includes_state() {
		// Set up client ID
		$settings = get_option("v2-settings", []);
		$settings['stripe_connect_client_id'] = 'ca_123456789';
		update_option("v2-settings", $settings);
		
		$state = 'test_state_123';
		
		$method = new \ReflectionMethod($this->gateway, 'get_connect_authorization_url');
		$method->setAccessible(true);
		
		$auth_url = $method->invoke($this->gateway, $state);
		
		$this->assertStringContainsString('state=test_state_123', $auth_url);
		$this->assertStringContainsString('client_id=ca_123456789', $auth_url);
		$this->assertStringContainsString('response_type=code', $auth_url);
		$this->assertStringContainsString('scope=read_write', $auth_url);
	}

	/**
	 * Test get_connect_onboard_html shows correct content when connected.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_get_connect_onboard_html_when_connected() {
		// Set up the gateway with a connected account
		$reflection = new \ReflectionProperty($this->gateway, 'stripe_connect_account_id');
		$reflection->setAccessible(true);
		$reflection->setValue($this->gateway, 'acct_123456789');
		
		$method = new \ReflectionMethod($this->gateway, 'get_connect_onboard_html');
		$method->setAccessible(true);
		
		$html = $method->invoke($this->gateway);
		
		$this->assertStringContainsString('Your Stripe account is connected!', $html);
		$this->assertStringContainsString('Connected Account ID: acct_123456789', $html);
		$this->assertStringContainsString('Disconnect Account', $html);
	}

	/**
	 * Test get_connect_onboard_html shows correct content when not connected.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_get_connect_onboard_html_when_not_connected() {
		// Ensure no account is connected
		$reflection = new \ReflectionProperty($this->gateway, 'stripe_connect_account_id');
		$reflection->setAccessible(true);
		$reflection->setValue($this->gateway, '');
		
		$method = new \ReflectionMethod($this->gateway, 'get_connect_onboard_html');
		$method->setAccessible(true);
		
		$html = $method->invoke($this->gateway);
		
		$this->assertStringContainsString('Connect your Stripe account', $html);
		$this->assertStringContainsString('Connect with Stripe', $html);
		$this->assertStringNotContainsString('Connected Account ID', $html);
		$this->assertStringNotContainsString('Disconnect Account', $html);
		
		// Verify that a state was generated
		$state = get_option('wu_stripe_connect_state');
		$this->assertNotEmpty($state);
	}

	/**
	 * Test OAuth token exchange uses platform secret key.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_oauth_token_exchange_uses_platform_secret() {
		// Set up platform secret key
		$settings = get_option("v2-settings", []);
		$settings['stripe_connect_sandbox_mode'] = '1';
		$settings['stripe_connect_platform_test_sk_key'] = 'platform_sk_test_123';
		update_option("v2-settings", $settings);
		
		// Generate a state and store it
		$state = wp_generate_password(32, false);
		update_option('wu_stripe_connect_state', $state);
		
		// Set up $_GET parameters
		$_GET['code'] = 'auth_code_123';
		$_GET['state'] = $state;
		
		// Capture the HTTP request to verify the platform secret is used
		$request_body = null;
		add_filter('pre_http_request', function($preempt, $args, $url) use (&$request_body) {
			if (strpos($url, 'connect.stripe.com/oauth/token') !== false) {
				$request_body = $args['body'];
				// Return mock response
				return [
					'body' => json_encode([
						'access_token' => 'access_token_123',
						'stripe_user_id' => 'acct_123',
						'stripe_publishable_key' => 'pk_123'
					]),
					'response' => ['code' => 200]
				];
			}
			return $preempt;
		}, 10, 3);
		
		// Prevent redirect
		add_filter('wp_redirect', function($location) {
			return false;
		});
		
		// This will cause the test to exit early, so we expect an exception
		$this->expectException(\WPDieException::class);
		
		$this->gateway->handle_oauth_callbacks();
		
		// Verify the platform secret key was used in the request
		$this->assertNotNull($request_body);
		$this->assertEquals('platform_sk_test_123', $request_body['client_secret']);
	}
}