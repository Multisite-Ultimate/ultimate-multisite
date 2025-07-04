<?php

namespace WP_Ultimo\Admin_Pages;

use WP_Ajax_UnitTestCase;
use WP_Ultimo\Models\Webhook;
use WP_Ultimo\Faker;

class Webhook_Edit_Admin_PageTest extends WP_Ajax_UnitTestCase {

	/**
	 * Instance of Webhook_Edit_Admin_Page to test.
	 *
	 * @var Webhook_Edit_Admin_Page
	 */
	protected $admin_page;

	/**
	 * Faker instance
	 *
	 * @var Faker
	 */
	protected $faker;

	/**
	 * Sets up the test scenario.
	 */
	public function setUp(): void {
		parent::setUp();

		// Initialize faker
		$this->faker = new Faker();

		// Create the admin page instance
		$this->admin_page = new Webhook_Edit_Admin_Page();

		// Generate a fake webhook using the Faker class
		$this->faker->generate_fake_webhook(1);
	}

	/**
	 * Test output_default_widget_payload outputs valid payload data.
	 */
	public function test_output_default_widget_payload_with_valid_event(): void {
		// Get the generated webhook from faker
		$webhooks = $this->faker->get_fake_data_generated('webhooks');
		$webhook  = $webhooks[0];

		// Set up the method to return our fake webhook
		$this->admin_page = $this->getMockBuilder(Webhook_Edit_Admin_Page::class)
								->setMethods(['get_object'])
								->getMock();
		$this->admin_page->method('get_object')->willReturn($webhook);

		ob_start();
		$this->admin_page->output_default_widget_payload();
		$output = ob_get_clean();

		$this->assertStringContainsString(
			'Loading Payload',
			$output,
			'The payload output does not match the expected JSON data.'
		);
		$this->assertGreaterThan(100, strlen($output), 'The payload output is too short.');
	}

	/**
	 * Test output_default_widget_payload handles missing event payload.
	 */
	public function test_output_default_widget_payload_with_no_payload(): void {
		// Create a webhook with an unknown event
		$webhook = wu_create_webhook(
			[
				'name'        => 'Test Webhook',
				'webhook_url' => 'https://example.com',
				'event'       => 'unknown_event',
				'active'      => true,
			]
		);

		$this->admin_page = $this->getMockBuilder(Webhook_Edit_Admin_Page::class)
								->setMethods(['get_object'])
								->getMock();
		$this->admin_page->method('get_object')->willReturn($webhook);

		ob_start();
		$this->admin_page->output_default_widget_payload();
		$output = ob_get_clean();

		$this->assertStringContainsString('{}', trim($output), 'The payload output for an unknown event should be an empty JSON object.');
	}
}
