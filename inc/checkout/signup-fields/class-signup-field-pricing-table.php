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
class Signup_Field_Pricing_Table extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'pricing_table';
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

		return __('Pricing Table', 'multisite-ultimate');
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

		return __('Adds a pricing table section that customers can use to choose a plan to subscribe to.', 'multisite-ultimate');
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

		return __('Adds a pricing table section that customers can use to choose a plan to subscribe to.', 'multisite-ultimate');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu dashicons-wu-columns';
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
			'pricing_table_products'               => implode(',', array_keys(wu_get_plans_as_options())),
			'pricing_table_template'               => 'list',
			'force_different_durations'            => false,
			'hide_pricing_table_when_pre_selected' => false,
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
			// 'name',
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
			'id'       => 'pricing_table',
			'name'     => __('Plan Selection', 'multisite-ultimate'),
			'required' => true,
		];
	}

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pricing_table_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('pricing_table');

		return $available_templates;
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = [];

		$editor_fields['pricing_table_products'] = [
			'type'        => 'model',
			'title'       => __('Products', 'multisite-ultimate'),
			'placeholder' => __('e.g. Premium', 'multisite-ultimate'),
			'desc'        => __('Be sure to add the products in the order you want them to show up.', 'multisite-ultimate'),
			'tooltip'     => '',
			'order'       => 20,
			'html_attr'   => [
				'data-model'        => 'product',
				'data-value-field'  => 'id',
				'data-label-field'  => 'name',
				'data-search-field' => 'name',
				'data-include'      => implode(',', array_keys(wu_get_plans_as_options())),
				'data-max-items'    => 999,
			],
		];

		$editor_fields['force_different_durations'] = [
			'type'      => 'toggle',
			'title'     => __('Force Different Durations', 'multisite-ultimate'),
			'desc'      => __('Check this option to force the display of plans with different recurring durations.', 'multisite-ultimate'),
			'tooltip'   => '',
			'value'     => 0,
			'order'     => 22,
			'html_attr' => [
				'v-model' => 'force_different_durations',
			],
		];

		$editor_fields['hide_pricing_table_when_pre_selected'] = [
			'type'      => 'toggle',
			'title'     => __('Hide when Pre-Selected', 'multisite-ultimate'),
			'desc'      => __('Prevent customers from seeing this field when a plan was already selected via the URL.', 'multisite-ultimate'),
			'tooltip'   => __('If the pricing table field is the only field in the current step, the step will be skipped.', 'multisite-ultimate'),
			'value'     => 0,
			'order'     => 24,
			'html_attr' => [
				'v-model' => 'hide_pricing_table_when_pre_selected',
			],
		];

		$editor_fields['pricing_table_template'] = [
			'type'   => 'group',
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('pricing_table'),
			'order'  => 26,
			'fields' => [
				'pricing_table_template' => [
					'type'            => 'select',
					'title'           => __('Pricing Table Template', 'multisite-ultimate'),
					'placeholder'     => __('Select your Template', 'multisite-ultimate'),
					'options'         => [$this, 'get_pricing_table_templates'],
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => [
						'v-model' => 'pricing_table_template',
					],
				],
			],
		];

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_2'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized pricing table templates?<br><a target="_blank" class="wu-no-underline" href="https://github.com/superdav42/wp-multisite-waas/wiki/Customize-Checkout-Flow">See how you can do that here</a>.', 'multisite-ultimate')),
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

		if ('legacy' === wu_get_isset($attributes, 'pricing_table_template')) {
			wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), ['dashicons'], wu_get_version());

			wp_add_inline_style('legacy-shortcodes', \WP_Ultimo\Checkout\Legacy_Checkout::get_instance()->get_legacy_dynamic_styles());
		}

		$product_list = explode(',', (string) $attributes['pricing_table_products']);

		$products = array_map('wu_get_product', $product_list);

		/**
		 * Clear the product list out of invalid items and inactive products.
		 */
		$products = array_filter($products, fn($item) => $item && $item->is_active());

		/**
		 * Hide when pre-selected.
		 */
		if (wu_should_hide_form_field($attributes)) {
			return [];
		}

		$template_attributes = [
			'products'                  => $products,
			'name'                      => $attributes['name'],
			'force_different_durations' => $attributes['force_different_durations'],
			'classes'                   => wu_get_isset($attributes, 'element_classes', ''),
		];

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('pricing_table', $attributes['pricing_table_template']);

		$content = $template_class ? $template_class->render_container($template_attributes) : __('Template does not exist.', 'multisite-ultimate');

		$checkout_fields = [];

		$checkout_fields[ $attributes['id'] ] = [
			'type'              => 'note',
			'id'                => $attributes['id'],
			'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
			'classes'           => wu_get_isset($attributes, 'element_classes', ''),
			'desc'              => $content,
			'wrapper_html_attr' => [
				'style' => $this->calculate_style_attr(),
			],
		];

		return $checkout_fields;
	}
}
