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

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Products extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'products';
	}

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 */
	public function is_required(): bool {

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

		return __('Product', 'multisite-ultimate');
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

		return __('Hidden field used to pre-select products. This is useful when you have a signup page for specific offering/bundles and do not want your customers to be able to choose plans and products manually.', 'multisite-ultimate');
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

		return __('Hidden field used to pre-select products. This is useful when you have a signup page for specific offering/bundles and do not want your customers to be able to choose plans and products manually.', 'multisite-ultimate');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu dashicons-wu-package';
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
			'',
		];
	}

	/**
	 * List of keys of the default fields we want to display on the builder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function default_fields() {

		return [];
	}

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return [
			'name' => __('Pre-selected Products', 'multisite-ultimate'),
			'id'   => 'products',
		];
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'products' => [
				'type'        => 'model',
				'title'       => __('Products', 'multisite-ultimate'),
				'placeholder' => __('Products', 'multisite-ultimate'),
				'desc'        => __('Use this field to pre-select products. This is useful when you have a signup page for specific offering/bundles and do not want your customers to be able to choose plans and other products manually.', 'multisite-ultimate'),
				'tooltip'     => '',
				'html_attr'   => [
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 10,
				],
			],
		];
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

		$checkout_fields = [];

		$products = explode(',', (string) $attributes['products']);

		foreach ($products as $product_id) {
			$checkout_fields[ "products[{$product_id}]" ] = [
				'type'      => 'hidden',
				'value'     => $product_id,
				'html_attr' => [
					'v-bind:name' => "'products[]'",
				],
			];
		}

		$this->insert_products_in_form($products);

		return $checkout_fields;
	}

	/**
	 * Inserts the products in the form.
	 *
	 * @param array $products An array of product IDs.
	 * @return void
	 */
	protected function insert_products_in_form(array $products): void {

		static $added = false;

		if ($added) {
			return;
		}

		$added = true;

		$script = "wp.hooks.addFilter('wu_before_form_init', 'nextpress/wp-ultimo', function(data) {
			if (typeof data !== 'undefined' && Array.isArray(data.products)) {
				data.products.push(...%s);
				data.products = data.products.map((value) => parseInt(value) || value);
				data.products = [...new Set(data.products)];
			}
			return data;
		});";

		if (did_action('wu-checkout')) {
			wp_add_inline_script('wu-checkout', sprintf($script, wp_json_encode($products)), 'before');

			return;
		}

		add_action(
			'wp_enqueue_scripts',
			function () use ($script, $products) {

				wp_add_inline_script('wu-checkout', sprintf($script, wp_json_encode($products)), 'before');
			},
			11
		);
	}
}
