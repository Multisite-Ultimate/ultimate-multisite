<?php

namespace WP_Ultimo;

defined('ABSPATH') || exit;

/**
 * License class for maintaining compatibility with addons.
 *
 * This class provides license-related functionality primarily for
 * backward compatibility with existing addons.
 */
class License {
	use \WP_Ultimo\Traits\Singleton;

	/**
	 * This exists to maintain compatibility with Addons
	 *
	 * @return null
	 */
	public function get_license_key() {
		return null;
	}
}
