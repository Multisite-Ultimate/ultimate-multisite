<?php
/**
 * Base Field Template
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Pricing_Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Base_Field_Template;

/**
 * Base Field Template
 *
 * @since 2.0.0
 */
class List_Pricing_Table_Field_Template extends Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'pricing-table/list';

	/**
	 * The title of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Simple List', 'multisite-ultimate');
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

		return __('Simple stylized list with price, recurrence, and the plan description.', 'multisite-ultimate');
	}

	/**
	 * The preview image of the field template.
	 *
	 * The URL of the image preview.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview(): string {

		return wu_get_asset('checkout-forms/list-pricing-table.webp');
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

		wu_get_template('checkout/templates/pricing-table/list', $attributes);
	}
}
