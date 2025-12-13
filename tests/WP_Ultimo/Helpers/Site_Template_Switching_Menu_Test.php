<?php
// phpcs:ignoreFile WordPress.Files.FileName
/**
 * Test case for Template Switching with Classic Menu Preservation.
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
 * Test Template Switching with focus on classic WordPress menu preservation.
 *
 * This test class addresses issues where menu items go missing
 * after switching templates between sites.
 */
class Site_Template_Switching_Menu_Test extends WP_UnitTestCase {

	/**
	 * Test customer.
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Test product.
	 *
	 * @var \WP_Ultimo\Models\Product
	 */
	private $product;

	/**
	 * Test membership.
	 *
	 * @var \WP_Ultimo\Models\Membership
	 */
	private $membership;

	/**
	 * Template A site ID.
	 *
	 * @var int
	 */
	private $template_a_id;

	/**
	 * Template B site ID.
	 *
	 * @var int
	 */
	private $template_b_id;

	/**
	 * Customer site ID.
	 *
	 * @var int
	 */
	private $customer_site_id;

	/**
	 * Track created sites for cleanup.
	 *
	 * @var array
	 */
	private $created_sites = [];

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Skip if not in multisite
		if (! is_multisite()) {
			$this->markTestSkipped('Template switching tests require multisite');
		}

		// Create test customer
		$this->customer = wu_create_customer(
			[
				'username' => 'menutestuser',
				'email'    => 'menutest@example.com',
				'password' => 'password123',
			]
		);

		if (is_wp_error($this->customer)) {
			$this->markTestSkipped('Could not create test customer: ' . $this->customer->get_error_message());
		}

		// Create test product
		$this->product = wu_create_product(
			[
				'name'              => 'Test Product',
				'amount'            => 10,
				'duration'          => 1,
				'duration_unit'     => 'month',
				'billing_frequency' => 1,
				'pricing_type'      => 'paid',
				'type'              => 'plan',
				'active'            => true,
			]
		);

		if (is_wp_error($this->product)) {
			$this->markTestSkipped('Could not create test product: ' . $this->product->get_error_message());
		}

		// Create test membership
		$this->membership = wu_create_membership(
			[
				'customer_id'            => $this->customer->get_id(),
				'user_id'                => $this->customer->get_user_id(),
				'plan_id'                => $this->product->get_id(),
				'amount'                 => $this->product->get_amount(),
				'billing_frequency'      => 1,
				'billing_frequency_unit' => 'month',
				'auto_renew'             => true,
			]
		);

		if (is_wp_error($this->membership)) {
			$this->markTestSkipped('Could not create test membership: ' . $this->membership->get_error_message());
		}

		// Create Template A with menus
		$this->template_a_id   = $this->create_template_with_menus('Template A', 'template-a');
		$this->created_sites[] = $this->template_a_id;

		// Create Template B with menus
		$this->template_b_id   = $this->create_template_with_menus('Template B', 'template-b');
		$this->created_sites[] = $this->template_b_id;

