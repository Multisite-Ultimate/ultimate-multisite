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
class Unique extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = ':attribute :value has been used';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = ['model', 'column', 'self_id']; // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value being checked.
	 */
	public function check($value): bool {

		if (empty($value)) {
			return true;
		}
		$this->requireParameters(
			[
				'model',
				'column',
			]
		);

		$column  = $this->parameter('column');
		$model   = $this->parameter('model');
		$self_id = $this->parameter('self_id');

		switch ($model) {
			case '\WP_User':
				$callback = 'get_user_by';
				break;
			default:
				$callback = [$model, 'get_by'];
				break;
		}

		// do query
		$existing = call_user_func($callback, $column, $value);

		$user_models = [
			'\WP_User',
			\WP_Ultimo\Models\Customer::class,
		];

		/*
		* Customize the error message for the customer.
		*/
		if (in_array($model, $user_models, true)) {
			$this->message = __('A customer with the same email address or username already exists.', 'multisite-ultimate');
		}

		if ( ! $existing) {
			return true;
		}

		if ( $existing instanceof \WP_User) {
			$id = $existing->ID;
		} else {
			$id = method_exists($existing, 'get_id') ? $existing->get_id() : $existing->id;
		}

		return absint($id) === absint($self_id);
	}
}
