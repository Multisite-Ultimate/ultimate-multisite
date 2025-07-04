<?php
/**
 * Describes a field and contains helper functions for sanitization and validation.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Describes a field and contains helper functions for sanitization and validation.
 *
 * @since 2.0.0
 */
class Field implements \JsonSerializable {

	/**
	 * Holds the attributes of this field.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $atts = [];

	/**
	 * Holds the value of the settings represented by this field.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Field id. This is going to be used to retrieve the value from the database later.
	 * @param array  $atts Field attributes.
	 */
	public function __construct($id, $atts) {

		$this->set_attributes($id, $atts);
	}

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Field id. This is going to be used to retrieve the value from the database later.
	 * @param array  $atts Field attributes.
	 * @return void
	 */
	public function set_attributes($id, $atts): void {

		$this->atts = wp_parse_args(
			$atts,
			[
				'id'                => $id,
				'type'              => 'text',
				'icon'              => 'dashicons-wu-cog',
				'action'            => false,
				'form'              => false,
				'title'             => false,
				'img'               => false,
				'desc'              => false,
				'content'           => false,
				'display_value'     => false,
				'default_value'     => false,
				'tooltip'           => false,
				'args'              => false,
				'sortable'          => false,
				'placeholder'       => false,
				'options'           => false,
				'options_template'  => false,
				'require'           => false,
				'button'            => false,
				'width'             => false,
				'rules'             => false,
				'min'               => false,
				'max'               => false,
				'allow_html'        => false,
				'append'            => false,
				'order'             => false,
				'dummy'             => false,
				'disabled'          => false,
				'capability'        => false,
				'edit'              => false,
				'copy'              => false,
				'validation'        => false,
				'meter'             => false,
				'href'              => false,
				'raw'               => false,
				'money'             => false,
				'stacked'           => false, // If the field is inside a restricted container
				'columns'           => 1,
				'classes'           => '',
				'wrapper_classes'   => '',
				'html_attr'         => [],
				'wrapper_html_attr' => [],
				'sub_fields'        => [],
				'prefix'            => '',
				'suffix'            => '',
				'prefix_html_attr'  => [],
				'suffix_html_attr'  => [],
			]
		);
	}

	/**
	 * Set a particular attribute.
	 *
	 * @since 2.0.0
	 *
	 * @param string $att The attribute name.
	 * @param mixed  $value The new attribute value.
	 * @return void
	 */
	public function set_attribute($att, $value): void {

		$this->atts[ $att ] = $value;
	}

	/**
	 * Returns the list of field attributes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_attributes() {

		return $this->atts;
	}

	/**
	 * Makes sure old fields remain compatible.
	 *
	 * We are making some field type name changes in 2.0.
	 * This method lists an array with aliases in the following format:
	 *
	 * - old_type_name => new_type_name.
	 *
	 * We throw a deprecation notice to make sure developers update their code appropriately.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_compat_template_name() {

		$aliases = [
			'heading'             => 'header',
			'heading_collapsible' => 'header',
			'select2'             => 'select',
			'checkbox'            => 'toggle',
		];

		$deprecated = [
			'heading',
			'heading_collapsible',
			'select2',
		];

		if (array_key_exists($this->type, $aliases)) {
			$new_type_name = $aliases[ $this->type ];

			if (array_key_exists($this->type, $deprecated)) {

				// translators: The %1$s placeholder is the old type name, the second, the new type name.
				_doing_it_wrong(esc_html('wu_add_field'), esc_html(sprintf(__('The field type "%1$s" is no longer supported, use "%2$s" instead.'), $this->type, $new_type_name)), '2.0.0');
			}

			/*
			 * Back Compat for Select2 Fields
			 */
			if ('select2' === $this->type) {
				$this->atts['html_attr']['data-selectize'] = 1;
				$this->atts['html_attr']['multiple']       = 1;
			}

