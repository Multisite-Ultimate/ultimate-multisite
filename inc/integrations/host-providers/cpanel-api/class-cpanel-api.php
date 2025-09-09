<?php
/**
 * CPanel API wrapper to send the calls.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/CPanel_API
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers\CPanel_API;

defined('ABSPATH') || exit;

/**
 * CPanel API wrapper to send the calls.
 */
class CPanel_API {


	/**
	 * Holds the cookie tokens
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $cpsess;

	/**
	 * Holds the cPanel url.
	 *
	 * @since 2.0.0
	 * @var string|null
	 */
	private ?string $homepage = null;

	/**
	 * Holds the execution url.
	 *
	 * @since 2.0.0
	 * @var string|null
	 */
	private ?string $ex_page = null;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var integer
	 */
	private $port;

	/**
	 * @var boolean
	 */
	private $log;

	/**
	 * Creates the CPanel_API Object.
	 *
	 * @since 1.6.2
	 * @param string  $username cPanel username.
	 * @param string  $password cPanel password. Yep =(.
	 * @param string  $host cPanel URL.
	 * @param integer $port cPanel port.
	 * @param boolean $log Log.
	 */
	public function __construct(
		$username,
		$password,
		$host,
		$port = 2083,
		$log = false
	) {

		$this->username = $username;
		$this->password = $password;
		$this->host     = $host;
		$this->port     = $port;
		$this->log      = $log;

		// Signs up
		$this->sign_in();
	}


	/**
	 * Logs error or success messages.
	 *
	 * @since 1.6.2
	 * @param string $message Message to be logged.
	 */
	public function log($message) {

		wu_log_add('integration-cpanel', $message);
	}

	/**
	 * Sends the request to the CPanel API using WordPress HTTP API.
	 *
	 * @since 1.6.2
	 * @param string $url URL endpoint.
	 * @param array  $params Request parameters to send.
	 * @return mixed
	 */
	private function request($url, $params = []) {

		// Get stored cookies from transient
		$cookies = get_transient('wu_cpanel_cookies_' . md5($this->host . $this->username)) ?: [];

		// Prepare request arguments
		$args = [
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.1',
			'sslverify'   => false,
			'cookies'     => $cookies,
			'headers'     => [
				'User-Agent'      => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0',
				'Host'            => $this->host,
				'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
				'Accept-Encoding' => 'gzip, deflate',
				'Connection'      => 'keep-alive',
				'Content-Type'    => 'application/x-www-form-urlencoded',
			],
		];

		// Add POST data if provided
		if (! empty($params)) {
			$args['method'] = 'POST';
			$args['body']   = $params;
		}

		if (! empty($params)) {
			$response = wp_remote_post($url, $args);
		} else {
			$response = wp_remote_get($url, $args);
		}

		if (is_wp_error($response)) {
			// translators: %s is the error.
			$this->log(sprintf(__('cPanel API Error: %s', 'multisite-ultimate'), $response->get_error_message()));
			return false;
		}

		// Save cookies from response headers to transient
		$this->save_cookies_from_response($response);

		$body = wp_remote_retrieve_body($response);

		return $body;
	}

	/**
	 * Save cookies from response headers to transient.
	 *
	 * @since 2.4.1
	 * @param array $response HTTP response.
	 */
	private function save_cookies_from_response($response) {
		$headers     = wp_remote_retrieve_headers($response);
		$set_cookies = isset($headers['set-cookie']) ? $headers['set-cookie'] : [];

		if (! is_array($set_cookies)) {
			$set_cookies = [$set_cookies];
		}

		if (! empty($set_cookies)) {
			$cookies = [];
			foreach ($set_cookies as $cookie) {
				// Parse cookie string to extract name and value
				if (preg_match('/^([^=]+)=([^;]*)/', $cookie, $matches)) {
					$cookies[ $matches[1] ] = $matches[2];
				}
			}

			if (! empty($cookies)) {
				// Store cookies in transient for 1 hour
				set_transient('wu_cpanel_cookies_' . md5($this->host . $this->username), $cookies, HOUR_IN_SECONDS);
			}
		}
	}

	/**
	 * Get the base URL to make the calls.
	 *
	 * @since 2.0.0
	 */
	private function get_base_url(): string {

		return sprintf('https://%s:%s', $this->host, $this->port);
	}

	/**
	 * Signs in on the cPanel.
	 *
	 * @since 1.6.2
	 */
	private function sign_in() {

		$url  = $this->get_base_url() . '/login/?login_only=1';
		$url .= '&user=' . $this->username . '&pass=' . rawurlencode($this->password);

		$reply = $this->request($url);

		$reply = json_decode((string) $reply, true);

		if (isset($reply['status']) && 1 == $reply['status']) { // phpcs:ignore

			$this->cpsess   = $reply['security_token'];
			$this->homepage = $this->get_base_url() . $reply['redirect'];
			$this->ex_page  = $this->get_base_url() . "/{$this->cpsess}/execute/";
		} else {
			$this->log(__('Cannot connect to your cPanel server : Invalid Credentials', 'multisite-ultimate'));
		}
	}

	/**
	 * Executes API calls, taking the request to the right API version
	 *
	 * @since 1.6.2
	 * @throws \Exception Throwns exception when the api is invalid.
	 * @param string $api API version.
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function_name Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return boolean
	 */
	public function execute($api, $module, $function_name, array $parameters = []) {

		switch ($api) {
			case 'api2':
				return $this->api2($module, $function_name, $parameters);
			case 'uapi':
				return $this->uapi($module, $function_name, $parameters);
			default:
				throw new \Exception('Invalid API type : api2 and uapi are accepted', 1);
		}
	}

	/**
	 * Send the request if the API being used is the UAPI (newer version)
	 *
	 * @since 1.6.2
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function_name Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return mixed
	 */
	public function uapi($module, $function_name, array $parameters = []) {

		if (count($parameters) < 1) {
			$parameters = '';
		} else {
			$parameters = (http_build_query($parameters));
		}

		return json_decode((string) $this->request($this->ex_page . $module . '/' . $function_name . '?' . $parameters));
	}

	/**
	 * Send the request if the API being used is the API2 (older version)
	 *
	 * @since 1.6.2
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function_name Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return mixed
	 */
	public function api2($module, $function_name, array $parameters = []) {

		if (count($parameters) < 1) {
			$parameters = '';
		} else {
			$parameters = (http_build_query($parameters));
		}

		$url = $this->get_base_url() . $this->cpsess . '/json-api/cpanel' .
				'?cpanel_jsonapi_version=2' .
				"&cpanel_jsonapi_func={$function_name}" .
				"&cpanel_jsonapi_module={$module}&" . $parameters;

		return json_decode((string) $this->request($url, $parameters));
	}
}
