<?php
/**
 * PayPal OAuth Handler.
 *
 * Handles the OAuth "Connect with PayPal" flow using PayPal's Partner Referrals API.
 * This enables merchants to connect their PayPal accounts without manually
 * copying API credentials.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use Psr\Log\LogLevel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * PayPal OAuth Handler class.
 *
 * @since 2.0.0
 */
class PayPal_OAuth_Handler {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Partner Attribution ID (BN Code) for PayPal Partner Program tracking.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $bn_code = 'UltimateMultisite_SP_PPCP';

	/**
	 * Holds if we are in test mode.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $test_mode = true;

	/**
	 * Client ID for the partner application.
	 *
	 * This is Ultimate Multisite's partner application credentials,
	 * used to initiate the OAuth flow on behalf of merchants.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $partner_client_id = '';

	/**
	 * Client secret for the partner application.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $partner_client_secret = '';

	/**
	 * Partner merchant ID.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $partner_merchant_id = '';

	/**
	 * Initialize the OAuth handler.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init(): void {

		$this->test_mode = (bool) (int) wu_get_setting('paypal_rest_sandbox_mode', true);

		$this->load_partner_credentials();

		// Register AJAX handlers
		add_action('wp_ajax_wu_paypal_connect', [$this, 'ajax_initiate_oauth']);
		add_action('wp_ajax_wu_paypal_disconnect', [$this, 'ajax_disconnect']);

		// Handle OAuth return callback
		add_action('admin_init', [$this, 'handle_oauth_return']);
	}

	/**
	 * Load partner credentials from settings.
	 *
	 * Partner credentials are used to authenticate with PayPal's Partner API
	 * to initiate the OAuth flow for merchants.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function load_partner_credentials(): void {

		// In production, these would be Ultimate Multisite's partner credentials
		// For now, merchants can use their own REST app credentials for testing
		$mode_prefix = $this->test_mode ? 'sandbox_' : 'live_';

		$this->partner_client_id     = wu_get_setting("paypal_rest_{$mode_prefix}partner_client_id", '');
		$this->partner_client_secret = wu_get_setting("paypal_rest_{$mode_prefix}partner_client_secret", '');
		$this->partner_merchant_id   = wu_get_setting("paypal_rest_{$mode_prefix}partner_merchant_id", '');
	}

	/**
	 * Returns the PayPal API base URL based on test mode.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_api_base_url(): string {

		return $this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
	}

	/**
	 * Returns the PayPal web base URL based on test mode.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_paypal_web_url(): string {

		return $this->test_mode ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com';
	}

	/**
	 * Get an access token for the partner application.
	 *
	 * @since 2.0.0
	 * @return string|\WP_Error Access token or error.
	 */
	protected function get_partner_access_token() {

		// Check for cached token
		$cache_key    = 'wu_paypal_partner_token_' . ($this->test_mode ? 'sandbox' : 'live');
		$cached_token = get_site_transient($cache_key);

		if ($cached_token) {
			return $cached_token;
		}

		if (empty($this->partner_client_id) || empty($this->partner_client_secret)) {
			return new \WP_Error(
				'wu_paypal_missing_partner_credentials',
				__('Partner credentials not configured. Please configure the partner client ID and secret.', 'ultimate-multisite')
			);
		}

		$response = wp_remote_post(
			$this->get_api_base_url() . '/v1/oauth2/token',
			[
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode($this->partner_client_id . ':' . $this->partner_client_secret),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				],
				'body'    => 'grant_type=client_credentials',
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			wu_log_add('paypal', 'Failed to get partner access token: ' . $response->get_error_message(), LogLevel::ERROR);
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$code = wp_remote_retrieve_response_code($response);

		if (200 !== $code || empty($body['access_token'])) {
			$error_msg = $body['error_description'] ?? __('Failed to obtain access token', 'ultimate-multisite');
			wu_log_add('paypal', 'Failed to get partner access token: ' . $error_msg, LogLevel::ERROR);
			return new \WP_Error('wu_paypal_token_error', $error_msg);
		}

		// Cache the token (expires_in is in seconds, subtract 5 minutes for safety)
		$expires_in = isset($body['expires_in']) ? (int) $body['expires_in'] - 300 : 3300;
		set_site_transient($cache_key, $body['access_token'], $expires_in);

		return $body['access_token'];
	}

	/**
	 * Generate a partner referral URL for merchant onboarding.
	 *
	 * Uses PayPal Partner Referrals API v2 to create an onboarding link.
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error Array with action_url and tracking_id, or error.
	 */
	public function generate_referral_url() {

		$access_token = $this->get_partner_access_token();

		if (is_wp_error($access_token)) {
			return $access_token;
		}

		// Generate a unique tracking ID for this onboarding attempt
		$tracking_id = 'wu_' . wp_generate_uuid4();

		// Store tracking ID for verification when merchant returns
		set_site_transient(
			'wu_paypal_onboarding_' . $tracking_id,
			[
				'started'   => time(),
				'test_mode' => $this->test_mode,
			],
			DAY_IN_SECONDS
		);

		// Build the return URL
		$return_url = add_query_arg(
			[
				'page'                 => 'wp-ultimo-settings',
				'tab'                  => 'payment-gateways',
				'wu_paypal_onboarding' => 'complete',
				'tracking_id'          => $tracking_id,
			],
			network_admin_url('admin.php')
		);

		// Build the partner referral request
		$referral_data = [
			'tracking_id'             => $tracking_id,
			'partner_config_override' => [
				'return_url' => $return_url,
			],
			'operations'              => [
				[
					'operation'                  => 'API_INTEGRATION',
					'api_integration_preference' => [
						'rest_api_integration' => [
							'integration_method'  => 'PAYPAL',
							'integration_type'    => 'THIRD_PARTY',
							'third_party_details' => [
								'features' => [
									'PAYMENT',
									'REFUND',
									'PARTNER_FEE',
									'DELAY_FUNDS_DISBURSEMENT',
								],
							],
						],
					],
				],
			],
			'products'                => ['EXPRESS_CHECKOUT'],
			'legal_consents'          => [
				[
					'type'    => 'SHARE_DATA_CONSENT',
					'granted' => true,
				],
			],
		];

		/**
		 * Filters the partner referral data before sending to PayPal.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $referral_data The referral request data.
		 * @param string $tracking_id   The tracking ID for this onboarding.
		 */
		$referral_data = apply_filters('wu_paypal_partner_referral_data', $referral_data, $tracking_id);

		$response = wp_remote_post(
			$this->get_api_base_url() . '/v2/customer/partner-referrals',
			[
				'headers' => [
					'Authorization'                 => 'Bearer ' . $access_token,
					'Content-Type'                  => 'application/json',
					'PayPal-Partner-Attribution-Id' => $this->bn_code,
				],
				'body'    => wp_json_encode($referral_data),
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			wu_log_add('paypal', 'Failed to create partner referral: ' . $response->get_error_message(), LogLevel::ERROR);
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$code = wp_remote_retrieve_response_code($response);

		if (201 !== $code || empty($body['links'])) {
			$error_msg = $body['message'] ?? __('Failed to create partner referral', 'ultimate-multisite');
			wu_log_add('paypal', 'Failed to create partner referral: ' . wp_json_encode($body), LogLevel::ERROR);
			return new \WP_Error('wu_paypal_referral_error', $error_msg);
		}

		// Find the action_url link
		$action_url = '';
		foreach ($body['links'] as $link) {
			if ('action_url' === $link['rel']) {
				$action_url = $link['href'];
				break;
			}
		}

		if (empty($action_url)) {
			return new \WP_Error('wu_paypal_no_action_url', __('No action URL returned from PayPal', 'ultimate-multisite'));
		}

		wu_log_add('paypal', sprintf('Partner referral created. Tracking ID: %s', $tracking_id));

		return [
			'action_url'  => $action_url,
			'tracking_id' => $tracking_id,
		];
	}

	/**
	 * AJAX handler to initiate OAuth flow.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function ajax_initiate_oauth(): void {

		check_ajax_referer('wu_paypal_oauth', 'nonce');

		if (! current_user_can('manage_network_options')) {
			wp_send_json_error(
				[
					'message' => __('You do not have permission to do this.', 'ultimate-multisite'),
				]
			);
		}

		// Update test mode from request if provided
		if (isset($_POST['sandbox_mode'])) {
			$this->test_mode = (bool) (int) $_POST['sandbox_mode'];
			$this->load_partner_credentials();
		}

		$result = $this->generate_referral_url();

		if (is_wp_error($result)) {
			wp_send_json_error(
				[
					'message' => $result->get_error_message(),
				]
			);
		}

		wp_send_json_success(
			[
				'redirect_url' => $result['action_url'],
				'tracking_id'  => $result['tracking_id'],
			]
		);
	}

	/**
	 * Handle the OAuth return callback.
	 *
	 * When the merchant completes the PayPal onboarding flow, they are redirected
	 * back to WordPress with parameters indicating success/failure.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_oauth_return(): void {

		// Check if this is an OAuth return - nonce verification not possible for external OAuth callback
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from PayPal
		if (! isset($_GET['wu_paypal_onboarding']) || 'complete' !== $_GET['wu_paypal_onboarding']) {
			return;
		}

		// Verify we're on the settings page
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from PayPal
		if (! isset($_GET['page']) || 'wp-ultimo-settings' !== $_GET['page']) {
			return;
		}

		// Get parameters from PayPal
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- OAuth callback from PayPal
		$merchant_id         = isset($_GET['merchantIdInPayPal']) ? sanitize_text_field(wp_unslash($_GET['merchantIdInPayPal'])) : '';
		$merchant_email      = isset($_GET['merchantId']) ? sanitize_email(wp_unslash($_GET['merchantId'])) : '';
		$permissions_granted = isset($_GET['permissionsGranted']) && 'true' === $_GET['permissionsGranted'];
		$consent_status      = isset($_GET['consentStatus']) && 'true' === $_GET['consentStatus'];
		$risk_status         = isset($_GET['isEmailConfirmed']) ? sanitize_text_field(wp_unslash($_GET['isEmailConfirmed'])) : '';
		$tracking_id         = isset($_GET['tracking_id']) ? sanitize_text_field(wp_unslash($_GET['tracking_id'])) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Verify tracking ID
		$onboarding_data = get_site_transient('wu_paypal_onboarding_' . $tracking_id);

		if (! $onboarding_data) {
			wu_log_add('paypal', 'OAuth return with invalid tracking ID: ' . $tracking_id, LogLevel::WARNING);
			$this->add_oauth_notice('error', __('Invalid onboarding session. Please try again.', 'ultimate-multisite'));
			return;
		}

		// Update test mode to match the onboarding session
		$this->test_mode = $onboarding_data['test_mode'];

		// Check if permissions were granted
		if (! $permissions_granted) {
			wu_log_add('paypal', 'OAuth: Merchant did not grant permissions', LogLevel::WARNING);
			$this->add_oauth_notice('warning', __('PayPal permissions were not granted. Please try again and approve the required permissions.', 'ultimate-multisite'));
			return;
		}

		// Verify the merchant status with PayPal
		$merchant_status = $this->verify_merchant_status($merchant_id, $tracking_id);

		if (is_wp_error($merchant_status)) {
			wu_log_add('paypal', 'Failed to verify merchant status: ' . $merchant_status->get_error_message(), LogLevel::ERROR);
			$this->add_oauth_notice('error', __('Failed to verify your PayPal account status. Please try again.', 'ultimate-multisite'));
			return;
		}

		// Store the merchant credentials
		$mode_prefix = $this->test_mode ? 'sandbox' : 'live';

		wu_save_setting("paypal_rest_{$mode_prefix}_merchant_id", $merchant_id);
		wu_save_setting("paypal_rest_{$mode_prefix}_merchant_email", $merchant_email);
		wu_save_setting('paypal_rest_connected', true);
		wu_save_setting('paypal_rest_connection_date', current_time('mysql'));
		wu_save_setting('paypal_rest_connection_mode', $mode_prefix);

		// Store additional status info if available
		if (! empty($merchant_status['payments_receivable'])) {
			wu_save_setting("paypal_rest_{$mode_prefix}_payments_receivable", $merchant_status['payments_receivable']);
		}
		if (! empty($merchant_status['primary_email_confirmed'])) {
			wu_save_setting("paypal_rest_{$mode_prefix}_email_confirmed", $merchant_status['primary_email_confirmed']);
		}

		// Clean up the tracking transient
		delete_site_transient('wu_paypal_onboarding_' . $tracking_id);

		wu_log_add('paypal', sprintf('PayPal OAuth completed. Merchant ID: %s, Mode: %s', $merchant_id, $mode_prefix));

		// Automatically install webhooks for the connected account
		$this->install_webhook_after_oauth($mode_prefix);

		$this->add_oauth_notice('success', __('PayPal account connected successfully!', 'ultimate-multisite'));

		// Redirect to remove query parameters
		wp_safe_redirect(
			add_query_arg(
				[
					'page'             => 'wp-ultimo-settings',
					'tab'              => 'payment-gateways',
					'paypal_connected' => '1',
				],
				network_admin_url('admin.php')
			)
		);
		exit;
	}

	/**
	 * Verify merchant status after OAuth completion.
	 *
	 * Calls PayPal to verify the merchant's integration status and capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @param string $merchant_id  The merchant's PayPal ID.
	 * @param string $tracking_id  The tracking ID from onboarding.
	 * @return array|\WP_Error Merchant status data or error.
	 */
	protected function verify_merchant_status(string $merchant_id, string $tracking_id) {

		$access_token = $this->get_partner_access_token();

		if (is_wp_error($access_token)) {
			return $access_token;
		}

		if (empty($this->partner_merchant_id)) {
			// If no partner merchant ID, we can't verify status via partner API
			// Return basic success
			return [
				'merchant_id'         => $merchant_id,
				'payments_receivable' => true,
			];
		}

		$response = wp_remote_get(
			$this->get_api_base_url() . '/v1/customer/partners/' . $this->partner_merchant_id . '/merchant-integrations/' . $merchant_id,
			[
				'headers' => [
					'Authorization'                 => 'Bearer ' . $access_token,
					'Content-Type'                  => 'application/json',
					'PayPal-Partner-Attribution-Id' => $this->bn_code,
				],
				'timeout' => 30,
			]
		);

		if (is_wp_error($response)) {
			return $response;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$code = wp_remote_retrieve_response_code($response);

		if (200 !== $code) {
			$error_msg = $body['message'] ?? __('Failed to verify merchant status', 'ultimate-multisite');
			return new \WP_Error('wu_paypal_verify_error', $error_msg);
		}

		return [
			'merchant_id'             => $body['merchant_id'] ?? $merchant_id,
			'tracking_id'             => $body['tracking_id'] ?? $tracking_id,
			'payments_receivable'     => $body['payments_receivable'] ?? false,
			'primary_email_confirmed' => $body['primary_email_confirmed'] ?? false,
			'oauth_integrations'      => $body['oauth_integrations'] ?? [],
		];
	}

	/**
	 * AJAX handler to disconnect PayPal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function ajax_disconnect(): void {

		check_ajax_referer('wu_paypal_oauth', 'nonce');

		if (! current_user_can('manage_network_options')) {
			wp_send_json_error(
				[
					'message' => __('You do not have permission to do this.', 'ultimate-multisite'),
				]
			);
		}

		// Delete webhooks before clearing credentials
		$this->delete_webhooks_on_disconnect();

		// Clear all connection data
		$settings_to_clear = [
			'paypal_rest_connected',
			'paypal_rest_connection_date',
			'paypal_rest_connection_mode',
			'paypal_rest_sandbox_merchant_id',
			'paypal_rest_sandbox_merchant_email',
			'paypal_rest_sandbox_payments_receivable',
			'paypal_rest_sandbox_email_confirmed',
			'paypal_rest_live_merchant_id',
			'paypal_rest_live_merchant_email',
			'paypal_rest_live_payments_receivable',
			'paypal_rest_live_email_confirmed',
			'paypal_rest_sandbox_webhook_id',
			'paypal_rest_live_webhook_id',
		];

		foreach ($settings_to_clear as $setting) {
			wu_save_setting($setting, '');
		}

		// Clear cached access tokens
		delete_site_transient('wu_paypal_partner_token_sandbox');
		delete_site_transient('wu_paypal_partner_token_live');
		delete_site_transient('wu_paypal_rest_access_token_sandbox');
		delete_site_transient('wu_paypal_rest_access_token_live');

		wu_log_add('paypal', 'PayPal account disconnected');

		wp_send_json_success(
			[
				'message' => __('PayPal account disconnected successfully.', 'ultimate-multisite'),
			]
		);
	}

	/**
	 * Add an admin notice for OAuth status.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type    Notice type: 'success', 'error', 'warning', 'info'.
	 * @param string $message The notice message.
	 * @return void
	 */
	protected function add_oauth_notice(string $type, string $message): void {

		set_site_transient(
			'wu_paypal_oauth_notice',
			[
				'type'    => $type,
				'message' => $message,
			],
			60
		);
	}

	/**
	 * Display OAuth notices.
	 *
	 * Should be called on admin_notices hook.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_oauth_notices(): void {

		$notice = get_site_transient('wu_paypal_oauth_notice');

		if ($notice) {
			delete_site_transient('wu_paypal_oauth_notice');

			$class = 'notice notice-' . esc_attr($notice['type']) . ' is-dismissible';
			printf(
				'<div class="%1$s"><p>%2$s</p></div>',
				esc_attr($class),
				esc_html($notice['message'])
			);
		}
	}

	/**
	 * Check if OAuth is fully configured.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_configured(): bool {

		return ! empty($this->partner_client_id) && ! empty($this->partner_client_secret);
	}

	/**
	 * Check if a merchant is connected via OAuth.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $sandbox Whether to check sandbox mode.
	 * @return bool
	 */
	public function is_merchant_connected(bool $sandbox = true): bool {

		$mode_prefix = $sandbox ? 'sandbox' : 'live';
		$merchant_id = wu_get_setting("paypal_rest_{$mode_prefix}_merchant_id", '');

		return ! empty($merchant_id);
	}

	/**
	 * Get connected merchant details.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $sandbox Whether to get sandbox mode details.
	 * @return array
	 */
	public function get_merchant_details(bool $sandbox = true): array {

		$mode_prefix = $sandbox ? 'sandbox' : 'live';

		return [
			'merchant_id'         => wu_get_setting("paypal_rest_{$mode_prefix}_merchant_id", ''),
			'merchant_email'      => wu_get_setting("paypal_rest_{$mode_prefix}_merchant_email", ''),
			'payments_receivable' => wu_get_setting("paypal_rest_{$mode_prefix}_payments_receivable", false),
			'email_confirmed'     => wu_get_setting("paypal_rest_{$mode_prefix}_email_confirmed", false),
			'connection_date'     => wu_get_setting('paypal_rest_connection_date', ''),
		];
	}

	/**
	 * Install webhooks after successful OAuth connection.
	 *
	 * Creates the webhook endpoint in PayPal to receive subscription and payment events.
	 *
	 * @since 2.0.0
	 *
	 * @param string $mode_prefix The mode prefix ('sandbox' or 'live').
	 * @return void
	 */
	protected function install_webhook_after_oauth(string $mode_prefix): void {

		try {
			// Get the PayPal REST gateway instance
			$gateway_manager = \WP_Ultimo\Managers\Gateway_Manager::get_instance();
			$gateway         = $gateway_manager->get_gateway('paypal-rest');

			if (! $gateway instanceof PayPal_REST_Gateway) {
				wu_log_add('paypal', 'Could not get PayPal REST gateway instance for webhook installation', LogLevel::WARNING);
				return;
			}

			// Ensure the gateway is in the correct mode
			$gateway->set_test_mode('sandbox' === $mode_prefix);

			// Install the webhook
			$result = $gateway->install_webhook();

			if (true === $result) {
				wu_log_add('paypal', sprintf('Webhook installed successfully for %s mode after OAuth', $mode_prefix));
			} elseif (is_wp_error($result)) {
				wu_log_add('paypal', sprintf('Failed to install webhook after OAuth: %s', $result->get_error_message()), LogLevel::ERROR);
			}
		} catch (\Exception $e) {
			wu_log_add('paypal', sprintf('Exception installing webhook after OAuth: %s', $e->getMessage()), LogLevel::ERROR);
		}
	}

	/**
	 * Delete webhooks when disconnecting from PayPal.
	 *
	 * Attempts to delete webhooks from both sandbox and live modes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function delete_webhooks_on_disconnect(): void {

		try {
			$gateway_manager = \WP_Ultimo\Managers\Gateway_Manager::get_instance();
			$gateway         = $gateway_manager->get_gateway('paypal-rest');

			if (! $gateway instanceof PayPal_REST_Gateway) {
				return;
			}

			// Try to delete sandbox webhook
			$gateway->set_test_mode(true);
			$result = $gateway->delete_webhook();
			if (is_wp_error($result)) {
				wu_log_add('paypal', sprintf('Failed to delete sandbox webhook: %s', $result->get_error_message()), LogLevel::WARNING);
			} else {
				wu_log_add('paypal', 'Sandbox webhook deleted during disconnect');
			}

			// Try to delete live webhook
			$gateway->set_test_mode(false);
			$result = $gateway->delete_webhook();
			if (is_wp_error($result)) {
				wu_log_add('paypal', sprintf('Failed to delete live webhook: %s', $result->get_error_message()), LogLevel::WARNING);
			} else {
				wu_log_add('paypal', 'Live webhook deleted during disconnect');
			}
		} catch (\Exception $e) {
			wu_log_add('paypal', sprintf('Exception deleting webhooks during disconnect: %s', $e->getMessage()), LogLevel::WARNING);
		}
	}
}