			return $new_type_name;
		}

		return false;
	}

	/**
	 * Returns the template name for a field.
	 *
	 * We use this to go to the views folder and fetch the HTML template.
	 * The return here is not an absolute path, as the folder depends on the view the form is using.
	 *
	 * @see \WP_Ultimo\UI\Forms
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_template_name() {

		$compat_name = $this->get_compat_template_name();

		$view_name = $compat_name ?: $this->type;

		return str_replace('_', '-', (string) $view_name);
	}

	/**
	 * Returns attributes as class properties.
	 *
	 * @since 2.0.0
	 *
	 * @param string $att Attribute to retrieve.
	 * @return mixed
	 */
	public function __get($att) {

		$allowed_callable = [
			'title',
			'desc',
			'content',
			'display_value',
			'default_value',
			'tooltip',
			'options',
			'require',
			'validation',
			'value',
			'html_attr',
			'img',
		];

		$attr = $this->atts[ $att ] ?? false;

		$allow_callable_prefix = is_string($attr) && str_starts_with($attr, 'wu_get_') && is_callable($attr);
		$allow_callable_method = is_array($attr) && is_callable($attr);

		if (in_array($att, $allowed_callable, true) && ($allow_callable_prefix || $allow_callable_method || is_a($attr, \Closure::class))) {
			$attr = call_user_func($attr, $this);
		}

		if ('wrapper_classes' === $att && isset($this->atts['wrapper_html_attr']['v-show'])) {
			$this->atts['wrapper_classes'] .= ' wu-requires-other';
		}

		if ('type' === $att && 'submit' === $this->atts[ $att ]) {
			$this->atts['wrapper_classes'] .= ' wu-submit-field';
		}

		if ('type' === $att && 'tab-select' === $this->atts[ $att ]) {
			$this->atts['wrapper_classes'] .= ' wu-tab-field';
		}

		if ('wrapper_classes' === $att && is_a($this->form, '\\WP_Ultimo\\UI\\Form')) {
			return $this->form->field_wrapper_classes . ' ' . $this->atts['wrapper_classes'];
		}

		if ('classes' === $att && is_a($this->form, '\\WP_Ultimo\\UI\\Form')) {
			return $this->form->field_classes . ' ' . $this->atts['classes'];
		}

		if ('title' === $att && false === $attr && isset($this->atts['name'])) {
			$attr = $this->atts['name'];
		}

		return $attr;
	}

	/**
	 * Returns the list of sanitization callbacks for each field type
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function sanitization_rules() {

		$rules = [
			'text'           => 'sanitize_text_field',
			'header'         => '__return_null',
			'number'         => [$this, 'validate_number_field'],
			'wp_editor'      => [$this, 'validate_textarea_field'],
			'textarea'       => [$this, 'validate_textarea_field'],
			'checkbox'       => 'wu_string_to_bool',
			'multi_checkbox' => false,
			'select2'        => false,
			'multiselect'    => false,
		];

		return apply_filters('wu_settings_fields_sanitization_rules', $rules);
	}

	/**
	 * Returns the value of the setting represented by this field.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_value() {

		return $this->value;
	}

	/**
	 * Sets the value of the settings represented by this field.
	 *
	 * This alone won't save the setting to the database. This method also invokes the
	 * sanitization callback, so we can be sure the data is ready for database insertion.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value of the settings being represented by this field.
	 * @return WP_Ultimo\UI\Field
	 */
	public function set_value($value) {

		$this->value = $value;

		if ( ! $this->raw) {
			$this->sanitize();
		}

		return $this;
	}

	/**
	 * Runs the value of the field through the sanitization callback.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function sanitize(): void {

		$rules = $this->sanitization_rules();

		$sanitize_method = $rules[ $this->type ] ?? $rules['text'];

		if ($sanitize_method) {
			$this->value = call_user_func($sanitize_method, $this->value);
		}
	}

	/**
	 * Sanitization callback for fields of type number.
	 *
	 * Checks if the new value set is between the min and max boundaries.
	 *
	 * @since 2.0.0
	 *
	 * @param int|float $value Value of the settings being represented by this field.
	 * @return int|float
	 */
	protected function validate_number_field($value) {

		/**
		 * Check if the value respects the min/max values.
		 */
		if ($this->min && $value < $this->min) {
			return $this->min;
		}

		if ($this->max && $value > $this->max) {
			return $this->max;
		}

		return $value;
	}

	/**
	 * Cleans the value submitted via a textarea or wp_editor field for database insertion.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Value of the settings being represented by this field.
	 * @return string
	 */
	protected function validate_textarea_field($value) {

		if ($this->allow_html) {
			return stripslashes(wp_filter_post_kses(addslashes($value)));
		}

		return wp_strip_all_tags(stripslashes($value));
	}

	/**
	 * Return HTML attributes for the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_html_attributes() {

		if (is_callable($this->atts['html_attr'])) {
			$this->atts['html_attr'] = call_user_func($this->atts['html_attr']);
		}

		$attributes = $this->atts['html_attr'];

		unset($this->atts['html_attr']['class']);

		if ('number' === $this->type) {
			if (false !== $this->min) {
				$attributes['min'] = $this->min;
			}

			if (false !== $this->max) {
				$attributes['max'] = $this->max;
			}
		}

		/*
		 * Adds money formatting and masking
		 */
		if (false !== $this->money) {
			$attributes['v-bind'] = 'money_settings';
		}

		return wu_array_to_html_attrs($attributes);
	}

	/**
	 * Return HTML attributes for the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_wrapper_html_attributes() {

		$attributes = $this->atts['wrapper_html_attr'];

		unset($this->atts['wrapper_html_attr']['class']);

		return wu_array_to_html_attrs($attributes);
	}

	/**
	 * Implements our on json_decode version of this object. Useful for use in vue.js
	 *
	 * @since 2.0.0
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		return $this->atts;
	}
}
