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
use WP_Ultimo\Models\Site;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Template_Selection extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 */
	public function get_type(): string {

		return 'template_selection';
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

		return __('Templates', 'multisite-ultimate');
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

		return __('Adds a template selection section. This allows the customer to choose a pre-built site to be used as a template for the site being currently created.', 'multisite-ultimate');
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

		return __('Adds a template selection section. This allows the customer to choose a pre-built site to be used as a template for the site being currently created.', 'multisite-ultimate');
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 */
	public function get_icon(): string {

		return 'dashicons-wu-layout';
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
			'template_selection_sites'                  => implode(',', wu_get_site_templates(['fields' => 'ids'])),
			'template_selection_type'                   => 'name',
			'template_selection_template'               => 'clean',
			'cols'                                      => 3,
			'hide_template_selection_when_pre_selected' => false,
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
			'id'       => 'template_selection',
			'name'     => __('Template Selection', 'multisite-ultimate'),
			'required' => true,
		];
	}

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_template_selection_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('template_selection');

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

		$editor_fields['cols'] = [
			'type' => 'hidden',
		];

		$editor_fields['template_selection_type'] = [
			'type'      => 'select',
			'title'     => __('Available templates', 'multisite-ultimate'),
			'desc'      => __('How do you want to choose available which templates will be available.', 'multisite-ultimate'),
			'order'     => 20,
			'options'   => [
				'name'       => __('Select by names', 'multisite-ultimate'),
				'categories' => __('Select by categories', 'multisite-ultimate'),
				'all'        => __('All templates', 'multisite-ultimate'),
			],
			'html_attr' => [
				'v-model' => 'template_selection_type',
			],
		];

		$editor_fields['template_selection_categories'] = [
			'type'              => 'select',
			'title'             => __('Template Categories', 'multisite-ultimate'),
			'placeholder'       => __('e.g.: Landing Page, Health...', 'multisite-ultimate'),
			'desc'              => __('Customers will be able to filter by categories during signup.', 'multisite-ultimate'),
			'order'             => 21,
			'options'           => Site::get_all_categories(),
			'html_attr'         => [
				'data-selectize-categories' => 1,
				'multiple'                  => 1,
			],
			'wrapper_html_attr' => [
				'v-show' => 'template_selection_type === "categories"',
			],
		];

		$editor_fields['template_selection_sites'] = [
			'type'              => 'model',
			'title'             => __('Template Sites', 'multisite-ultimate'),
			'placeholder'       => __('e.g. Template Site 1, My Agency', 'multisite-ultimate'),
			'desc'              => __('Be sure to add the templates in the order you want them to show up.', 'multisite-ultimate'),
			'order'             => 22,
			'html_attr'         => [
				'v-model'           => 'template_selection_sites',
				'data-model'        => 'site',
				'data-value-field'  => 'blog_id',
				'data-label-field'  => 'title',
				'data-search-field' => 'title',
				'data-max-items'    => 999,
				'data-include'      => implode(
					',',
					wu_get_site_templates(
						[
							'fields' => 'blog_id',
						]
					)
				),
			],
			'wrapper_html_attr' => [
				'v-show' => 'template_selection_type === \'name\'',
			],
		];

		$editor_fields['hide_template_selection_when_pre_selected'] = [
			'type'      => 'toggle',
			'title'     => __('Hide when Pre-Selected', 'multisite-ultimate'),
			'desc'      => __('Prevent customers from seeing this field when a template was already selected via the URL.', 'multisite-ultimate'),
			'tooltip'   => __('If the template selection field is the only field in the current step, the step will be skipped.', 'multisite-ultimate'),
			'value'     => 0,
			'order'     => 23,
			'html_attr' => [
				'v-model' => 'hide_template_selection_when_pre_selected',
			],
		];

		$editor_fields['template_selection_template'] = [
			'type'   => 'group',
			'order'  => 24,
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('template_selection'),
			'fields' => [
				'template_selection_template' => [
					'type'            => 'select',
					'title'           => __('Template Selector Template', 'multisite-ultimate'),
					'placeholder'     => __('Select your Template', 'multisite-ultimate'),
					'options'         => [$this, 'get_template_selection_templates'],
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => [
						'v-model' => 'template_selection_template',
					],
				],
			],
		];

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_1'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized template selection templates?<br><a target="_blank" class="wu-no-underline" href="https://github.com/superdav42/wp-multisite-waas/wiki/Customize-Checkout-Flow">See how you can do that here</a>.', 'multisite-ultimate')),
		// );

		return $editor_fields;
	}

	/**
	 * Treat the attributes array to avoid reaching the input var limits.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The attributes.
	 * @return array
	 */
	public function reduce_attributes($attributes) {

		$array_sites = json_decode(json_encode($attributes['sites']), true);

		$attributes['sites'] = array_values(array_column($array_sites, 'blog_id'));

		return $attributes;
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

		$checkout_fields['template_id'] = [
			'type'      => 'hidden',
			'html_attr' => [
				'v-model' => 'template_id',
			],
		];

		/**
		 * Hide when pre-selected.
		 */
		if (wu_should_hide_form_field($attributes)) {
			return $checkout_fields;
		}

		if (wu_get_isset($attributes, 'template_selection_template') === 'legacy') {
			wp_register_script('wu-legacy-signup', wu_get_asset('legacy-signup.js', 'js'), ['wu-functions'], wu_get_version(), true);

			wp_enqueue_script('wu-legacy-signup');

			wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), ['dashicons'], wu_get_version());
		}

		$site_list = $this->site_list($attributes);

		$customer_sites = [];

		if (wu_get_setting('allow_own_site_as_template')) {
			$customer = wu_get_current_customer();

			if ($customer) {
				$customer_sites = $customer->get_sites(['fields' => 'ids']);

				$site_list = array_merge(
					$customer_sites,
					$site_list
				);
			}
		}

		$sites = array_map('wu_get_site', $site_list);

		$sites = array_filter($sites);

		// Remove inactive sites
		$sites = array_filter($sites, fn($site) => $site->is_active());

		$template_attributes = [
			'sites'          => $sites,
			'name'           => $attributes['name'],
			'cols'           => $attributes['cols'],
			'categories'     => $attributes['template_selection_categories'] ?? \WP_Ultimo\Models\Site::get_all_categories($sites),
			'customer_sites' => $customer_sites,
		];

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('template_selection', $attributes['template_selection_template']);

		$content = $template_class ? $template_class->render_container($template_attributes, $this) : __('Template does not exist.', 'multisite-ultimate');

		$checkout_fields[ $attributes['id'] ] = [
			'type'            => 'note',
			'desc'            => $content,
			'wrapper_classes' => $attributes['element_classes'],
		];

		return $checkout_fields;
	}

	/**
	 * Return site list according to selection type used.
	 *
	 * @param  array $attributes Attributes saved on the editor form.
	 * @return array             Array of template ID's
	 */
	protected function site_list(array $attributes): array {

		$selection_type = wu_get_isset($attributes, 'template_selection_type', 'name');

		if ('name' === $selection_type) {
			return explode(',', $attributes['template_selection_sites']);
		}

		if ('all' === $selection_type) {
			return wu_get_site_templates(['fields' => 'blog_id']);
		}

		if ('categories' === $selection_type) {
			return array_column(
				\WP_Ultimo\Models\Site::get_all_by_categories(
					$attributes['template_selection_categories'],
					[
						'fields' => ['blog_id'],
					],
				),
				'blog_id'
			);
		}

		return explode(',', $attributes['template_selection_sites']);
	}
}
