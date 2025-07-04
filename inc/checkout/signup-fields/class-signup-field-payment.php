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
use WP_Ultimo\Managers\Gateway_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Payment extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'payment';
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

		return __('Payment', 'multisite-ultimate');
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

		return __('Adds the payment options and the additional fields required to complete a purchase (e.g. credit card field).', 'multisite-ultimate');
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

		return __('Adds the payment options and the additional fields required to complete a purchase (e.g. credit card field).', 'multisite-ultimate');
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

		return 'dashicons-wu-credit-card2';
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
			'id' => 'payment',
		];
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return [];
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

		$fields = [
			'payment_template' => [
				'type'    => 'text',
				'id'      => 'payment_template',
				'name'    => '',
				'classes' => 'wu-hidden',
			],
			'payment'          => [
				'type'              => 'payment-methods',
				'id'                => 'payment',
				'name'              => $attributes['name'],
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'wrapper_html_attr' => [
					'style' => $this->calculate_style_attr(),
				],
			],
		];

		/*
		 * Checks if we need to add the
		 * auto renew field.
		 */
		if ( ! wu_get_setting('force_auto_renew', 1)) {
			$auto_renewable_gateways = Gateway_Manager::get_instance()->get_auto_renewable_gateways();

			$fields['auto_renew'] = [
				'type'              => 'toggle',
				'id'                => 'auto_renew',
				'name'              => __('Auto-renew', 'multisite-ultimate'),
				'tooltip'           => '',
				'value'             => '1',
				'html_attr'         => [
					'v-model'     => 'auto_renew',
					'true-value'  => '1',
					'false-value' => '0',
				],
				'wrapper_html_attr' => [
					'v-cloak' => 1,
					'v-show'  => sprintf('%s.includes(gateway) && order.should_collect_payment && order.has_recurring', wp_json_encode($auto_renewable_gateways)),
				],
			];
		}

		return $fields;
	}
}
