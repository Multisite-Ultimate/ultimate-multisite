<?php
/**
 * Session Handler with session cookies
 *
 * This is usefull to handle multi step sign up process
 *
 * @author Arindo Duque <arindo@wpultimo.com>
 * @package WP_Ultimo
 * @since 2.0.18
 */

namespace WP_Ultimo;

use WP_Ultimo\Contracts\Session;
use Delight\Cookie\Cookie;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Session Class.
 */
class Session_Cookie implements Session {

	/**
	 * The data from this current session
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * If the session has already loaded
	 *
	 * @var boolean
	 */
	protected $loaded = false;

	/**
	 * @var string
	 */
	protected $realm_name;

	/**
	 * Constructs the manager.
	 *
	 * @since 2.0.18
	 *
	 * @param string $realm_name The key name of the cookie.
	 */
	public function __construct($realm_name) {

		$this->realm_name = $realm_name;
		if (true === $this->loaded) {
			return;
		}

		$cookie_name = $this->get_cookie_name();

		$data_raw = Cookie::get($cookie_name, '{"new": true}');
		$data_raw = urldecode((string) $data_raw);
		$data_raw = stripslashes($data_raw);

		$this->data = json_decode($data_raw, true);

		$this->loaded = true;
	}

	/**
	 * Get the name of current cookie.
	 *
	 * @return string
	 */
	protected function get_cookie_name() {

		return "wu_session_{$this->realm_name}";
	}

	/**
	 * Gets the value of a session key.
	 *
	 * @since 2.0.18
	 *
	 * @param string $key The key to retrieve.
	 * @return mixed
	 */
	public function get($key = null) {

		if ( ! $key) {
			return $this->data;
		}

		return is_array($this->data) && isset($this->data[ $key ]) ? $this->data[ $key ] : null;
	}

	/**
	 * Set the value of a session key.
	 *
	 * @since 2.0.18
	 *
	 * @param string $key The value of the key to set.
	 * @param mixed  $value The value.
	 */
	public function set($key, $value): bool {

		$this->data[ $key ] = $value;

		return true;
	}

	/**
	 * Appends values to a given key, instead of replacing it.
	 *
	 * @since 2.0.18
	 *
	 * @param string $key    The data key to insert values.
	 * @param array  $values Additional array values.
	 */
	public function add_values($key, $values): bool {

		$old_values = $this->data[ $key ] ?? [];

		$this->data[ $key ] = array_merge($old_values, $values);

		return true;
	}

	/**
	 * Writes to the session and closes the connection.
	 *
	 * @since 2.0.18
	 * @param int $expire the expire date of cookie.
	 */
	public function commit($expire = null): bool {

		if (null === $expire) {
			$expire = 7 * DAY_IN_SECONDS;
		}

		$value = wp_json_encode($this->data, JSON_UNESCAPED_UNICODE);

		$cookie = new Cookie($this->get_cookie_name());
		$cookie->setValue($value);
		$cookie->setMaxAge($expire);
		$cookie->setPath('/');
		$cookie->setDomain(COOKIE_DOMAIN);
		$cookie->setHttpOnly(true);
		$cookie->setSecureOnly(is_ssl());
		$cookie->setSameSiteRestriction('Lax');

		$cookie->save();

		// Set for current call
		$_COOKIE[ $this->get_cookie_name() ] = $value;

		return true;
	}

	/**
	 * Clears the current session.
	 *
	 * @since 2.0.18
	 */
	public function clear(): void {

		$this->data = [];
	}

	/**
	 * Destroys the session. Equivalent to session_destroy();
	 *
	 * @since 2.0.18
	 * @return bool
	 */
	public function destroy() {

		$name = $this->get_cookie_name();

		// unset from current call
		unset($_COOKIE[ $this->get_cookie_name() ]);

		return (new Cookie($name))->delete();
	}
}
