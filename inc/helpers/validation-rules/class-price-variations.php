<?php
/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use Rakit\Validation\Rule;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @since 2.0.0
 */
class Price_Variations extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = ':attribute is wrongly setup.';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = ['duration', 'duration_unit']; // phpcs:ignore
	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value being checked.
	 */
	public function check($value): bool {

		if (is_string($value)) {
			$value = maybe_unserialize($value);
		}

		if (! is_array($value)) {
			return false;
		}

		foreach ($value as $price_variation) {

			/**
			 * Validation Duration
			 */
			$duration = wu_get_isset($price_variation, 'duration', false);

			if (! is_numeric($duration) || (int) $duration <= 0) {
				return false;
			}

			/**
			 * Validation Unit
			 */
			$unit = wu_get_isset($price_variation, 'duration_unit', false);

			$allowed_units = [
				'day',
				'week',
				'month',
				'year',
			];

			if (! in_array($unit, $allowed_units, true)) {
				return false;
			}

			/**
			 * Check if it is the same as the main duration
			 */
			if ($this->parameter('duration') == $duration && $this->parameter('duration_unit') === $unit) {
				$this->message = __('This product cannot have a price variation for the same duration and duration unit values as the product itself.', 'ultimate-multisite');

				return false;
			}

			/**
			 * Validation Amount
			 */
			$amount = wu_get_isset($price_variation, 'amount', false);

			if ($amount) {
				$amount = wu_to_float($amount);
			}

			if (! is_numeric($amount)) {
				return false;
			}
		}

		return true;
	}
}
