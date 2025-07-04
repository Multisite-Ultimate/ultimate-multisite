<?php
/**
 * Domain Types enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Domains
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Domains;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Engine\Enum;

/**
 * Domain Stage.
 *
 * @since 2.0.0
 */
class Domain_Stage extends Enum {

	/**
	 * Default product type.
	 */
	const __default = 'checking-dns'; // phpcs:ignore

	const FAILED = 'failed';

	const CHECKING_DNS = 'checking-dns';

	const CHECKING_SSL = 'checking-ssl-cert';

	const DONE_WITHOUT_SSL = 'done-without-ssl';

	const DONE = 'done';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function classes() {

		return [
			static::FAILED           => 'wu-bg-red-200 wu-text-red-700',
			static::CHECKING_DNS     => 'wu-bg-blue-200 wu-text-blue-700',
			static::CHECKING_SSL     => 'wu-bg-yellow-200 wu-text-yellow-700',
			static::DONE             => 'wu-bg-green-200 wu-text-green-700',
			static::DONE_WITHOUT_SSL => 'wu-bg-gray-800 wu-text-white',
		];
	}

	/**
	 * Returns an array with values => labels.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function labels() {

		return [
			static::FAILED           => __('DNS Failed', 'multisite-ultimate'),
			static::CHECKING_DNS     => __('Checking DNS', 'multisite-ultimate'),
			static::CHECKING_SSL     => __('Checking SSL', 'multisite-ultimate'),
			static::DONE             => __('Ready', 'multisite-ultimate'),
			static::DONE_WITHOUT_SSL => __('Ready (without SSL)', 'multisite-ultimate'),
		];
	}
}
