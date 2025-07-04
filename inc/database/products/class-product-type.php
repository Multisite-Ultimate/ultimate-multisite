<?php
/**
 * Product Types enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Products
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Products;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Engine\Enum;

/**
 * Product Types.
 *
 * @since 2.0.0
 */
class Product_Type extends Enum {

	/**
	 * Default product type.
	 */
	const __default = 'plan'; // phpcs:ignore

	const PLAN = 'plan';

	const PACKAGE = 'package';

	const SERVICE = 'service';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function classes() {

		return [
			static::PLAN    => 'wu-bg-green-200 wu-text-green-700',
			static::PACKAGE => 'wu-bg-gray-200 wu-text-blue-700',
			static::SERVICE => 'wu-bg-yellow-200 wu-text-yellow-700',
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
			static::PLAN    => __('Plan', 'multisite-ultimate'),
			static::PACKAGE => __('Package', 'multisite-ultimate'),
			static::SERVICE => __('Service', 'multisite-ultimate'),
		];
	}
}
