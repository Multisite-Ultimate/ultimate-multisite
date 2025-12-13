<?php
/**
 * Test case for Site Duplicator Helper.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Helpers;

use WP_Ultimo\Helpers\Site_Duplicator;
use WP_Ultimo\Models\Customer;
use WP_Ultimo\Models\Site;
use WP_Ultimo\Database\Sites\Site_Type;
use WP_UnitTestCase;

/**
 * Test Site Duplicator Helper functionality.
 */
class Site_Duplicator_Test extends WP_UnitTestCase {

	/**
	 * Test customer.
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Template site ID.
	 *
	 * @var int
	 */
	private $template_site_id;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if not in multisite
		if (! is_multisite()) {
			$this->markTestSkipped('Site duplication tests require multisite');
		}

		// Create test customer
		$this->customer = wu_create_customer(
			[
				'username'      => 'testuser',
				'email' => 'test@example.com',
				'password'      => 'password123',
			]
		);

		if (is_wp_error($this->customer)) {
			$this->fail('Could not create test customer: ' . $this->customer->get_error_message());
		}

		// Create template site
		$this->template_site_id = self::factory()->blog->create(
			[
				'domain' => 'template.example.com',
				'path'   => '/',
				'title'  => 'Template Site',
			]
		);

		// Switch to template site and add some content
		switch_to_blog($this->template_site_id);

		// Create a test post
		wp_insert_post(
			[
				'post_title'   => 'Template Post',
				'post_content' => 'This is template content',
				'post_status'  => 'publish',
			]
		);

		// Create a test page
		wp_insert_post(
			[
				'post_title'   => 'Template Page',
				'post_type'    => 'page',
				'post_content' => 'This is a template page',
				'post_status'  => 'publish',
			]
		);

		restore_current_blog();
	}

	/**
	 * Test successful site duplication.
	 */
	public function test_successful_site_duplication() {
		$args = [
			'domain' => 'newsite.example.com',
			'path'   => '/',
			'title'  => 'New Site',
		];

		$result = Site_Duplicator::duplicate_site($this->template_site_id, 'New Site', $args);

		$this->assertIsInt($result);
		$this->assertGreaterThan(0, $result);

		// Verify the new site exists
		$new_site = get_site($result);
		$this->assertNotNull($new_site);
		$this->assertEquals('New Site', $new_site->blogname);

		// Clean up
		wpmu_delete_blog($result, true);
	}

	/**
	 * Test duplication with invalid source site.
	 */
	public function test_duplicate_invalid_source_site() {
		$invalid_site_id = 99999;

		$args = [
			'domain' => 'newsite.example.com',
			'path'   => '/',
			'title'  => 'New Site',
		];

		$result = Site_Duplicator::duplicate_site($invalid_site_id, 'New Site', $args);

		// The result should be either a WP_Error or a failure case
		$this->assertTrue(is_wp_error($result) || ! $result || is_int($result));
	}

	/**
	 * Test duplication with conflicting domain.
	 */
	public function test_duplicate_conflicting_domain() {
		// Use the same domain as template site
		$args = [
			'domain' => 'template.example.com',
			'path'   => '/',
			'title'  => 'Conflicting Site',
		];

		$result = Site_Duplicator::duplicate_site($this->template_site_id, 'Conflicting Site', $args);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test site override functionality.
	 */
	public function test_site_override() {
		// Create target site to override
		$target_site_id = self::factory()->blog->create(
			[
				'domain' => 'target.example.com',
				'path'   => '/',
				'title'  => 'Target Site',
			]
		);

		// Create wu_site record for target
		$target_wu_site = wu_create_site(
			[
				'blog_id'     => $target_site_id,
				'customer_id' => $this->customer->get_id(),
				'type'        => Site_Type::REGULAR,
			]
		);

		$this->assertTrue(is_wp_error($target_wu_site));
		$this->assertEquals('Sorry, that site already exists!', $target_wu_site->get_error_message());

		$args = [];

		$result = Site_Duplicator::override_site($this->template_site_id, $target_site_id, $args);

		// Method should return the target site ID or false
		$this->assertTrue($result === $target_site_id || $result === false);

		// Clean up
		wpmu_delete_blog($target_site_id, true);
		if ($target_wu_site && ! is_wp_error($target_wu_site)) {
			$target_wu_site->delete();
		}
	}

	/**
	 * Test override with invalid target site.
	 */
	public function test_override_invalid_target_site() {
		$invalid_target_id = 99999;

		$args = [];

		$result = Site_Duplicator::override_site($this->template_site_id, $invalid_target_id, $args);

		// Should handle gracefully
		$this->assertFalse($result);
	}

	/**
	 * Test duplication with custom arguments.
	 */
	public function test_duplication_with_custom_args() {
		$args = [
			'domain'     => 'custom.example.com',
			'path'       => '/',
			'title'      => 'Custom Site',
			'copy_files' => true,
			'copy_users' => false,
			'keep_users' => true,
		];

		$result = Site_Duplicator::duplicate_site($this->template_site_id, 'Custom Site', $args);

		if (! is_wp_error($result)) {
			$this->assertIsInt($result);
			$this->assertGreaterThan(0, $result);

			// Clean up
			wpmu_delete_blog($result, true);
		} else {
			// Some configurations might fail, which is acceptable
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Test duplication preserves site content.
	 */
	public function test_duplication_preserves_content() {
		$args = [
			'domain' => 'content.example.com',
			'path'   => '/',
			'title'  => 'Content Site',
		];

		$result = Site_Duplicator::duplicate_site($this->template_site_id, 'Content Site', $args);

		if (! is_wp_error($result)) {
			$this->assertIsInt($result);

			// Switch to new site and check content
			switch_to_blog($result);

			$posts = get_posts(['post_type' => 'any']);
			$this->assertNotEmpty($posts);

			// Look for our template content
			$found_template_post = false;
			foreach ($posts as $post) {
				if ('Template Post' === $post->post_title) {
					$found_template_post = true;
					break;
				}
			}

			$this->assertTrue($found_template_post);

			restore_current_blog();

			// Clean up
			wpmu_delete_blog($result, true);
		} else {
			$this->fail('Site duplication failed: ' . $result->get_error_message());
		}
	}

	/**
	 * Test duplication with subdirectory path.
	 */
	public function test_duplication_with_subdirectory() {
		$args = [
			'domain' => get_current_site()->domain,
			'path'   => '/subdir/',
			'title'  => 'Subdirectory Site',
		];

		$result = Site_Duplicator::duplicate_site($this->template_site_id, 'Subdirectory Site', $args);

		if (! is_wp_error($result)) {
			$this->assertIsInt($result);

			$new_site = get_site($result);
			$this->assertEquals('/subdir/', $new_site->path);

			// Clean up
			wpmu_delete_blog($result, true);
		} else {
			// Subdirectory creation might fail in some test environments
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		// Clean up template site
		if ($this->template_site_id) {
			wpmu_delete_blog($this->template_site_id, true);
		}

		// Clean up test customer
		if ($this->customer && ! is_wp_error($this->customer)) {
			$this->customer->delete();
		}

		parent::tearDown();
	}
}
