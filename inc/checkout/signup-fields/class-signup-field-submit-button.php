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
class Signup_Field_Submit_Button extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'submit_button';
	}

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

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

		return __('Submit Button', 'multisite-ultimate');
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

		return __('Adds a submit button. This is required to finalize single-step checkout forms or to navigate to the next step on multi-step checkout forms.', 'multisite-ultimate');
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

		return __('Adds a submit button. This is required to finalize single-step checkout forms or to navigate to the next step on multi-step checkout forms.', 'multisite-ultimate');
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

		return 'dashicons-wu-zap';
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
			'enable_go_back_button' => false,
			'back_button_label'     => __('&larr; Go Back', 'multisite-ultimate'),
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
			'id',
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

		return [];
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [
			'enable_go_back_button' => [
				'type'      => 'toggle',
				'title'     => __('Add "Go Back" button', 'multisite-ultimate'),
				'desc'      => __('Enable this option to add a "Go Back" button. Useful for multi-step checkout forms.', 'multisite-ultimate'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => [
					'v-model' => 'enable_go_back_button',
				],
			],
			'back_button_label'     => [
				'type'              => 'text',
				'title'             => __('"Go Back" Button Label', 'multisite-ultimate'),
				'desc'              => __('Value to be used as the "Go Back" label.', 'multisite-ultimate'),
				'placeholder'       => __('e.g. &larr; Go Back', 'multisite-ultimate'),
				'value'             => __('&larr; Go Back', 'multisite-ultimate'),
				'wrapper_html_attr' => [
					'v-cloak' => '1',
					'v-show'  => 'enable_go_back_button',
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

		$uniqid = uniqid();

		$fields = [];

		$fields[ $attributes['id'] . '_errors' ] = [
			'type'              => 'html',
			'wrapper_classes'   => 'wu_submit_button_errors wu-clear-both',
			'content'           => '<span v-cloak class="wu-block wu-bg-red-100 wu-p-2 wu-mb-4" v-html="get_errors().join(' . esc_js(wp_json_encode('<br>')) . ')"></span>',
			'wrapper_html_attr' => [
				'v-if' => 'get_errors()',
			],
		];

		$fields[ $attributes['id'] . '_group' ] = [
			'type'            => 'group',
			'raw'             => true,
			'default'         => [],
			'wrapper_classes' => '',
			'fields'          => [],
		];

		$button_wrapper_classes = 'wu_submit_button';

		if ($attributes['enable_go_back_button']) {
			$steps = \WP_Ultimo\Checkout\Checkout::get_instance()->steps;

			$is_first_step = isset($steps[0]) && $steps[0]['id'] === $attributes['step'];

			if ( ! $is_first_step) {
				$fields[ $attributes['id'] . '_group' ]['fields'][ $attributes['id'] . '_go_back' ] = [
					'type'            => 'html',
					'wrapper_classes' => 'md:wu-w-1/2 wu-box-border wu-float-left wu--mt-4',
					'id'              => $attributes['id'] . '_go_back',
					'content'         => sprintf('<a href="#" class="button wu-go-back" v-on:click.prevent="go_back()">%s</a>', esc_html($attributes['back_button_label'])),
				];

				$button_wrapper_classes .= ' md:wu-w-1/2 wu-box-border wu-float-left wu-text-right';
			}
		}

		$fields[ $attributes['id'] . '_group' ]['fields'][ $attributes['id'] ] = [
			'type'            => 'submit',
			'wrapper_classes' => trim($button_wrapper_classes . ' ' . wu_get_isset($attributes, 'wrapper_element_classes', '')),
			'classes'         => trim('button button-primary btn-primary ' . wu_get_isset($attributes, 'element_classes', '')),
			'id'              => $attributes['id'],
			'name'            => $attributes['name'],
		];

		if ($attributes['enable_go_back_button']) {
			$fields[ $attributes['id'] . '_clear' ] = [
				'type' => 'clear',
			];
		}

		return $fields;
	}
}
