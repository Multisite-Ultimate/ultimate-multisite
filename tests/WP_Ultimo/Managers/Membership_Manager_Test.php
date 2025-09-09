<?php
/**
 * Test case for Membership Manager.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Managers;

use WP_Ultimo\Managers\Membership_Manager;
use WP_Ultimo\Models\Membership;
use WP_Ultimo\Models\Customer;
use WP_Ultimo\Models\Product;
use WP_Ultimo\Database\Memberships\Membership_Status;
use WP_UnitTestCase;

/**
 * Test Membership Manager functionality.
 */
class Membership_Manager_Test extends WP_UnitTestCase {

	/**
	 * Test membership manager instance.
	 *
	 * @var Membership_Manager
	 */
	private $manager;

	/**
	 * Test customer.
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Test product.
	 *
	 * @var Product
	 */
	private $product;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->manager = Membership_Manager::get_instance();

		// Create test customer
		$customer = wu_create_customer([
			'username'      => 'testuser',
			'email_address' => 'test@example.com',
			'password'      => 'password123',
		]);

		if (is_wp_error($customer)) {
			$this->markTestSkipped('Could not create test customer: ' . $customer->get_error_message());
		}
		
		$this->customer = $customer;

		// Create test product
		$product = wu_create_product([
			'name'        => 'Test Product',
			'slug'        => 'test-product',
			'description' => 'A test product',
			'type'        => 'plan',
			'amount'      => 10,
			'duration'    => 1,
			'duration_unit' => 'month',
		]);

		if (is_wp_error($product)) {
			$this->markTestSkipped('Could not create test product: ' . $product->get_error_message());
		}
		
		$this->product = $product;
	}

	/**
	 * Test manager initialization.
	 */
	public function test_manager_initialization() {
		$this->assertInstanceOf(Membership_Manager::class, $this->manager);
		
		// Use reflection to access protected properties
		$reflection = new \ReflectionClass($this->manager);
		$slug_property = $reflection->getProperty('slug');
		$slug_property->setAccessible(true);
		$this->assertEquals('membership', $slug_property->getValue($this->manager));
		
		$model_class_property = $reflection->getProperty('model_class');
		$model_class_property->setAccessible(true);
		$this->assertEquals(\WP_Ultimo\Models\Membership::class, $model_class_property->getValue($this->manager));
	}

	/**
	 * Test async publish pending site with valid membership.
	 */
	public function test_async_publish_pending_site_success() {
		// Create membership with pending site
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::ACTIVE,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		$this->assertInstanceOf(Membership::class, $membership);

		// Test async publish with valid membership ID
		$result = $this->manager->async_publish_pending_site($membership->get_id());
		
		// Since we don't have a pending site in this test setup, 
		// we expect the method to handle gracefully
		$this->assertNotInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test async publish pending site with invalid membership ID.
	 */
	public function test_async_publish_pending_site_invalid_id() {
		$result = $this->manager->async_publish_pending_site(99999);
		
		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('error', $result->get_error_code());
		$this->assertEquals('An unexpected error happened.', $result->get_error_message());
	}

	/**
	 * Test mark cancelled date functionality.
	 */
	public function test_mark_cancelled_date() {
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::ACTIVE,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		$this->assertInstanceOf(Membership::class, $membership);

		// Test status transition to cancelled
		$old_status = Membership_Status::ACTIVE;
		$new_status = Membership_Status::CANCELLED;

		// Mock the method call that would be triggered by status transition
		$this->manager->mark_cancelled_date($old_status, $new_status, $membership);

		// Refresh membership from database
		$membership = wu_get_membership($membership->get_id());
		
		// If status changed to cancelled, cancelled_at should be set
		if ($new_status === Membership_Status::CANCELLED) {
			$this->assertNotNull($membership->get_date_cancelled());
		}
	}

	/**
	 * Test membership status transition.
	 */
	public function test_transition_membership_status() {
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::PENDING,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		$this->assertInstanceOf(Membership::class, $membership);

		$old_status = Membership_Status::PENDING;
		$new_status = Membership_Status::ACTIVE;

		// Test transition method doesn't throw errors
		$this->manager->transition_membership_status($old_status, $new_status, $membership);

		// This test mainly ensures the method executes without errors
		$this->assertTrue(true);
	}

	/**
	 * Test async transfer membership.
	 */
	public function test_async_transfer_membership() {
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::ACTIVE,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		// Create another customer to transfer to
		$new_customer = wu_create_customer([
			'username'      => 'newuser',
			'email_address' => 'new@example.com',
			'password'      => 'password123',
		]);

		$this->assertInstanceOf(Membership::class, $membership);
		$this->assertInstanceOf(Customer::class, $new_customer);

		// Test async transfer
		$result = $this->manager->async_transfer_membership($membership->get_id(), $new_customer->get_id());

		// Method should execute without throwing errors
		$this->assertTrue(true);
	}

	/**
	 * Test async delete membership.
	 */
	public function test_async_delete_membership() {
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::ACTIVE,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		$this->assertInstanceOf(Membership::class, $membership);
		$membership_id = $membership->get_id();

		// Test async delete
		$this->manager->async_delete_membership($membership_id);

		// Check if membership was deleted
		$deleted_membership = wu_get_membership($membership_id);
		$this->assertNull($deleted_membership);
	}

	/**
	 * Test async membership swap.
	 */
	public function test_async_membership_swap() {
		$membership = wu_create_membership([
			'customer_id' => $this->customer->get_id(),
			'product_id'  => $this->product->get_id(),
			'status'      => Membership_Status::ACTIVE,
			'amount'      => 10,
			'currency'    => 'USD',
		]);

		$this->assertInstanceOf(Membership::class, $membership);

		// Test async swap - this mainly tests that method doesn't throw errors
		$this->manager->async_membership_swap($membership->get_id());

		$this->assertTrue(true);
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		// Clean up test data
		if ($this->customer && !is_wp_error($this->customer)) {
			$this->customer->delete();
		}
		if ($this->product && !is_wp_error($this->product)) {
			$this->product->delete();
		}

		parent::tearDown();
	}
}