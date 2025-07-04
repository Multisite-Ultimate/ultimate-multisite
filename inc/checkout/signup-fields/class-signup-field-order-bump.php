<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields;

use WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field;
use WP_Ultimo\Managers\Field_Templates_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Order_Bump extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'order_bump';
	}

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return false;
	}

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Order Bump', 'multisite-ultimate');
	}

	/**
	 * Returns the description of the field/element.
	 *
	 * This is used as the title attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Adds a product offer that the customer can click to add to the current cart.', 'multisite-ultimate');
	}

	/**
	 * Returns the tooltip of the field/element.
	 *
	 * This is used as the tooltip attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tooltip() {

		return __('Adds a product offer that the customer can click to add to the current cart.', 'multisite-ultimate');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-wu-gift';
	}

	/**
	 * Returns the default values for the field-elements.
	 *
	 * This is passed through a wp_parse_args before we send the values
	 * to the method that returns the actual fields for the checkout form.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return [
			'order_bump_template'         => 'simple',
			'display_product_description' => 0,
		];
	}

	/**
	 * List of keys of the default fields we want to display on the builder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function default_fields() {

		return [
			// 'id',
			'name',
		];
	}

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return [
			'order_bump_template' => 'simple',
		];
	}

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('order_bump');

		return $available_templates;
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = [
			'product'                     => [
				'type'        => 'model',
				'title'       => __('Product', 'multisite-ultimate'),
				'placeholder' => __('e.g. Premium', 'multisite-ultimate'),
				'desc'        => __('Select the product that will be presented to the customer as an add-on option.', 'multisite-ultimate'),
				'tooltip'     => '',
				'order'       => 12,
				'html_attr'   => [
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
				],
			],
			'display_product_description' => [
				'order' => 13,
				'type'  => 'toggle',
				'title' => __('Display Product Description', 'multisite-ultimate'),
				'desc'  => __('Toggle to display the product description as well, if one is available.', 'multisite-ultimate'),
				'value' => 0,
			],
			'display_product_image'       => [
				'order' => 14,
				'type'  => 'toggle',
				'title' => __('Display Product Image', 'multisite-ultimate'),
				'desc'  => __('Toggle to display the product image as well, if one is available.', 'multisite-ultimate'),
				'value' => 1,
			],
		];

		// $editor_fields['order_bump_template'] = array(
		// 'type'   => 'group',
		// 'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('order_bump'),
		// 'order'  => 98,
		// 'fields' => array(
		// 'order_bump_template' => array(
		// 'type'            => 'select',
		// 'title'           => __('Layout', 'multisite-ultimate'),
		// 'placeholder'     => __('Select your Layout', 'multisite-ultimate'),
		// 'options'         => array($this, 'get_templates'),
		// 'wrapper_classes' => 'wu-flex-grow',
		// 'html_attr'       => array(
		// 'v-model' => 'order_bump_template',
		// ),
		// ),
		// ),
		// );

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_order_bump'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized order bump templates?<br><a target="_blank" class="wu-no-underline" href="https://github.com/superdav42/wp-multisite-waas/wiki/Customize-Checkout-Flow">See how you can do that here</a>.', 'multisite-ultimate')),
		// );

		return $editor_fields;
	}

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	public function to_fields_array($attributes) {

		$product_id = $attributes['product'];

		$product = is_numeric($product_id) ? wu_get_product($product_id) : wu_get_product_by_slug($product_id);

		if ( ! $product) {
			return [];
		}

		$attributes['product'] = $product;

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('order_bump', $attributes['order_bump_template']);

		$content = $template_class ? $template_class->render_container($attributes) : __('Template does not exist.', 'multisite-ultimate');

		return [
			$attributes['id'] => [
				'type'            => 'note',
				'desc'            => $content,
				'wrapper_classes' => $attributes['element_classes'],
			],
		];
	}
}
