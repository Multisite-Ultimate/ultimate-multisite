<?php
/**
 * Stripe Connect utilities.
 *
 * Handles Stripe Connect OAuth flow and account management.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class Stripe_Connect_Utils
 *
 * Handles Stripe Connect OAuth flow and account management.
 *
 * @since 2.0.0
 */
class Stripe_Connect_Utils {

	/**
	 * Handle the OAuth callback from Stripe.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_oauth_callback() {
		// Check if this is a Stripe Connect OAuth callback
		if (! isset($_GET['code']) || ! isset($_GET['state'])) {
			return;
		}

		// Sanitize inputs
		$code = sanitize_text_field(wp_unslash($_GET['code']));
		$state = sanitize_text_field(wp_unslash($_GET['state']));

		// Verify the state parameter to prevent CSRF attacks
		$expected_state = get_option('wu_stripe_connect_state');
		if (empty($expected_state) || $expected_state !== $state) {
			wp_die(__('Invalid state parameter', 'ultimate-multisite'));
		}

		// Clear the state after verification
		delete_option('wu_stripe_connect_state');

		$response = wp_remote_post(
			'https://connect.stripe.com/oauth/token',
			[
				'body' => [
					'grant_type'    => 'authorization_code',
					'client_secret' => self::get_stripe_secret_key(),
					'code'          => $code,
					'redirect_uri'  => self::get_redirect_uri(),
				],
			]
		);

		if (is_wp_error($response)) {
			wp_die(__('Error connecting to Stripe: ', 'ultimate-multisite') . $response->get_error_message());
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_die(__('Error parsing Stripe response', 'ultimate-multisite'));
		}

		if (isset($body['error'])) {
			wp_die(__('Stripe error: ', 'ultimate-multisite') . $body['error_description']);
		}

		// Save the tokens
		self::save_account_tokens($body);

		// Redirect back to settings page with success message
		$redirect_url = add_query_arg(
			[
				'page'   => 'wu-settings',
				'tab'    => 'payment-gateways',
				'notice' => 'stripe_connect_success',
			],
			admin_url('admin.php')
		);

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Handle disconnecting the Stripe Connect account.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function handle_disconnect() {
		if (! isset($_GET['stripe_connect_disconnect'])) {
			return;
		}

		// Verify nonce
		$nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
		if (! wp_verify_nonce($nonce, 'wu_disconnect_stripe_connect')) {
			wp_die(__('Invalid nonce', 'ultimate-multisite'));
		}

		// Delete the saved tokens
		self::delete_account_tokens();

		// Redirect back to settings page with success message
		$redirect_url = add_query_arg(
			[
				'page'   => 'wu-settings',
				'tab'    => 'payment-gateways',
				'notice' => 'stripe_connect_disconnected',
			],
			admin_url('admin.php')
		);

		wp_safe_redirect($redirect_url);
		exit;
	}

	/**
	 * Get the redirect URI for Stripe Connect OAuth.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function get_redirect_uri() {
		return add_query_arg(
			[
				'page' => 'wu-settings',
				'tab'  => 'payment-gateways',
			],
			admin_url('admin.php')
		);
	}

	/**
	 * Get the Stripe secret key based on mode.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function get_stripe_secret_key() {
		$is_test_mode = (bool) wu_get_setting('stripe_connect_sandbox_mode', true);

		if ($is_test_mode) {
			return wu_get_setting('stripe_connect_test_sk_key', '');
		} else {
			return wu_get_setting('stripe_connect_live_sk_key', '');
		}
	}

	/**
	 * Save the account tokens from the OAuth flow.
	 *
	 * @since 2.0.0
	 * @param array $tokens The tokens from the OAuth response.
	 * @return void
	 */
	public static function save_account_tokens($tokens) {
		$is_test_mode = (bool) wu_get_setting('stripe_connect_sandbox_mode', true);

		// Save the access token
		if ($is_test_mode) {
			wu_save_setting('stripe_connect_test_access_token', $tokens['access_token']);
			wu_save_setting('stripe_connect_test_refresh_token', $tokens['refresh_token'] ?? '');
			wu_save_setting('stripe_connect_test_account_id', $tokens['stripe_user_id']);
			wu_save_setting('stripe_connect_test_publishable_key', $tokens['stripe_publishable_key']);
		} else {
			wu_save_setting('stripe_connect_live_access_token', $tokens['access_token']);
			wu_save_setting('stripe_connect_live_refresh_token', $tokens['refresh_token'] ?? '');
			wu_save_setting('stripe_connect_live_account_id', $tokens['stripe_user_id']);
			wu_save_setting('stripe_connect_live_publishable_key', $tokens['stripe_publishable_key']);
		}
	}

	/**
	 * Delete the saved account tokens.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function delete_account_tokens() {
		// Delete both test and live tokens
		wu_save_setting('stripe_connect_test_access_token', '');
		wu_save_setting('stripe_connect_test_refresh_token', '');
		wu_save_setting('stripe_connect_test_account_id', '');
		wu_save_setting('stripe_connect_test_publishable_key', '');

		wu_save_setting('stripe_connect_live_access_token', '');
		wu_save_setting('stripe_connect_live_refresh_token', '');
		wu_save_setting('stripe_connect_live_account_id', '');
		wu_save_setting('stripe_connect_live_publishable_key', '');
	}

	/**
	 * Get the Stripe Connect account ID.
	 *
	 * @since 2.0.0
	 * @return string|false
	 */
	public static function get_account_id() {
		$is_test_mode = (bool) wu_get_setting('stripe_connect_sandbox_mode', true);

		if ($is_test_mode) {
			return wu_get_setting('stripe_connect_test_account_id', false);
		} else {
			return wu_get_setting('stripe_connect_live_account_id', false);
		}
	}

	/**
	 * Check if the account is connected.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public static function is_connected() {
		return (bool) self::get_account_id();
	}
}
