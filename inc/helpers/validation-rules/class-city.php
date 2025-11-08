<?php
/**
 * Adds a validation rules for cities
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.11
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

// Exit if accessed directly
defined('ABSPATH') || exit;

use Rakit\Validation\Rule;

/**
 * Validates template sites.
 *
 * @since 2.0.4
 */
class City extends Rule {

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.4
	 * @var array
	 */
	protected $fillableParams = ['country', 'state']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.11
	 *
	 * @param mixed $city The city value detected.
	 */
	public function check($city): bool {

		$check = true;

		$country = $this->parameter('country') ?? wu_request('billing_country');

		$state = $this->parameter('state') ?? wu_request('billing_state');

		if ($country && $state && $city) {
			$state = strtoupper((string) $state);

			$allowed_cities = wu_get_country_cities(strtoupper((string) $country), $state, false);

			if (! empty($allowed_cities)) {
				$check = in_array($city, $allowed_cities, true);
			}
		}

		return $check;
	}
}
