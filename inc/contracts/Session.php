<?php
/**
 * Session Contract
 *
 * @since 2.1.0
 * @package Contracts
 */

namespace WP_Ultimo\Contracts;

defined('ABSPATH') || exit;

interface Session {

	/**
	 * Gets the value of a session key.
	 *
	 * @param string $key The key to retrieve.
	 * @return mixed
	 */
	public function get($key = null);

	/**
	 * Set the value of a session key.
	 *
	 * @param string $key The value of the key to set.
	 * @param mixed  $value The value.
	 * @return bool
	 */
	public function set($key, $value);

	/**
	 * Appends values to a given key, instead of replacing it.
	 *
	 * @param string $key    The data key to insert values.
	 * @param array  $values Additional array values.
	 * @return bool
	 */
	public function add_values($key, $values);

	/**
	 * Writes to the session and closes the connection.
	 *
	 * @param int $expire the expire date of cookie.
	 * @return bool
	 */
	public function commit($expire = null);

	/**
	 * Clears the current session.
	 */
	public function clear();

	/**
	 * Destroys the session. Equivalent to session_destroy();
	 *
	 * @return bool
	 */
	public function destroy();
}
