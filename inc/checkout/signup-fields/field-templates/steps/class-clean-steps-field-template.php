<?php
/**
 * Template Selection Clean
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Steps;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Base_Field_Template;

/**
 * Template Selection Clean
 *
 * @since 2.0.0
 */
class Clean_Steps_Field_Template extends Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'steps/clean';

	/**
	 * The render type for the template.
	 *
	 * Field templates can have two different render types, ajax and dynamic.
	 * If ajax is selected, when we detect a change in the billing period and other
	 * sensitive info, an ajax request is made to fetch the new pricing table HTML
	 * markup.
	 *
	 * If dynamic is selected, nothing is done as the template can handle
	 * reactive updates natively (using Vue.js)
	 *
	 * In terms of performance, dynamic is preferred, but ajax should
	 * work just fine.
	 *
	 * @since 2.0.0
	 * @return string Either ajax or dynamic
	 */
	public function get_render_type(): string {

		return 'dynamic';
	}

	/**
	 * The title of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Clean', 'multisite-ultimate');
	}

	/**
	 * The description of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('A simple layout with minimal styling, just enough to make it usable out-of-the-box.', 'multisite-ultimate');
	}

	/**
	 * The preview of the field template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview(): string {

		return wu_get_asset('checkout-forms/clean-steps.webp');
	}

	/**
	 * The content of the template.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The field template attributes.
	 * @return void
	 */
	public function output($attributes): void {

		wu_get_template('checkout/templates/steps/clean', $attributes);
	}
}
