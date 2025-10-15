<?php
/**
 * Used to hack legacy signups
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Replacement of the old WU_Signup class for templates.
 *
 * @since 2.0.0
 */
class Mocked_Signup {
	/**
	 * @var string
	 */
	public $step;

	/**
	 * @var array
	 */
	public $steps;

	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $step Current step.
	 * @param array  $steps List of all steps.
	 */
	public function __construct($step, $steps) {
		$this->step  = $step;
		$this->steps = $steps;
	}

	/**
	 * Get the value of steps.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_steps() {

		return $this->steps;
	}

	/**
	 * Deprecated: returns the prev step link.
	 *
	 * @since 2.0.0
	 */
	public function get_prev_step_link(): string {

		return '';
	}
}
