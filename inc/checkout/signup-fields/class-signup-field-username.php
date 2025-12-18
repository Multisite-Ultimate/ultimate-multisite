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
class Signup_Field_Username extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'username';
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
	 * Is this a user-related field?
	 *
	 * If this is set to true, this field will be hidden
	 * when the user is already logged in.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_user_field() {

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

		return __('Username', 'ultimate-multisite');
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

		return __('Adds an username field. This username will be used to create the WordPress user.', 'ultimate-multisite');
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

		return __('Adds an username field. This username will be used to create the WordPress user.', 'ultimate-multisite');
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

		return 'dashicons-wu-user1';
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
			'auto_generate_username' => false,
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
			'id'       => 'username',
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
			'auto_generate_username' => [
				'type'      => 'toggle',
				'title'     => __('Auto-generate', 'ultimate-multisite'),
				'desc'      => __('Check this option to auto-generate this field based on the email address of the customer.', 'ultimate-multisite'),
				'tooltip'   => '',
				'value'     => 0,
				'html_attr' => [
					'v-model' => 'auto_generate_username',
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
		 * Logged in user, bail.
		 */
		if (is_user_logged_in()) {
			return [];
		}

		if (isset($attributes['auto_generate_username']) && $attributes['auto_generate_username']) {
			return [
				'auto_generate_username' => [
					'type'  => 'hidden',
					'id'    => 'auto_generate_username',
					'value' => 'email',
				],
				'username'               => [
					'type'  => 'hidden',
					'id'    => 'username',
					'value' => uniqid(),
				],
			];
		}

		return [
			'username'                     => [
				'type'              => 'text',
				'id'                => 'username',
				'name'              => $attributes['name'],
				'placeholder'       => $attributes['placeholder'],
				'tooltip'           => $attributes['tooltip'],
				'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
				'classes'           => wu_get_isset($attributes, 'element_classes', ''),
				'required'          => true,
				'value'             => $this->get_value(),
				'html_attr'         => [
					'v-model'         => 'username',
					'v-init:username' => "'{$this->get_value()}'",
					'autocomplete'    => 'username',
					'@blur'           => "check_user_exists_debounced('username', username)",
				],
				'wrapper_html_attr' => [
					'style' => $this->calculate_style_attr(),
				],
			],
			'username_inline_login_prompt' => [
				'type'              => 'html',
				'id'                => 'username_inline_login_prompt',
				'content'           => [$this, 'render_inline_login_prompt'],
				'wrapper_classes'   => '',
				'wrapper_html_attr' => [
					'v-if'    => "show_login_prompt && login_prompt_field === 'username'",
					'v-cloak' => true,
				],
			],
		];
	}

	/**
	 * Renders the inline login prompt HTML.
	 *
	 * @since 2.0.20
	 * @return string
	 */
	public function render_inline_login_prompt() {

		ob_start();

		?>
		<div id="wu-inline-login-prompt" class="wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded wu-p-4 wu-mt-2 wu-mb-4">
			<div class="wu-flex wu-items-center wu-justify-between wu-mb-3">
				<p class="wu-m-0 wu-font-semibold wu-text-blue-900 wu-text-sm">
					<?php esc_html_e('Already have an account?', 'ultimate-multisite'); ?>
				</p>
				<button
					type="button"
					id="wu-dismiss-login-prompt"
					class="wu-text-gray-500 hover:wu-text-gray-700 wu-text-2xl wu-leading-none wu-cursor-pointer wu-border-0 wu-bg-transparent wu-p-0"
					aria-label="<?php esc_attr_e('Close', 'ultimate-multisite'); ?>"
				>
					&times;
				</button>
			</div>

			<div class="wu-mb-3">
				<label for="wu-inline-login-password" class="wu-block wu-text-sm wu-font-medium wu-text-gray-700 wu-mb-1">
					<?php esc_html_e('Password', 'ultimate-multisite'); ?>
				</label>
				<input
					type="password"
					id="wu-inline-login-password"
					class="form-control wu-w-full"
					autocomplete="current-password"
					placeholder="<?php esc_attr_e('Enter your password', 'ultimate-multisite'); ?>"
				/>
			</div>

			<div id="wu-login-error" class="wu-bg-red-100 wu-text-red-800 wu-p-3 wu-rounded wu-text-sm wu-mb-3" style="display: none;">
			</div>

			<div class="wu-flex wu-flex-wrap wu-items-center wu-justify-between wu-gap-2">
				<a
					href="<?php echo esc_url(wp_lostpassword_url(wu_get_current_url())); ?>"
					class="wu-text-sm wu-text-blue-600 hover:wu-text-blue-800 wu-no-underline"
					target="_blank"
				>
					<?php esc_html_e('Forgot password?', 'ultimate-multisite'); ?>
				</a>

				<button
					type="button"
					id="wu-inline-login-submit"
					class="wu-bg-blue-600 wu-text-white wu-px-4 wu-py-2 wu-rounded hover:wu-bg-blue-700 disabled:wu-opacity-50 disabled:wu-cursor-not-allowed wu-border-0 wu-text-sm wu-font-medium wu-cursor-pointer"
				>
					<?php esc_html_e('Sign in', 'ultimate-multisite'); ?>
				</button>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}
