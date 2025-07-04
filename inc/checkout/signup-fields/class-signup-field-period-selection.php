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
class Signup_Field_Period_Selection extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'period_selection';
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

		return __('Period Select', 'multisite-ultimate');
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

		return __('Adds a period selector, that allows customers to switch between different billing periods.', 'multisite-ultimate');
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

		return __('Adds a period selector, that allows customers to switch between different billing periods.', 'multisite-ultimate');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu dashicons-wu-toggle-right';
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
			'period_selection_template' => 'clean',
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
			'id'       => 'period_selection',
			'name'     => __('Plan Duration Switch', 'multisite-ultimate'),
			'required' => true,
		];
	}

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_template_options() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('period_selection');

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

		$editor_fields['period_selection_template'] = [
			'type'   => 'group',
			'order'  => 98.4,
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('period_selection'),
			'fields' => [
				'period_selection_template' => [
					'type'            => 'select',
					'title'           => __('Period Selector Template', 'multisite-ultimate'),
					'placeholder'     => __('Select your Template', 'multisite-ultimate'),
					'options'         => [$this, 'get_template_options'],
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => [
						'v-model' => 'period_selection_template',
					],
				],
			],
		];

		$editor_fields['period_options_header'] = [
			'type'  => 'small-header',
			'title' => __('Options', 'multisite-ultimate'),
			'desc'  => __('Add different options below. These need to match your product price variations.', 'multisite-ultimate'),
			'order' => 90,
		];

		$editor_fields['period_options_empty'] = [
			'type'              => 'note',
			'desc'              => __('Add the first option using the button below.', 'multisite-ultimate'),
			'classes'           => 'wu-text-gray-600 wu-text-xs wu-text-center wu-w-full',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'order'             => 90.5,
			'wrapper_html_attr' => [
				'v-if'    => 'period_options.length === 0',
				'v-cloak' => '1',
			],
		];

		$editor_fields['period_options'] = [
			'type'              => 'group',
			'tooltip'           => '',
			'order'             => 91,
			'wrapper_classes'   => 'wu-relative wu-bg-gray-100 wu-pb-2',
			'wrapper_html_attr' => [
				'v-if'    => 'period_options.length',
				'v-for'   => '(period_option, index) in period_options',
				'v-cloak' => '1',
			],
			'fields'            => [
				'period_options_remove'        => [
					'type'            => 'note',
					'desc'            => sprintf('<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600 wu-mt-2 wu-mr-2" href="#" @click.prevent="() => period_options.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>', __('Remove', 'multisite-ultimate')),
					'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
				],
				'period_options_duration'      => [
					'type'            => 'number',
					'title'           => __('Duration', 'multisite-ultimate'),
					'placeholder'     => '',
					'wrapper_classes' => 'wu-w-2/12',
					'min'             => 1,
					'html_attr'       => [
						'v-model'     => 'period_option.duration',
						'steps'       => 1,
						'v-bind:name' => '"period_options[" + index + "][duration]"',
					],
				],
				'period_options_duration_unit' => [
					'type'            => 'select',
					'title'           => '&nbsp',
					'placeholder'     => '',
					'wrapper_classes' => 'wu-w-5/12 wu-mx-2',
					'html_attr'       => [
						'v-model'     => 'period_option.duration_unit',
						'v-bind:name' => '"period_options[" + index + "][duration_unit]"',
					],
					'options'         => [
						'day'   => __('Days', 'multisite-ultimate'),
						'week'  => __('Weeks', 'multisite-ultimate'),
						'month' => __('Months', 'multisite-ultimate'),
						'year'  => __('Years', 'multisite-ultimate'),
					],
				],
				'period_options_label'         => [
					'type'            => 'text',
					'title'           => __('Label', 'multisite-ultimate'),
					'placeholder'     => __('e.g. Monthly', 'multisite-ultimate'),
					'wrapper_classes' => 'wu-w-5/12',
					'html_attr'       => [
						'v-model'     => 'period_option.label',
						'v-bind:name' => '"period_options[" + index + "][label]"',
					],
				],
			],
		];

		$editor_fields['repeat'] = [
			'order'             => 92,
			'type'              => 'submit',
			'title'             => __('+ Add option', 'multisite-ultimate'),
			'classes'           => 'wu-uppercase wu-text-2xs wu-text-blue-700 wu-border-none wu-bg-transparent wu-font-bold wu-text-right wu-w-full wu-cursor-pointer',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'wrapper_html_attr' => [
				'v-cloak' => '1',
			],
			'html_attr'         => [
				'v-on:click.prevent' => '() => period_options.push({
					duration: 1,
					duration_unit: "month",
					label: "",
				})',
			],
		];

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

		if ('legacy' === wu_get_isset($attributes, 'period_selection_template')) {
			wp_register_script('wu-legacy-signup', wu_get_asset('legacy-signup.js', 'js'), ['wu-functions'], wu_get_version(), true);

			wp_enqueue_script('wu-legacy-signup');

			wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), ['dashicons'], wu_get_version());
		}

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('period_selection', $attributes['period_selection_template']);

		$content = $template_class ? $template_class->render_container($attributes) : __('Template does not exist.', 'multisite-ultimate');

		$checkout_fields = [];

		$checkout_fields[ $attributes['id'] ] = [
			'type'            => 'note',
			'id'              => $attributes['id'],
			'wrapper_classes' => $attributes['element_classes'],
			'desc'            => $content,
		];

		$checkout_fields['duration'] = [
			'type'      => 'hidden',
			'html_attr' => [
				'v-model' => 'duration',
			],
		];

		$checkout_fields['duration_unit'] = [
			'type'      => 'hidden',
			'html_attr' => [
				'v-model' => 'duration_unit',
			],
		];

		return $checkout_fields;
	}
}
