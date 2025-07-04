<?php

namespace WP_Ultimo\Models;

use WP_UnitTestCase;

class Checkout_Form_Test extends WP_UnitTestCase {

	/**
	 * Test checkout form creation with valid data.
	 */
	public function test_checkout_form_creation_with_valid_data(): void {
		$checkout_form = new Checkout_Form();
		$checkout_form->set_name('Test Checkout Form');
		$checkout_form->set_slug('test-checkout-form');
		$checkout_form->set_active(true);
		$checkout_form->set_custom_css('.test { color: red; }');
		$checkout_form->set_template('single-step');

		$this->assertEquals('Test Checkout Form', $checkout_form->get_name());
		$this->assertEquals('test-checkout-form', $checkout_form->get_slug());
		$this->assertTrue($checkout_form->is_active());
		$this->assertEquals('.test { color: red; }', $checkout_form->get_custom_css());
		$this->assertEquals('single-step', $checkout_form->get_template());
	}

	/**
	 * Test default active status.
	 */
	public function test_default_active_status(): void {
		$checkout_form = new Checkout_Form();
		$this->assertTrue($checkout_form->is_active());
	}

	/**
	 * Test active status setter and getter.
	 */
	public function test_active_status_functionality(): void {
		$checkout_form = new Checkout_Form();

		$checkout_form->set_active(false);
		$this->assertFalse($checkout_form->is_active());

		$checkout_form->set_active(true);
		$this->assertTrue($checkout_form->is_active());

		// Test with non-boolean values
		$checkout_form->set_active(1);
		$this->assertTrue($checkout_form->is_active());

		$checkout_form->set_active(0);
		$this->assertFalse($checkout_form->is_active());
	}

	/**
	 * Test settings functionality.
	 */
	public function test_settings_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test empty settings
		$this->assertEquals([], $checkout_form->get_settings());

		// Test setting settings as array
		$settings = [
			[
				'id'     => 'checkout',
				'name'   => 'Checkout Step',
				'fields' => [],
			],
		];

		$checkout_form->set_settings($settings);
		$this->assertEquals($settings, $checkout_form->get_settings());

