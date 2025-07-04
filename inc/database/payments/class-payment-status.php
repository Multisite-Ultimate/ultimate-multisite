<?php
/**
 * Payment Status enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Payments
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Payments;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Engine\Enum;

/**
 * Payment Status.
 *
 * @since 2.0.0
 */
class Payment_Status extends Enum {

	/**
	 * Default product type.
	 */
	const __default = 'pending'; // phpcs:ignore

	const PENDING = 'pending';

	const COMPLETED = 'completed';

	const REFUND = 'refunded';

	const PARTIAL_REFUND = 'partially-refunded';

	const PARTIAL = 'partially-paid';

	const FAILED = 'failed';

	const CANCELLED = 'cancelled';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function classes() {

		return [
			static::PENDING        => 'wu-bg-gray-200 wu-text-gray-700',
			static::COMPLETED      => 'wu-bg-green-200 wu-text-green-700',
			static::REFUND         => 'wu-bg-blue-200 wu-text-gray-700',
			static::PARTIAL_REFUND => 'wu-bg-blue-200 wu-text-gray-700',
			static::PARTIAL        => 'wu-bg-yellow-200 wu-text-yellow-700',
			static::FAILED         => 'wu-bg-red-200 wu-text-red-700',
			static::CANCELLED      => 'wu-bg-orange-200 wu-text-orange-700',
		];
	}

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function icon_classes() {

		return [
			static::PENDING        => 'wu-align-middle dashicons-wu-clock wu-text-gray-700',
			static::COMPLETED      => 'wu-align-middle dashicons-wu-check wu-text-green-700',
			static::REFUND         => 'wu-align-middle dashicons-wu-cw wu-text-gray-700',
			static::PARTIAL_REFUND => 'wu-align-middle dashicons-wu-cw wu-text-gray-700',
			static::PARTIAL        => 'wu-align-middle dashicons-wu-cw wu-text-yellow-700',
			static::FAILED         => 'wu-align-middle dashicons-wu-circle-with-cross wu-text-red-700',
			static::CANCELLED      => 'wu-align-middle dashicons-wu-circle-with-cross wu-text-orange-700',
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
			static::PENDING        => __('Pending', 'multisite-ultimate'),
			static::COMPLETED      => __('Completed', 'multisite-ultimate'),
			static::REFUND         => __('Refunded', 'multisite-ultimate'),
			static::PARTIAL_REFUND => __('Partially Refunded', 'multisite-ultimate'),
			static::PARTIAL        => __('Partially Paid', 'multisite-ultimate'),
			static::FAILED         => __('Failed', 'multisite-ultimate'),
			static::CANCELLED      => __('Cancelled', 'multisite-ultimate'),
		];
	}
}
