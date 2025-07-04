<?php
/**
 * Membership Status enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Memberships
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Memberships;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Engine\Enum;

/**
 * Membership Status.
 *
 * @since 2.0.0
 */
class Membership_Status extends Enum {

	/**
	 * Default product type.
	 */
	const __default = 'pending'; // phpcs:ignore

	const PENDING = 'pending';

	const ACTIVE = 'active';

	const TRIALING = 'trialing';

	const EXPIRED = 'expired';

	const ON_HOLD = 'on-hold';

	const CANCELLED = 'cancelled';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function classes() {

		return [
			static::PENDING   => 'wu-bg-gray-200 wu-text-gray-700',
			static::ACTIVE    => 'wu-bg-green-200 wu-text-green-700',
			static::TRIALING  => 'wu-bg-orange-200 wu-text-orange-700',
			static::ON_HOLD   => 'wu-bg-blue-200 wu-text-blue-700',
			static::EXPIRED   => 'wu-bg-yellow-200 wu-text-yellow-700',
			static::CANCELLED => 'wu-bg-red-200 wu-text-red-700',
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
			static::PENDING   => __('Pending', 'multisite-ultimate'),
			static::ACTIVE    => __('Active', 'multisite-ultimate'),
			static::TRIALING  => __('Trialing', 'multisite-ultimate'),
			static::ON_HOLD   => __('On Hold', 'multisite-ultimate'),
			static::EXPIRED   => __('Expired', 'multisite-ultimate'),
			static::CANCELLED => __('Cancelled', 'multisite-ultimate'),
		];
	}
}
