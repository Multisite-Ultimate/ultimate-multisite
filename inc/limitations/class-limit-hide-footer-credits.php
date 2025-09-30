<?php
/**
 * Hide Footer Credits Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.4.5
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Hide Footer Credits Limit Module.
 *
 * Controls whether sites can hide the footer credits based on their plan/product.
 *
 * @since 2.4.5
 */
class Limit_Hide_Footer_Credits extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.4.5
	 * @var string
	 */
	protected $id = 'hide_credits';

	/**
	 * Allows subtype limits to set their own default value for enabled.
	 *
	 * @since 2.4.5
	 * @var bool
	 */
	protected bool $enabled_default_value = false;

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * For this limit, we check if the site is allowed to hide footer credits.
	 * If enabled and limit is true, the site CAN hide credits.
	 *
	 * @since 2.4.5
	 *
	 * @param mixed  $value_to_check Value to check (not used for this limit).
	 * @param mixed  $limit The list of limits in this module.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = ''): bool {

		if (! $this->is_enabled()) {
			return false;
		}

		// For boolean limits (enabled/disabled to hide credits)
		if (is_bool($limit)) {
			return $limit;
		}

		// Default to not allowed if limit is not properly set
		return false;
	}

	/**
	 * Returns a default state.
	 *
	 * @since 2.4.5
	 * @return array
	 */
	public static function default_state(): array {

		return [
			'enabled' => false,
			'limit'   => false,
		];
	}
}
