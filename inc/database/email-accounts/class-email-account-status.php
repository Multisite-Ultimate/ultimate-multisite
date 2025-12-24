<?php
/**
 * Email Account Status enum.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Email_Accounts
 * @since 2.3.0
 */

namespace WP_Ultimo\Database\Email_Accounts;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Database\Engine\Enum;

/**
 * Email Account Status.
 *
 * @since 2.3.0
 */
class Email_Account_Status extends Enum {

	/**
	 * Default status.
	 */
	const __default = 'pending'; // phpcs:ignore

	const PENDING = 'pending';

	const PROVISIONING = 'provisioning';

	const ACTIVE = 'active';

	const SUSPENDED = 'suspended';

	const FAILED = 'failed';

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	protected function classes() {

		return [
			static::PENDING      => 'wu-bg-gray-200 wu-text-gray-700',
			static::PROVISIONING => 'wu-bg-blue-200 wu-text-blue-700',
			static::ACTIVE       => 'wu-bg-green-200 wu-text-green-700',
			static::SUSPENDED    => 'wu-bg-yellow-200 wu-text-yellow-700',
			static::FAILED       => 'wu-bg-red-200 wu-text-red-700',
		];
	}

	/**
	 * Returns an array with values => labels.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	protected function labels() {

		return [
			static::PENDING      => __('Pending', 'ultimate-multisite'),
			static::PROVISIONING => __('Provisioning', 'ultimate-multisite'),
			static::ACTIVE       => __('Active', 'ultimate-multisite'),
			static::SUSPENDED    => __('Suspended', 'ultimate-multisite'),
			static::FAILED       => __('Failed', 'ultimate-multisite'),
		];
	}

	/**
	 * Returns an array with values => icons.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	protected function icons() {

		return [
			static::PENDING      => 'dashicons-wu-clock',
			static::PROVISIONING => 'dashicons-wu-loader',
			static::ACTIVE       => 'dashicons-wu-check',
			static::SUSPENDED    => 'dashicons-wu-block',
			static::FAILED       => 'dashicons-wu-circle-with-cross',
		];
	}

	/**
	 * Get the icon for the current status.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_icon(): string {

		$icons = $this->icons();

		return $icons[ $this->get_value() ] ?? '';
	}
}
