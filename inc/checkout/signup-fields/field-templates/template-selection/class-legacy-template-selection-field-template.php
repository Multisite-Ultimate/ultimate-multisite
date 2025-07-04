<?php
/**
 * Legacy Field Template Selector
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Template_Selection;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Checkout\Signup_Fields\Field_Templates\Base_Field_Template;

/**
 * Legacy Field Template Selector
 *
 * @since 2.0.0
 */
class Legacy_Template_Selection_Field_Template extends Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'template-selection/legacy';

	/**
	 * Get render type for the template.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_render_type(): string {

		return 'ajax';
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

		return __('Legacy', 'multisite-ultimate');
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

		return __('Implementation of the layout that shipped with WP Ultimo < 1.10.X.', 'multisite-ultimate');
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

		return wu_get_asset('checkout-forms/legacy-template-selection.webp');
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

		wu_get_template('checkout/templates/template-selection/legacy', $attributes);
	}
}
