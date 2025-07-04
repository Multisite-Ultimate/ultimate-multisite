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
class Signup_Field_Site_Title extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'site_title';
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
	 * Defines if this field/element is related to site creation or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_site_field() {

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

		return __('Site Title', 'multisite-ultimate');
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

		return __('Adds a Site Title field. This value is used to set the site title for the site being created.', 'multisite-ultimate');
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

		return __('Adds a Site Title field. This value is used to set the site title for the site being created.', 'multisite-ultimate');
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

		return 'dashicons-wu-type';
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
			'auto_generate_site_title' => false,
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
			'placeholder',
			'tooltip',
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
			'id'       => 'site_title',
			'required' => true,
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
			'auto_generate_site_title' => [
				'type'      => 'toggle',
				'title'     => __('Auto-generate?', 'multisite-ultimate'),
				'desc'      => __('Check this option to auto-generate this field based on the username of the customer.', 'multisite-ultimate'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => [
					'v-model' => 'auto_generate_site_title',
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
		/*
		 * If we should auto-generate, add as hidden.
		 */
		if (isset($attributes['auto_generate_site_title']) && $attributes['auto_generate_site_title']) {
			return [
				'auto_generate_site_title' => [
					'type'  => 'hidden',
					'id'    => 'auto_generate_site_title',
					'value' => 'username',
				],
				'site_title'               => [
					'type'      => 'hidden',
					'id'        => 'site_title',
					'html_attr' => [
						'v-bind:value' => 'username',
					],
				],
			];
		}

		return [
			'site_title' => [
				'type'              => 'text',
				'id'                => 'site_title',
				'required'          => true,
				'name'              => $attributes['name'],
				'placeholder'       => $attributes['placeholder'],
				'tooltip'           => $attributes['tooltip'],
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'value'             => $this->get_value(),
				'wrapper_html_attr' => [
					'style' => $this->calculate_style_attr(),
				],
			],
		];
	}
}
