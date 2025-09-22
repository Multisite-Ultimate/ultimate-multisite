<?php
/**
 * Test case for Base Signup Field.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Checkout\Signup_Fields;

use WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field;
use WP_UnitTestCase;

/**
 * Test signup field base class with concrete implementation.
 */
class Test_Signup_Field extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {
		return 'test_field';
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
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {
		return 'Test Field';
	}

	/**
	 * Returns the description of the field/element.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {
		return 'A test field for testing purposes';
	}

	/**
	 * Returns the tooltip of the field/element.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tooltip() {
		return 'This is a test field tooltip';
	}

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {
		return 'dashicons-admin-generic';
	}

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {
		return [
			'test_option' => [
				'type'  => 'text',
				'title' => 'Test Option',
			],
		];
	}

	/**
	 * Returns the field/element actual field array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {
		return [
			'type'        => $this->get_type(),
			'title'       => $this->get_title(),
			'description' => $this->get_description(),
			'required'    => $this->is_required(),
		];
	}

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 * @param array $attributes Field attributes.
	 * @return array
	 */
	public function to_fields_array($attributes = []) {
		return [
			'test_field' => [
				'type'        => 'text',
				'title'       => $this->get_title(),
				'placeholder' => 'Enter test value',
				'required'    => $this->is_required(),
			],
		];
	}

	/**
	 * Outputs the contents of the field.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {
		echo '<input type="text" name="test_field" />';
	}
}

/**
 * Test Base Signup Field functionality.
 */
class Base_Signup_Field_Test extends WP_UnitTestCase {

	/**
	 * Test field instance.
	 *
	 * @var Test_Signup_Field
	 */
	private $field;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->field = new Test_Signup_Field();
	}

	/**
	 * Test field type.
	 */
	public function test_get_type() {
		$this->assertEquals('test_field', $this->field->get_type());
	}

	/**
	 * Test field required status.
	 */
	public function test_is_required() {
		$this->assertTrue($this->field->is_required());
	}

	/**
	 * Test field title.
	 */
	public function test_get_title() {
		$this->assertEquals('Test Field', $this->field->get_title());
	}

	/**
	 * Test field description.
	 */
	public function test_get_description() {
		$this->assertEquals('A test field for testing purposes', $this->field->get_description());
	}

	/**
	 * Test field tooltip.
	 */
	public function test_get_tooltip() {
		$this->assertEquals('This is a test field tooltip', $this->field->get_tooltip());
	}

	/**
	 * Test field icon.
	 */
	public function test_get_icon() {
		$this->assertEquals('dashicons-admin-generic', $this->field->get_icon());
	}

	/**
	 * Test field configuration fields.
	 */
	public function test_get_fields() {
		$fields = $this->field->get_fields();

		$this->assertIsArray($fields);
		$this->assertArrayHasKey('test_option', $fields);
		$this->assertEquals('text', $fields['test_option']['type']);
		$this->assertEquals('Test Option', $fields['test_option']['title']);
	}

	/**
	 * Test field to array conversion.
	 */
	public function test_to_array() {
		$array = $this->field->to_array();

		$this->assertIsArray($array);
		$this->assertEquals('test_field', $array['type']);
		$this->assertEquals('Test Field', $array['title']);
		$this->assertEquals('A test field for testing purposes', $array['description']);
		$this->assertTrue($array['required']);
	}

	/**
	 * Test field output.
	 */
	public function test_output() {
		ob_start();
		$this->field->output();
		$output = ob_get_clean();

		$this->assertStringContainsString('input', $output);
		$this->assertStringContainsString('test_field', $output);
	}

	/**
	 * Test field attribute handling.
	 */
	public function test_attributes() {
		// Test that attributes property exists
		$reflection = new \ReflectionClass($this->field);
		$this->assertTrue($reflection->hasProperty('attributes'));

		$property = $reflection->getProperty('attributes');
		$property->setAccessible(true);

		// Initially should be null or empty
		$attributes = $property->getValue($this->field);
		$this->assertTrue(is_null($attributes) || empty($attributes));
	}

	/**
	 * Test abstract class enforcement.
	 */
	public function test_abstract_methods() {
		$reflection = new \ReflectionClass(Base_Signup_Field::class);

		$this->assertTrue($reflection->isAbstract());

		// Check that all required abstract methods exist
		$abstract_methods = [
			'get_type',
			'is_required',
			'get_title',
			'get_description',
			'get_tooltip',
			'get_icon',
			'get_fields',
		];

		foreach ($abstract_methods as $method) {
			$this->assertTrue($reflection->hasMethod($method));
		}
	}

	/**
	 * Test field inheritance.
	 */
	public function test_inheritance() {
		$this->assertInstanceOf(Base_Signup_Field::class, $this->field);
	}

	/**
	 * Test field method return types.
	 */
	public function test_return_types() {
		$this->assertIsString($this->field->get_type());
		$this->assertIsBool($this->field->is_required());
		$this->assertIsString($this->field->get_title());
		$this->assertIsString($this->field->get_description());
		$this->assertIsString($this->field->get_tooltip());
		$this->assertIsString($this->field->get_icon());
		$this->assertIsArray($this->field->get_fields());
		$this->assertIsArray($this->field->to_array());
	}

	/**
	 * Test field validation methods exist.
	 */
	public function test_has_validation_methods() {
		$reflection = new \ReflectionClass($this->field);

		// These are common methods that signup fields often have
		$common_methods = ['get_type', 'get_title', 'is_required'];

		foreach ($common_methods as $method) {
			$this->assertTrue($reflection->hasMethod($method), "Method {$method} should exist");
		}
	}
}