		// Test setting settings as serialized string
		$serialized_settings = serialize($settings);
		$checkout_form->set_settings($serialized_settings);
		$this->assertEquals($settings, $checkout_form->get_settings());
	}

	/**
	 * Test allowed countries functionality.
	 */
	public function test_allowed_countries_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test no country restrictions
		$this->assertFalse($checkout_form->has_country_lock());

		// Test setting allowed countries
		$countries = ['US', 'CA', 'GB'];
		$checkout_form->set_allowed_countries($countries);

		$this->assertTrue($checkout_form->has_country_lock());
		$this->assertEquals($countries, $checkout_form->get_allowed_countries());

		// Test with serialized countries
		$serialized_countries = serialize($countries);
		$checkout_form->set_allowed_countries($serialized_countries);
		$this->assertEquals($countries, $checkout_form->get_allowed_countries());
	}

	/**
	 * Test thank you page functionality.
	 */
	public function test_thank_you_page_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test no thank you page set
		$this->assertFalse($checkout_form->has_thank_you_page());
		$this->assertEmpty($checkout_form->get_thank_you_page_id());

		// Test setting thank you page
		$page_id = self::factory()->post->create(['post_type' => 'page']);
		$checkout_form->set_thank_you_page_id($page_id);

		$this->assertEquals($page_id, $checkout_form->get_thank_you_page_id());
		$this->assertTrue($checkout_form->has_thank_you_page());
	}

	/**
	 * Test conversion snippets functionality.
	 */
	public function test_conversion_snippets_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test empty snippets
		$this->assertEmpty($checkout_form->get_conversion_snippets());

		// Test setting snippets
		$snippets = '<script>console.log("conversion");</script>';
		$checkout_form->set_conversion_snippets($snippets);
		$this->assertEquals($snippets, $checkout_form->get_conversion_snippets());
	}

	/**
	 * Test shortcode generation.
	 */
	public function test_shortcode_generation(): void {
		$checkout_form = new Checkout_Form();
		$checkout_form->set_slug('test-form');

		$expected_shortcode = '[wu_checkout slug="test-form"]';
		$this->assertEquals($expected_shortcode, $checkout_form->get_shortcode());
	}

	/**
	 * Test validation rules.
	 */
	public function test_validation_rules(): void {
		$checkout_form = new Checkout_Form();
		$rules         = $checkout_form->validation_rules();

		// Check that required validation rules exist
		$this->assertArrayHasKey('name', $rules);
		$this->assertArrayHasKey('slug', $rules);
		$this->assertArrayHasKey('active', $rules);
		$this->assertArrayHasKey('custom_css', $rules);
		$this->assertArrayHasKey('settings', $rules);
		$this->assertArrayHasKey('allowed_countries', $rules);
		$this->assertArrayHasKey('thank_you_page_id', $rules);
		$this->assertArrayHasKey('conversion_snippets', $rules);
		$this->assertArrayHasKey('template', $rules);

		// Check specific rule patterns
		$this->assertStringContainsString('required', $rules['name']);
		$this->assertStringContainsString('required', $rules['slug']);
		$this->assertStringContainsString('unique', $rules['slug']);
		$this->assertStringContainsString('min:3', $rules['slug']);
		$this->assertStringContainsString('required', $rules['active']);
		$this->assertStringContainsString('default:1', $rules['active']);
		$this->assertStringContainsString('checkout_steps', $rules['settings']);
		$this->assertStringContainsString('integer', $rules['thank_you_page_id']);
		$this->assertStringContainsString('in:blank,single-step,multi-step', $rules['template']);
	}

	/**
	 * Test step count functionality.
	 */
	public function test_step_count_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test empty settings
		$this->assertEquals(0, $checkout_form->get_step_count());

		// Test with steps
		$settings = [
			[
				'id'     => 'step1',
				'name'   => 'Step 1',
				'fields' => [],
			],
			[
				'id'     => 'step2',
				'name'   => 'Step 2',
				'fields' => [],
			],
			[
				'id'     => 'step3',
				'name'   => 'Step 3',
				'fields' => [],
			],
		];

		$checkout_form->set_settings($settings);
		$this->assertEquals(3, $checkout_form->get_step_count());
	}

	/**
	 * Test field count functionality.
	 */
	public function test_field_count_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test empty settings
		$this->assertEquals(0, $checkout_form->get_field_count());

		// Test with fields
		$settings = [
			[
				'id'     => 'checkout',
				'name'   => 'Checkout',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
					],
					[
						'id'   => 'password',
						'type' => 'password',
					],
					[
						'id'   => 'submit',
						'type' => 'submit_button',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);
		$this->assertEquals(3, $checkout_form->get_field_count());
	}

	/**
	 * Test get_step functionality.
	 */
	public function test_get_step_functionality(): void {
		$checkout_form = new Checkout_Form();

		$settings = [
			[
				'id'     => 'checkout',
				'name'   => 'Checkout Step',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);

		// Test getting existing step
		$step = $checkout_form->get_step('checkout');
		$this->assertIsArray($step);
		$this->assertEquals('checkout', $step['id']);
		$this->assertEquals('Checkout Step', $step['name']);
		$this->assertArrayHasKey('logged', $step);
		$this->assertArrayHasKey('fields', $step);

		// Test getting non-existing step
		$non_existing_step = $checkout_form->get_step('non-existing');
		$this->assertFalse($non_existing_step);
	}

	/**
	 * Test get_field functionality.
	 */
	public function test_get_field_functionality(): void {
		$checkout_form = new Checkout_Form();

		$settings = [
			[
				'id'     => 'checkout',
				'name'   => 'Checkout Step',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
						'name' => 'Email Address',
					],
					[
						'id'   => 'password',
						'type' => 'password',
						'name' => 'Password',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);

		// Test getting existing field
		$field = $checkout_form->get_field('checkout', 'email');
		$this->assertIsArray($field);
		$this->assertEquals('email', $field['id']);
		$this->assertEquals('email', $field['type']);

		// Test getting non-existing field
		$non_existing_field = $checkout_form->get_field('checkout', 'non-existing');
		$this->assertFalse($non_existing_field);

		// Test getting field from non-existing step
		$field_from_non_existing_step = $checkout_form->get_field('non-existing', 'email');
		$this->assertFalse($field_from_non_existing_step);
	}

	/**
	 * Test get_all_fields functionality.
	 */
	public function test_get_all_fields_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test empty settings
		$this->assertEquals([], $checkout_form->get_all_fields());

		$settings = [
			[
				'id'     => 'step1',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
					],
					[
						'id'   => 'password',
						'type' => 'password',
					],
				],
			],
			[
				'id'     => 'step2',
				'fields' => [
					[
						'id'   => 'site_title',
						'type' => 'site_title',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);

		$all_fields = $checkout_form->get_all_fields();
		$this->assertCount(3, $all_fields);

		$field_ids = array_column($all_fields, 'id');
		$this->assertContains('email', $field_ids);
		$this->assertContains('password', $field_ids);
		$this->assertContains('site_title', $field_ids);
	}

	/**
	 * Test get_all_fields_by_type functionality.
	 */
	public function test_get_all_fields_by_type_functionality(): void {
		$checkout_form = new Checkout_Form();

		$settings = [
			[
				'id'     => 'checkout',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
					],
					[
						'id'   => 'username',
						'type' => 'text',
					],
					[
						'id'   => 'password',
						'type' => 'password',
					],
					[
						'id'   => 'bio',
						'type' => 'text',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);

		// Test getting fields by single type
		$text_fields = $checkout_form->get_all_fields_by_type('text');
		$this->assertCount(2, $text_fields);

		// Test getting fields by multiple types
		$multiple_types_fields = $checkout_form->get_all_fields_by_type(['email', 'password']);
		$this->assertCount(2, $multiple_types_fields);
	}

	/**
	 * Test template functionality.
	 */
	public function test_template_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test setting template
		$checkout_form->set_template('multi-step');
		$this->assertEquals('multi-step', $checkout_form->get_template());

		$checkout_form->set_template('single-step');
		$this->assertEquals('single-step', $checkout_form->get_template());

		$checkout_form->set_template('blank');
		$this->assertEquals('blank', $checkout_form->get_template());
	}

	/**
	 * Test use_template functionality.
	 */
	public function test_use_template_functionality(): void {
		$checkout_form = new Checkout_Form();

		// Test using single-step template
		$checkout_form->use_template('single-step');
		$settings = $checkout_form->get_settings();
		$this->assertNotEmpty($settings);
		$this->assertIsArray($settings);

		// Should have at least one step
		$this->assertGreaterThan(0, count($settings));
		$this->assertArrayHasKey('id', $settings[0]);

		// Test using multi-step template
		$checkout_form->use_template('multi-step');
		$multi_step_settings = $checkout_form->get_settings();
		$this->assertNotEmpty($multi_step_settings);
		$this->assertIsArray($multi_step_settings);

		// Multi-step should have more steps than single-step
		$this->assertGreaterThan(count($settings), count($multi_step_settings));
	}

	/**
	 * Test query class property.
	 */
	public function test_query_class(): void {
		$checkout_form = new Checkout_Form();

		// Use reflection to access protected property
		$reflection           = new \ReflectionClass($checkout_form);
		$query_class_property = $reflection->getProperty('query_class');
		$query_class_property->setAccessible(true);
		$query_class = $query_class_property->getValue($checkout_form);

		$this->assertEquals(\WP_Ultimo\Database\Checkout_Forms\Checkout_Form_Query::class, $query_class);
	}

	/**
	 * Test static method finish_checkout_form_fields.
	 */
	public function test_finish_checkout_form_fields(): void {
		// Without payment, should return empty array
		$fields = Checkout_Form::finish_checkout_form_fields();
		$this->assertEquals([], $fields);
	}

	/**
	 * Test static method membership_change_form_fields.
	 */
	public function test_membership_change_form_fields(): void {
		// Without membership, should return empty array
		$fields = Checkout_Form::membership_change_form_fields();
		$this->assertEquals([], $fields);
	}

	/**
	 * Test static method add_new_site_form_fields.
	 */
	public function test_add_new_site_form_fields(): void {
		// Without membership, should return empty array
		$fields = Checkout_Form::add_new_site_form_fields();
		$this->assertEquals([], $fields);
	}

	/**
	 * Test steps_to_show property.
	 */
	public function test_steps_to_show_functionality(): void {
		$checkout_form = new Checkout_Form();

		$settings = [
			[
				'id'     => 'checkout',
				'logged' => 'always',
				'fields' => [
					[
						'id'   => 'email',
						'type' => 'email',
					],
				],
			],
			[
				'id'     => 'guest_only',
				'logged' => 'guests_only',
				'fields' => [
					[
						'id'   => 'signup',
						'type' => 'text',
					],
				],
			],
		];

		$checkout_form->set_settings($settings);

		$steps_to_show = $checkout_form->get_steps_to_show();
		$this->assertIsArray($steps_to_show);
	}
}
