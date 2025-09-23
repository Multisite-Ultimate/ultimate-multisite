<?php
/**
 * WooCommerce API Client for fetching addon products.
 *
 * @package WP_Ultimo
 * @subpackage Helpers
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

use WP_Error;
use WP_Ultimo\Addon_Repository;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WooCommerce API Client for fetching addon products.
 *
 * @since 2.0.0
 */
class WooCommerce_API_Client {

	/**
	 * Base URL for the WooCommerce API.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $base_url;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 * @param string $base_url The base URL for the WooCommerce API.
	 */
	public function __construct($base_url) {
		$this->base_url = trailingslashit($base_url);
	}

	/**
	 * Executes the HTTP request.
	 *
	 * @since 2.0.0
	 * @param string $endpoint The request URL.
	 * @param array  $params Query parameters.
	 * @param string $method HTTP method.
	 * @return array|WP_Error API response or WP_Error on failure.
	 */
	private function execute_request($endpoint, $params = [], $method = 'GET') {
		$url = $this->base_url . 'wp-json/wc/store/v1/' . ltrim($endpoint, '/');
		if ('GET' === $method) {
			$url  = add_query_arg($params, $url);
			$args = [
				'method'  => 'GET',
				'timeout' => 30,
			];
		} else {
			$args = [
				'method'  => $method,
				'body'    => wp_json_encode($params),
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'timeout' => 30,
			];
		}
		$addon_repo = \WP_Ultimo::get_instance()->get_addon_repository();

		$access_token = $addon_repo->get_access_token();
		if ($access_token) {
			$args['headers']['Authorization'] = 'Bearer ' . $access_token;
		}

		$response = wp_remote_request($url, $args);

		if (is_wp_error($response)) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		if ($response_code < 200 || $response_code >= 300) {
			return new WP_Error(
				'woocommerce_api_error',
				sprintf(
					/* translators: %1$s: HTTP response code, %2$s: response body */
					__('WooCommerce API request failed with status %1$s: %2$s', 'ultimate-multisite'),
					$response_code,
					$response_body
				)
			);
		}

		$data = json_decode($response_body, true);

		if (null === $data) {
			return new WP_Error(
				'json_decode_error',
				__('Failed to decode API response JSON', 'ultimate-multisite')
			);
		}

		return $data;
	}

	/**
	 * Gets downloadable products (addons) with specific metadata.
	 *
	 * @since 2.0.0
	 * @return array|WP_Error Array of addon products or WP_Error on failure.
	 */
	public function get_addons() {
		$params = [
			'per_page'     => 100,
			'status'       => 'publish',
			'downloadable' => true,
			'type'         => 'subscription',
			'tag'          => 'addon',
		];

		return $this->execute_request('products', $params);
	}
}