		// Create customer site based on Template A
		$this->customer_site_id = $this->create_customer_site_from_template($this->template_a_id);
		$this->created_sites[]  = $this->customer_site_id;
	}

	/**
	 * Create a template site with sample menus.
	 *
	 * @param string $title Template site title.
	 * @param string $slug  Template site slug.
	 * @return int Site ID.
	 */
	private function create_template_with_menus(string $title, string $slug): int {
		// Create template site
		$site_id = self::factory()->blog->create(
			[
				'domain' => $slug . '.example.com',
				'path'   => '/',
				'title'  => $title,
			]
		);

		// Switch to template site
		switch_to_blog($site_id);

		// Create some pages to use in menus
		$home_page = wp_insert_post(
			[
				'post_title'  => $title . ' Home',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$about_page = wp_insert_post(
			[
				'post_title'  => $title . ' About',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$services_page = wp_insert_post(
			[
				'post_title'  => $title . ' Services',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$service_sub_page = wp_insert_post(
			[
				'post_title'  => $title . ' Service Details',
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_parent' => $services_page,
			]
		);

		$contact_page = wp_insert_post(
			[
				'post_title'  => $title . ' Contact',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// Create primary menu
		$primary_menu_id = wp_create_nav_menu($title . ' Primary Menu');

		// Add menu items to primary menu
		wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'     => $title . ' Home',
				'menu-item-object-id' => $home_page,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			]
		);

		wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'     => $title . ' About',
				'menu-item-object-id' => $about_page,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			]
		);

		$services_menu_item_id = wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'     => $title . ' Services',
				'menu-item-object-id' => $services_page,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			]
		);

		// Add sub-menu item under Services
		wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'     => $title . ' Service Details',
				'menu-item-object-id' => $service_sub_page,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $services_menu_item_id,
				'menu-item-status'    => 'publish',
			]
		);

		wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'     => $title . ' Contact',
				'menu-item-object-id' => $contact_page,
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			]
		);

		// Create a custom link menu item
		wp_update_nav_menu_item(
			$primary_menu_id,
			0,
			[
				'menu-item-title'  => $title . ' External Link',
				'menu-item-url'    => 'https://example.com/' . $slug,
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			]
		);

		// Create footer menu
		$footer_menu_id = wp_create_nav_menu($title . ' Footer Menu');

		// Add items to footer menu
		wp_update_nav_menu_item(
			$footer_menu_id,
			0,
			[
				'menu-item-title'  => $title . ' Privacy Policy',
				'menu-item-url'    => 'https://example.com/' . $slug . '/privacy',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			]
		);

		wp_update_nav_menu_item(
			$footer_menu_id,
			0,
			[
				'menu-item-title'  => $title . ' Terms of Service',
				'menu-item-url'    => 'https://example.com/' . $slug . '/terms',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			]
		);

		// Assign menus to locations (if theme supports them)
		$locations = [
			'primary' => $primary_menu_id,
			'footer'  => $footer_menu_id,
		];
		set_theme_mod('nav_menu_locations', $locations);

		restore_current_blog();

		return $site_id;
	}

	/**
	 * Create a customer site from a template.
	 *
	 * @param int $template_id Template site ID.
	 * @return int Customer site ID.
	 */
	private function create_customer_site_from_template(int $template_id): int {
		$args = [
			'domain'     => 'customer-menu-site.example.com',
			'path'       => '/',
			'title'      => 'Customer Menu Site',
			'copy_files' => true,
		];

		$site_id = Site_Duplicator::duplicate_site($template_id, 'Customer Menu Site', $args);

		if (is_wp_error($site_id)) {
			$this->markTestSkipped('Could not create customer site: ' . $site_id->get_error_message());
		}

		// Try to get existing wu_site record
		$existing_sites = wu_get_sites(
			[
				'blog_id' => $site_id,
				'number'  => 1,
			]
		);

		if (empty($existing_sites)) {
			// Create wu_site record if it doesn't exist
			$wu_site = wu_create_site(
				[
					'blog_id'       => $site_id,
					'customer_id'   => $this->customer->get_id(),
					'membership_id' => $this->membership->get_id(),
					'type'          => Site_Type::REGULAR,
				]
			);

			if (is_wp_error($wu_site)) {
				$this->markTestSkipped('Could not create wu_site record: ' . $wu_site->get_error_message());
			}
		} else {
			// Update with customer_id and membership_id if needed
			$wu_site = $existing_sites[0];
			$wu_site->set_customer_id($this->customer->get_id());
			$wu_site->set_membership_id($this->membership->get_id());
			$wu_site->save();
		}

		return $site_id;
	}

	/**
	 * Test that menus are copied on initial site creation.
	 */
	public function test_menus_copied_on_initial_site_creation() {
		switch_to_blog($this->customer_site_id);

		// Get all menus
		$menus = wp_get_nav_menus();

		$this->assertNotEmpty($menus, 'Customer site should have menus');
		$this->assertGreaterThanOrEqual(2, count($menus), 'Customer site should have at least 2 menus (primary and footer)');

		// Verify menu names
		$menu_names = array_map(
			function($menu) {
				return $menu->name;
			},
			$menus
		);

		$this->assertContains('Template A Primary Menu', $menu_names, 'Primary menu should exist');
		$this->assertContains('Template A Footer Menu', $menu_names, 'Footer menu should exist');

		restore_current_blog();
	}

	/**
	 * Test that menu items are preserved on initial site creation.
	 */
	public function test_menu_items_preserved_on_initial_creation() {
		switch_to_blog($this->customer_site_id);

		// Find the primary menu
		$menus       = wp_get_nav_menus();
		$primary_menu = null;

		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		$this->assertNotNull($primary_menu, 'Primary menu should exist');

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			$this->assertNotEmpty($menu_items, 'Primary menu should have items');
			$this->assertGreaterThanOrEqual(5, count($menu_items), 'Primary menu should have at least 5 items');

			// Verify specific menu items exist
			$menu_titles = array_map(
				function($item) {
					return $item->title;
				},
				$menu_items
			);

			$this->assertContains('Template A Home', $menu_titles, 'Home menu item should exist');
			$this->assertContains('Template A About', $menu_titles, 'About menu item should exist');
			$this->assertContains('Template A Services', $menu_titles, 'Services menu item should exist');
			$this->assertContains('Template A Contact', $menu_titles, 'Contact menu item should exist');
			$this->assertContains('Template A External Link', $menu_titles, 'Custom link menu item should exist');
		}

		restore_current_blog();
	}

	/**
	 * Test that menu hierarchy (parent/child) is preserved.
	 */
	public function test_menu_hierarchy_preserved() {
		switch_to_blog($this->customer_site_id);

		// Find the primary menu
		$menus       = wp_get_nav_menus();
		$primary_menu = null;

		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		$this->assertNotNull($primary_menu, 'Primary menu should exist');

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			// Find parent and child items
			$parent_item = null;
			$child_item  = null;

			foreach ($menu_items as $item) {
				if ('Template A Services' === $item->title) {
					$parent_item = $item;
				}
				if ('Template A Service Details' === $item->title) {
					$child_item = $item;
				}
			}

			$this->assertNotNull($parent_item, 'Parent menu item (Services) should exist');
			$this->assertNotNull($child_item, 'Child menu item (Service Details) should exist');

			if ($parent_item && $child_item) {
				// Verify parent-child relationship
				$this->assertEquals(
					$parent_item->ID,
					$child_item->menu_item_parent,
					'Child menu item should have correct parent'
				);
			}
		}

		restore_current_blog();
	}

	/**
	 * Test that menu locations are preserved.
	 */
	public function test_menu_locations_preserved() {
		switch_to_blog($this->customer_site_id);

		// Get menu locations
		$locations = get_theme_mod('nav_menu_locations');

		$this->assertNotEmpty($locations, 'Menu locations should be set');
		$this->assertArrayHasKey('primary', $locations, 'Primary menu location should be set');
		$this->assertArrayHasKey('footer', $locations, 'Footer menu location should be set');

		// Verify menus are assigned to correct locations
		if (isset($locations['primary'])) {
			$primary_menu = wp_get_nav_menu_object($locations['primary']);
			$this->assertNotFalse($primary_menu, 'Primary menu location should have a valid menu');

			if ($primary_menu) {
				$this->assertStringContainsString('Primary Menu', $primary_menu->name, 'Correct menu should be in primary location');
			}
		}

		if (isset($locations['footer'])) {
			$footer_menu = wp_get_nav_menu_object($locations['footer']);
			$this->assertNotFalse($footer_menu, 'Footer menu location should have a valid menu');

			if ($footer_menu) {
				$this->assertStringContainsString('Footer Menu', $footer_menu->name, 'Correct menu should be in footer location');
			}
		}

		restore_current_blog();
	}

	/**
	 * Test that menus are correctly updated when switching templates.
	 */
	public function test_menus_updated_during_template_switch() {
		// Switch to Template B
		$result = Site_Duplicator::override_site($this->template_b_id, $this->customer_site_id, ['copy_files' => true]);

		$this->assertEquals($this->customer_site_id, $result, 'Template switch should succeed');

		// Verify Template B menus are now on customer site
		switch_to_blog($this->customer_site_id);

		// Get all menus
		$menus = wp_get_nav_menus();

		$this->assertNotEmpty($menus, 'Menus should exist after template switch');

		// Verify menu names contain Template B
		$menu_names = array_map(
			function($menu) {
				return $menu->name;
			},
			$menus
		);

		$has_template_b_menu = false;
		foreach ($menu_names as $name) {
			if (false !== strpos($name, 'Template B')) {
				$has_template_b_menu = true;
				break;
			}
		}

		$this->assertTrue($has_template_b_menu, 'Should have Template B menus after switch');

		// Verify Template A menus are replaced (not duplicated)
		$has_template_a_menu = false;
		foreach ($menu_names as $name) {
			if (false !== strpos($name, 'Template A')) {
				$has_template_a_menu = true;
				break;
			}
		}

		$this->assertFalse($has_template_a_menu, 'Template A menus should be replaced, not kept');

		restore_current_blog();
	}

	/**
	 * Test that menu items are correctly updated during template switch.
	 */
	public function test_menu_items_updated_during_template_switch() {
		// Switch to Template B
		Site_Duplicator::override_site($this->template_b_id, $this->customer_site_id, ['copy_files' => true]);

		switch_to_blog($this->customer_site_id);

		// Find the primary menu
		$menus       = wp_get_nav_menus();
		$primary_menu = null;

		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		$this->assertNotNull($primary_menu, 'Primary menu should exist after switch');

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			$this->assertNotEmpty($menu_items, 'Primary menu should have items after switch');

			// Verify menu items are from Template B
			$menu_titles = array_map(
				function($item) {
					return $item->title;
				},
				$menu_items
			);

			$has_template_b_items = false;
			foreach ($menu_titles as $title) {
				if (false !== strpos($title, 'Template B')) {
					$has_template_b_items = true;
					break;
				}
			}

			$this->assertTrue($has_template_b_items, 'Menu items should be from Template B');

			// Verify Template A menu items are gone
			$has_template_a_items = false;
			foreach ($menu_titles as $title) {
				if (false !== strpos($title, 'Template A')) {
					$has_template_a_items = true;
					break;
				}
			}

			$this->assertFalse($has_template_a_items, 'Template A menu items should be replaced');
		}

		restore_current_blog();
	}

	/**
	 * Test that custom link menu items work after template switch.
	 */
	public function test_custom_link_menu_items_preserved() {
		switch_to_blog($this->customer_site_id);

		// Find the primary menu
		$menus       = wp_get_nav_menus();
		$primary_menu = null;

		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			// Find custom link items
			$custom_link_items = array_filter(
				$menu_items,
				function($item) {
					return 'custom' === $item->type;
				}
			);

			$this->assertNotEmpty($custom_link_items, 'Should have custom link menu items');

			// Verify custom links have valid URLs
			foreach ($custom_link_items as $item) {
				$this->assertNotEmpty($item->url, 'Custom link should have URL');
				$this->assertStringStartsWith('http', $item->url, 'Custom link URL should be valid');
			}
		}

		restore_current_blog();
	}

	/**
	 * Test that page menu items reference correct pages after switch.
	 */
	public function test_page_menu_items_reference_correct_pages() {
		switch_to_blog($this->customer_site_id);

		// Find the primary menu
		$menus       = wp_get_nav_menus();
		$primary_menu = null;

		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			// Find page-type menu items
			$page_items = array_filter(
				$menu_items,
				function($item) {
					return 'post_type' === $item->type && 'page' === $item->object;
				}
			);

			$this->assertNotEmpty($page_items, 'Should have page menu items');

			// Verify each page reference is valid
			foreach ($page_items as $item) {
				$page = get_post($item->object_id);

				$this->assertNotNull($page, "Menu item '{$item->title}' should reference a valid page");

				if ($page) {
					$this->assertEquals('page', $page->post_type, 'Referenced object should be a page');
					$this->assertEquals('publish', $page->post_status, 'Referenced page should be published');
				}
			}
		}

		restore_current_blog();
	}

	/**
	 * Test that multiple template switches preserve menu structure.
	 */
	public function test_multiple_template_switches_preserve_menu_structure() {
		// Perform multiple switches
		Site_Duplicator::override_site($this->template_b_id, $this->customer_site_id, ['copy_files' => true]);
		Site_Duplicator::override_site($this->template_a_id, $this->customer_site_id, ['copy_files' => true]);
		Site_Duplicator::override_site($this->template_b_id, $this->customer_site_id, ['copy_files' => true]);

		switch_to_blog($this->customer_site_id);

		// Verify menus still exist and are correct (should be Template B after final switch)
		$menus = wp_get_nav_menus();

		$this->assertNotEmpty($menus, 'Menus should exist after multiple switches');

		// Find primary menu
		$primary_menu = null;
		foreach ($menus as $menu) {
			if (false !== strpos($menu->name, 'Primary Menu')) {
				$primary_menu = $menu;
				break;
			}
		}

		$this->assertNotNull($primary_menu, 'Primary menu should exist after multiple switches');

		if ($primary_menu) {
			// Get menu items
			$menu_items = wp_get_nav_menu_items($primary_menu->term_id);

			$this->assertNotEmpty($menu_items, 'Menu items should exist after multiple switches');
			$this->assertGreaterThanOrEqual(5, count($menu_items), 'Should have complete menu structure');

			// Verify hierarchy still works
			$has_parent_child = false;
			foreach ($menu_items as $item) {
				if ($item->menu_item_parent > 0) {
					$has_parent_child = true;
					break;
				}
			}

			$this->assertTrue($has_parent_child, 'Menu hierarchy should be preserved after multiple switches');
		}

		restore_current_blog();
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		// Clean up all created sites
		foreach ($this->created_sites as $site_id) {
			if ($site_id) {
				wpmu_delete_blog($site_id, true);
			}
		}

		// Clean up test membership
		if ($this->membership && ! is_wp_error($this->membership)) {
			$this->membership->delete();
		}

		// Clean up test product
		if ($this->product && ! is_wp_error($this->product)) {
			$this->product->delete();
		}

		// Clean up test customer
		if ($this->customer && ! is_wp_error($this->customer)) {
			$this->customer->delete();
		}

		parent::tearDown();
	}
}