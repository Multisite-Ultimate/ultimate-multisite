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
class Signup_Field_Email extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'email';
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
	 * Is this a user-related field?
	 *
	 * If this is set to true, this field will be hidden
	 * when the user is already logged in.
	 *
	 * @since 2.0.0
	 */
	public function is_user_field(): bool {

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

		return __('Email', 'multisite-ultimate');
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

		return __('Adds a email address field. This email address will be used to create the WordPress user.', 'multisite-ultimate');
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

		return __('Adds a email address field. This email address will be used to create the WordPress user.', 'multisite-ultimate');
	}
	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu-at-sign';
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
			'display_notices' => true,
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
			'id'       => 'email_address',
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
			'display_notices' => [
				'type'      => 'toggle',
				'title'     => __('Display Notices', 'multisite-ultimate'),
				'desc'      => __('When the customer is already logged in, a box with the customer\'s username and a link to logout is displayed instead of the email field. Disable this option if you do not want that box to show up.', 'multisite-ultimate'),
				'tooltip'   => '',
				'value'     => 1,
				'html_attr' => [
					'v-model' => 'display_notices',
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

		if (is_user_logged_in()) {
			if ($attributes['display_notices']) {
				$checkout_fields['login_note'] = [
					'type'              => 'note',
					'title'             => __('Not you?', 'multisite-ultimate'),
					'desc'              => [$this, 'render_not_you_customer_message'],
					'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
					'wrapper_html_attr' => [
						'style' => $this->calculate_style_attr(),
					],
				];
			}
		} else {
			if ($attributes['display_notices']) {
				$checkout_fields['login_note'] = [
					'type'              => 'note',
					'title'             => __('Existing customer?', 'multisite-ultimate'),
					'desc'              => [$this, 'render_existing_customer_message'],
					'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
					'wrapper_html_attr' => [
						'style' => $this->calculate_style_attr(),
					],
				];
			}

			$checkout_fields['email_address'] = [
				'type'              => 'text',
				'id'                => 'email_address',
				'name'              => $attributes['name'],
				'placeholder'       => $attributes['placeholder'],
				'tooltip'           => $attributes['tooltip'],
				'value'             => $this->get_value(),
				'required'          => true,
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'wrapper_html_attr' => [
					'style' => $this->calculate_style_attr(),
				],
			];
		}

		return $checkout_fields;
	}

	/**
	 * Renders the login message for users that are not logged in.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_existing_customer_message() {

		$login_url = wp_login_url(add_query_arg('logged', '1'));

		ob_start(); ?>

		<div class="wu-p-4 wu-bg-yellow-200">

			<?php // phpcs:disable

			// translators: %s is the login URL.
			printf(__('<a href="%s">Log in</a> to renew or change an existing membership.', 'multisite-ultimate'), $login_url);

			?>

		</div>

		<?php // phpcs:enable

		return ob_get_clean();
	}

	/**
	 * Renders the login message for users that are not logged in.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_not_you_customer_message() {

		$login_url = wp_login_url(add_query_arg('logged', '1'), true);

		ob_start();

		?>

		<p class="wu-p-4 wu-bg-yellow-200">
		<?php

		// translators: 1$s is the display name of the user currently logged in.
		printf(wp_kses_post(__('Not %1$s? <a href="%2$s">Log in</a> using your account.', 'multisite-ultimate')), esc_html(wp_get_current_user()->display_name), esc_attr($login_url));

		?>
		</p>

		<?php

		return ob_get_clean();
	}
}
