<?php
/**
 * Describes a form that can be used in different contexts, with different view files for each field type.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\UI\Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Describes a form that can be used in different contexts, with different view files for each field type.
 *
 * @since 2.0.0
 */
class Form implements \JsonSerializable {

	/**
	 * Holds the attributes of this field.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $atts = [];

	/**
	 * Holds the fields we want to display using this form.
	 *
	 * @since 2.0.0
	 * @var Field[]
	 */
	protected $fields = [];

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Form id. This is going to be used to retrieve the value from the database later.
	 * @param array  $fields List of arrays representing the form fields.
	 * @param array  $atts Form attributes.
	 */
	public function __construct($id, $fields, $atts = []) {

		$this->atts = apply_filters(
			"wu_{$id}_form_atts",
			wp_parse_args(
				$atts,
				[
					'id'                    => $id,
					'method'                => 'post',
					'before'                => '',
					'after'                 => '',
					'action'                => false,
					'title'                 => false,
					'wrap_in_form_tag'      => false,
					'wrap_tag'              => 'div',
					'classes'               => false,
					'field_wrapper_classes' => false,
					'field_classes'         => false,
					'views'                 => 'settings/fields',
					'variables'             => [],
					'step'                  => (object) [
						'classes'    => '',
						'element_id' => '',
					],
					'html_attr'             => [
						'class' => '',
					],
				]
			)
		);

		$this->set_fields($fields);
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
			'before',
			'after',
		];

		$attr = $this->atts[ $att ] ?? false;

		if (in_array($att, $allowed_callable, true) && is_callable($attr)) {
			$attr = call_user_func($attr, $this);
		}

		return $attr;
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
	 * Returns the list of fields used by the form.
	 *
	 * @since 2.0.0
	 * @return Field[]
	 */
	public function get_fields() {

		return (array) $this->fields;
	}

	/**
	 * Casts fields to \WP_Ultimo\UI\Fields and stores them on private list.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields List of fields of the form.
	 * @return void
	 */
	public function set_fields($fields): void {

		$id = $this->id;

		/**
		 * Filters the fields on a form. The form is identified by the ID in the filter name.
		 *
		 * @since 2.0.0
		 *
		 * @param array $fields List of fields of the form.
		 */
		$fields = apply_filters("wu_{$id}_form_fields", $fields);

		foreach ($fields as $field_slug => $field) {
			$field['form'] = $this;

			$this->fields[ $field_slug ] = new Field($field_slug, $field);
		}
	}

	/**
	 * Renders the form with its fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render(): void {

		$variables = array_merge(
			$this->variables,
			[
				'form_slug' => $this->id,
				'form'      => $this,
				'step'      => $this->step,
			]
		);

		wu_get_template("{$this->views}/form", $variables);
	}

	/**
	 * @return void
	 */
	public function render_fields(): void {
		foreach ($this->get_fields() as $field_slug => $field) {
			$template_name = $field->get_template_name();

			if (wu_get_isset($field->wrapper_html_attr, 'id') === false) {
				$new_wrapper_attributes = $field->wrapper_html_attr;

				$new_wrapper_attributes['id'] = "wrapper-field-$field_slug";

				$field->set_attribute('wrapper_html_attr', $new_wrapper_attributes);
			}

			wu_get_template(
				"{$this->views}/field-{$template_name}",
				[
					'field_slug' => $field_slug,
					'field'      => $field,
				],
				"{$this->views}/field-text"
			);
		}
	}

	/**
	 * Echos HTML attributes for the field.
	 *
	 * @since 2.4.4
	 * @return void
	 */
	public function print_html_attributes(): void {
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

		wu_print_html_attributes($attributes);
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
