<?php

namespace WP_Ultimo\Models;

use WP_Ultimo\Faker;

/**
 * Unit tests for the Event class.
 */
class Event_Test extends \WP_UnitTestCase {

	/**
	 * Event instance.
	 *
	 * @var Event
	 */
	protected $event;

	/**
	 * Sample test data.
	 *
	 * @var array
	 */
	protected $test_data;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->test_data = [
			'severity'    => Event::SEVERITY_INFO,
			'payload'     => [
				'action' => 'test_action',
				'result' => 'success',
			],
			'object_type' => 'site',
			'object_id'   => 1,
			'author_id'   => 1,
			'slug'        => 'test-event',
			'initiator'   => 'manual',
		];

		$this->event = new Event($this->test_data);
	}

	/**
	 * Test Event class constants.
	 */
	public function test_severity_constants() {
		$this->assertEquals(1, Event::SEVERITY_SUCCESS);
		$this->assertEquals(2, Event::SEVERITY_NEUTRAL);
		$this->assertEquals(3, Event::SEVERITY_INFO);
		$this->assertEquals(4, Event::SEVERITY_WARNING);
		$this->assertEquals(5, Event::SEVERITY_FATAL);
	}

	/**
	 * Test event creation and basic getters.
	 */
	public function test_event_creation() {
		$this->assertInstanceOf(Event::class, $this->event);
		$this->assertEquals(Event::SEVERITY_INFO, $this->event->get_severity());
		$this->assertEquals('site', $this->event->get_object_type());
		$this->assertEquals(1, $this->event->get_object_id());
		$this->assertEquals(1, $this->event->get_author_id());
		$this->assertEquals('test-event', $this->event->get_slug());
		$this->assertEquals('manual', $this->event->get_initiator());
	}

	/**
	 * Test validation rules.
	 */
	public function test_validation_rules() {
		$rules = $this->event->validation_rules();

		$this->assertArrayHasKey('severity', $rules);
		$this->assertArrayHasKey('payload', $rules);
		$this->assertArrayHasKey('object_type', $rules);
		$this->assertArrayHasKey('object_id', $rules);
		$this->assertArrayHasKey('author_id', $rules);
		$this->assertArrayHasKey('slug', $rules);
		$this->assertArrayHasKey('initiator', $rules);

		$this->assertStringContainsString('required', $rules['severity']);
		$this->assertStringContainsString('numeric', $rules['severity']);
		$this->assertStringContainsString('between:1,5', $rules['severity']);
		$this->assertStringContainsString('in:system,manual', $rules['initiator']);
	}

	/**
	 * Test severity getter and setter.
	 */
	public function test_severity_getter_setter() {
		$this->event->set_severity(Event::SEVERITY_WARNING);
		$this->assertEquals(Event::SEVERITY_WARNING, $this->event->get_severity());

		$this->event->set_severity(Event::SEVERITY_FATAL);
		$this->assertEquals(Event::SEVERITY_FATAL, $this->event->get_severity());
	}

	/**
	 * Test severity label retrieval.
	 */
	public function test_severity_labels() {
		$this->event->set_severity(Event::SEVERITY_SUCCESS);
		$this->assertEquals('Success', $this->event->get_severity_label());

		$this->event->set_severity(Event::SEVERITY_NEUTRAL);
		$this->assertEquals('Neutral', $this->event->get_severity_label());

		$this->event->set_severity(Event::SEVERITY_INFO);
		$this->assertEquals('Info', $this->event->get_severity_label());

		$this->event->set_severity(Event::SEVERITY_WARNING);
		$this->assertEquals('Warning', $this->event->get_severity_label());

		$this->event->set_severity(Event::SEVERITY_FATAL);
		$this->assertEquals('Fatal', $this->event->get_severity_label());

		// Test invalid severity returns default
		$this->event->set_severity(99);
		$this->assertEquals('Note', $this->event->get_severity_label());
	}

	/**
	 * Test severity CSS classes.
	 */
	public function test_severity_classes() {
		$this->event->set_severity(Event::SEVERITY_SUCCESS);
		$this->assertEquals('wu-bg-green-200 wu-text-green-700', $this->event->get_severity_class());

		$this->event->set_severity(Event::SEVERITY_WARNING);
		$this->assertEquals('wu-bg-yellow-200 wu-text-yellow-700', $this->event->get_severity_class());

		$this->event->set_severity(Event::SEVERITY_FATAL);
		$this->assertEquals('wu-bg-red-200 wu-text-red-700', $this->event->get_severity_class());

		// Test invalid severity returns empty string
		$this->event->set_severity(99);
		$this->assertEquals('', $this->event->get_severity_class());
	}

	/**
	 * Test payload getter and setter.
	 */
	public function test_payload_getter_setter() {
		$payload = [
			'test'   => 'data',
			'number' => 123,
		];
		$this->event->set_payload($payload);

		$retrieved_payload = $this->event->get_payload();
		$this->assertIsArray($retrieved_payload);
		$this->assertEquals('data', $retrieved_payload['test']);
		$this->assertEquals(123, $retrieved_payload['number']);
	}

	/**
	 * Test date created getter and setter.
	 */
	public function test_date_created() {
		$date = '2023-01-01 12:00:00';
		$this->event->set_date_created($date);
		$this->assertEquals($date, $this->event->get_date_created());
	}

	/**
	 * Test initiator getter and setter.
	 */
	public function test_initiator_getter_setter() {
		$this->event->set_initiator('system');
		$this->assertEquals('system', $this->event->get_initiator());

		$this->event->set_initiator('manual');
		$this->assertEquals('manual', $this->event->get_initiator());
	}

	/**
	 * Test author ID getter and setter.
	 */
	public function test_author_id_getter_setter() {
		$this->event->set_author_id(42);
		$this->assertEquals(42, $this->event->get_author_id());
	}

	/**
	 * Test object type getter and setter.
	 */
	public function test_object_type_getter_setter() {
		$this->event->set_object_type('customer');
		$this->assertEquals('customer', $this->event->get_object_type());

		$this->event->set_object_type('membership');
		$this->assertEquals('membership', $this->event->get_object_type());
	}

	/**
	 * Test object ID getter and setter.
	 */
	public function test_object_id_getter_setter() {
		$this->event->set_object_id(999);
		$this->assertEquals(999, $this->event->get_object_id());
	}

	/**
	 * Test slug getter and setter.
	 */
	public function test_slug_getter_setter() {
		$this->event->set_slug('new-test-slug');
		$this->assertEquals('new-test-slug', $this->event->get_slug());
	}

	/**
	 * Test message interpolation.
	 */
	public function test_message_interpolation() {
		$message = 'Hello {{name}}, your {{action}} was {{result}}';
		$payload = [
			'name'   => 'John',
			'action' => 'login',
			'result' => 'successful',
		];

		$interpolated = $this->event->interpolate_message($message, $payload);
		$this->assertEquals('Hello John, your login was successful', $interpolated);
	}

	/**
	 * Test message interpolation with nested arrays.
	 */
	public function test_message_interpolation_with_arrays() {
		$message = 'Status: {{status}}';
		$payload = [
			'status' => [
				'old' => 'pending',
				'new' => 'active',
			],
		];

		$interpolated = $this->event->interpolate_message($message, $payload);
		$this->assertEquals('Status: pending &rarr; active', $interpolated);
	}

	/**
	 * Test default system messages.
	 */
	public function test_default_system_messages() {
		$changed_message = Event::get_default_system_messages('changed');
		$created_message = Event::get_default_system_messages('created');
		$unknown_message = Event::get_default_system_messages('unknown-slug');

		$this->assertStringContainsString('{{model}}', $changed_message);
		$this->assertStringContainsString('{{object_id}}', $changed_message);
		$this->assertStringContainsString('was changed', $changed_message);

		$this->assertStringContainsString('was created', $created_message);
		$this->assertEquals('No Message', $unknown_message);
	}

	/**
	 * Test get_message method.
	 */
	public function test_get_message() {
		$this->event->set_slug('created');
		$this->event->set_object_type('site');
		$this->event->set_object_id(123);

		$message = $this->event->get_message();
		$this->assertStringContainsString('Site', $message);
		$this->assertStringContainsString('123', $message);
		$this->assertStringContainsString('was created', $message);
	}

	/**
	 * Test object type-specific getter methods return false for wrong types.
	 */
	public function test_object_type_specific_getters() {
		$this->event->set_object_type('site');

		$this->assertFalse($this->event->get_membership());
		$this->assertFalse($this->event->get_customer());
		$this->assertFalse($this->event->get_payment());
		$this->assertFalse($this->event->get_product());

		// Test with membership type
		$this->event->set_object_type('membership');
		$this->assertFalse($this->event->get_site());
		$this->assertFalse($this->event->get_customer());
	}

	/**
	 * Test to_array method.
	 */
	public function test_to_array() {
		$this->event->set_severity(Event::SEVERITY_INFO);
		$array = $this->event->to_array();

		$this->assertIsArray($array);
		$this->assertArrayHasKey('payload', $array);
		$this->assertArrayHasKey('message', $array);
		$this->assertArrayHasKey('severity_label', $array);
		$this->assertArrayHasKey('severity_classes', $array);
		$this->assertArrayHasKey('author', $array);

		$this->assertEquals('Info', $array['severity_label']);
		$this->assertEquals('wu-bg-blue-200 wu-text-blue-700', $array['severity_classes']);
	}

	/**
	 * Test author-related methods with no user.
	 */
	public function test_author_methods_with_no_user() {
		$this->event->set_author_id(0);

		$this->assertNull($this->event->get_author_user());
		$this->assertNull($this->event->get_author_display_name());
		$this->assertNull($this->event->get_author_email_address());
	}

	/**
	 * Test creating event with minimal required data.
	 */
	public function test_minimal_event_creation() {
		$minimal_data = [
			'severity'    => Event::SEVERITY_SUCCESS,
			'payload'     => ['test' => 'data'],
			'object_type' => 'network',
			'slug'        => 'minimal-event',
			'initiator'   => 'system',
		];

		$event = new Event($minimal_data);

		$this->assertInstanceOf(Event::class, $event);
		$this->assertEquals(Event::SEVERITY_SUCCESS, $event->get_severity());
		$this->assertEquals('network', $event->get_object_type());
		$this->assertEquals(0, $event->get_object_id()); // Default value
		$this->assertEquals(0, $event->get_author_id()); // Default value
		$this->assertEquals('minimal-event', $event->get_slug());
		$this->assertEquals('system', $event->get_initiator());
	}
}
