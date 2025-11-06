<?php
/**
 * Handles hashing to encode ids and prevent spoofing due to auto-increments.
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

use Hashids\Hashids;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles hashing to encode ids and prevent spoofing due to auto-increments.
 *
 * @since 2.0.0
 */
class Hash {

	/**
	 * Hash length.
	 */
	const LENGTH = 10;

	/**
	 * Static-only class.
	 */
	private function __construct() {}

	/**
	 * Encodes a number or ID. Do not use to encode strings.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $number Number to encode.
	 * @param string  $group Hash group. Used to increase entropy.
	 * @return string
	 */
	public static function encode($number, $group = 'wp-ultimo') {

		$hasher = new Hashids($group, self::LENGTH, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

		return $hasher->encode($number);
	}

	/**
	 * Decodes a hash back into an integer.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hash Hash to decode.
	 * @param string $group Hash group. Used to increase entropy.
	 * @return string|int
	 */
	public static function decode($hash, $group = 'wp-ultimo') {

		$hasher = new Hashids($group, 10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

		return current($hasher->decode($hash));
	}
}
