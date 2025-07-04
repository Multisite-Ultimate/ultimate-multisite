<?php
use PHPUnit\Framework\TestCase;
use WP_Ultimo\Builders\Block_Editor\Block_Editor_Widget_Manager;

class BlockEditorWidgetManagerTest extends TestCase {
	public function testGetAttributesFromFields() {
		// Mock element with fields and defaults
		$mockElement = $this->createMock(\WP_Ultimo\Builders\Block_Editor\Element::class);

		// Define test fields
		$fields = [
			'text_field'   => [
				'type'  => 'text',
				'value' => 'default text',
			],
			'toggle_field' => [
				'type' => 'toggle',
			],
			'number_field' => [
				'type'  => 'number',
				'value' => 42,
			],
		];

		// Define test defaults
		$defaults = [
			'text_field'   => 'default value',
			'toggle_field' => false,
			'number_field' => 0,
		];

		// Set up mock methods
		$mockElement->method('fields')->willReturn($fields);
		$mockElement->method('defaults')->willReturn($defaults);

		// Test case 1: Default values
		$widgetManager = new Block_Editor_Widget_Manager();
		$result        = $widgetManager->get_attributes_from_fields($mockElement);

		$expected = [
			'text_field'   => [
				'default' => 'default text',
				'type'    => 'string',
			],
			'toggle_field' => [
				'default' => false,
				'type'    => 'boolean',
			],
			'number_field' => [
				'default' => 42,
				'type'    => 'integer',
			],
		];

		$this->assertEquals($expected, $result);

		// Test case 2: Missing values
		$fields['missing_value'] = [
			'type' => 'text',
		];
		$mockElement->method('fields')->willReturn($fields);

		$result = $widgetManager->get_attributes_from_fields($mockElement);
		$this->assertEquals('', $result['missing_value']['default']);
		$this->assertEquals('string', $result['missing_value']['type']);
	}
}
