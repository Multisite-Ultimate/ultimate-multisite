<?php
/**
 * CPanel API wrapper to send the calls.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/CPanel_API
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers\CPanel_API;

use WP_Ultimo\Logger;

/**
 * CPanel API wrapper to send the calls.
 */
class CPanel_API {

	/**
	 * Holds the name of the cookis file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $cookie_file;

	/**
	 * Holds the curl file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $curlfile;

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
		// Generates the cookie file
		$this->generate_cookie();
		$this->cookie_file = Logger::get_logs_folder() . 'integration-cpanel-cookie.log';

		// Signs up
		$this->sign_in();
	}

	/**
	 * Generate the Cookie File, that is used to make API requests to CPanel.
	 *
	 * @since 1.6.2
	 * @return void
	 */
	public function generate_cookie(): void {

		wu_log_add('integration-cpanel-cookie', '');
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
	 * Sends the request to the CPanel API.
	 *
	 * @since 1.6.2
	 * @param string $url URL endpoint.
	 * @param array  $params Request parameters to send.
	 * @return mixed
	 */
	private function request($url, $params = []) {

		if ($this->log) {
			$curl_log = fopen($this->curlfile, 'a+');
		}

		if ( ! file_exists($this->cookie_file)) {
			try {
				fopen($this->cookie_file, 'w');
			} catch (\Exception $ex) {
				if ( ! file_exists($this->cookie_file)) {
					$this->log($ex . __('Cookie file missing.', 'multisite-ultimate'));

					return false;
				}
			}
		} elseif ( ! is_writable($this->cookie_file)) {
			$this->log(__('Cookie file not writable', 'multisite-ultimate'));

			return false;
		}

		$ch = curl_init();

		$curl_opts = [
			CURLOPT_URL            => $url,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_COOKIEJAR      => realpath($this->cookie_file),
			CURLOPT_COOKIEFILE     => realpath($this->cookie_file),
			CURLOPT_HTTPHEADER     => [
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0',
				'Host: ' . $this->host,
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: en-US,en;q=0.5',
				'Accept-Encoding: gzip, deflate',
				'Connection: keep-alive',
				'Content-Type: application/x-www-form-urlencoded',
			],
		];

		if ( ! empty($params)) {
			$curl_opts[ CURLOPT_POST ]       = true;
			$curl_opts[ CURLOPT_POSTFIELDS ] = $params;
		}

		if ($this->log) {
			$curl_opts[ CURLOPT_STDERR ]      = $curl_log;
			$curl_opts[ CURLOPT_FAILONERROR ] = false;
			$curl_opts[ CURLOPT_VERBOSE ]     = true;
		}

		curl_setopt_array($ch, $curl_opts);

		$answer = curl_exec($ch);

		if (curl_error($ch)) {

			// translators: %s is the cURL error.
			$this->log(sprintf(__('cPanel API Error: %s', 'multisite-ultimate'), curl_error($ch)));

			return false;
		}

		curl_close($ch);

		if ($this->log) {
			fclose($curl_log);
		}

		return (@gzdecode($answer)) ? gzdecode($answer) : $answer; // phpcs:ignore
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
