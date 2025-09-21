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
class Signup_Field_Order_Summary extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'order_summary';
	}

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 */
	public function is_required(): bool {

		return true;
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

		return __('Order Summary', 'ultimate-multisite');
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

		return __('Adds a summary table with prices, key subscription dates, discounts, and taxes.', 'ultimate-multisite');
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

		return __('Adds a summary table with prices, key subscription dates, discounts, and taxes.', 'ultimate-multisite');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu-dollar-sign';
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
			'order_summary_template' => 'clean',
			'table_columns'          => 'simple',
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
			'id' => 'order_summary',
		];
	}

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('order_summary');

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

		$editor_fields['table_columns'] = [
			'type'    => 'select',
			'title'   => __('Table Columns', 'ultimate-multisite'),
			'desc'    => __('"Simplified" will condense all discount and tax info into separate rows to keep the table with only two columns. "Display All" adds a discounts and taxes column to each product row.', 'ultimate-multisite'),
			'options' => [
				'simple' => __('Simplified', 'ultimate-multisite'),
				'full'   => __('Display All', 'ultimate-multisite'),
			],
		];

		$editor_fields['order_summary_template'] = [
			'type'   => 'group',
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('order_summary'),
			'fields' => [
				'order_summary_template' => [
					'type'            => 'select',
					'title'           => __('Layout', 'ultimate-multisite'),
					'placeholder'     => __('Select your Layout', 'ultimate-multisite'),
					'options'         => [$this, 'get_templates'],
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => [
						'v-model' => 'order_summary_template',
					],
				],
			],
		];

		// phpcs:disable
		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_order_summary'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized order summary templates?<br><a target="_blank" class="wu-no-underline" href="https://github.com/superdav42/wp-multisite-waas/wiki/Customize-Checkout-Flow">See how you can do that here</a>.', 'ultimate-multisite')),
		// );
		// phpcs:enable

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

		$checkout_fields = [];

		/*
		 * Backwards compatibility with previous betas
		 */
		if ('simple' === $attributes['order_summary_template']) {
			$attributes['order_summary_template'] = 'clean';
		}

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('order_summary', $attributes['order_summary_template']);

		$desc = function() use($attributes, $template_class) {
			if ($template_class) {
				$template_class->render_container($attributes);
			} else {
				esc_html_e('Template does not exist.', 'ultimate-multisite');
			}
		};

		$checkout_fields[ $attributes['id'] ] = [
			'type'              => 'note',
			'desc'              => $desc,
			'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
			'classes'           => wu_get_isset($attributes, 'element_classes', ''),
			'wrapper_html_attr' => [
				'style' => $this->calculate_style_attr(),
			],
		];

		return $checkout_fields;
	}
}
