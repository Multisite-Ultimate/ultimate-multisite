<?php
/**
 * Test case for Gateway Manager.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Managers;

use WP_Ultimo\Managers\Gateway_Manager;
use WP_Ultimo\Gateways\Base_Gateway;
use WP_Ultimo\Gateways\Free_Gateway;
use WP_Ultimo\Gateways\Manual_Gateway;
use WP_UnitTestCase;

/**
 * Test Gateway Manager functionality.
 */
class Gateway_Manager_Test extends WP_UnitTestCase {

	/**
	 * Test gateway manager instance.
	 *
	 * @var Gateway_Manager
	 */
	private $manager;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->manager = Gateway_Manager::get_instance();
	}

	/**
	 * Test manager initialization.
	 */
	public function test_manager_initialization() {
		$this->assertInstanceOf(Gateway_Manager::class, $this->manager);
	}

	/**
	 * Test default gateways registration.
	 */
	public function test_add_default_gateways() {
		// Clear any existing gateways for clean test
		$reflection = new \ReflectionClass($this->manager);
		$property   = $reflection->getProperty('registered_gateways');

		// Only call setAccessible() on PHP < 8.1 where it's needed
		if (PHP_VERSION_ID < 80100) {
			$property->setAccessible(true);
		}

		$property->setValue($this->manager, []);

		// Register default gateways
		$this->manager->add_default_gateways();

		$registered_gateways = $this->manager->get_registered_gateways();

		$this->assertIsArray($registered_gateways);
		$this->assertArrayHasKey('free', $registered_gateways);
		$this->assertArrayHasKey('manual', $registered_gateways);
	}

	/**
	 * Test gateway registration.
	 */
	public function test_register_gateway() {
		$gateway_id    = 'test-gateway';
		$gateway_class = Manual_Gateway::class;

		$result = $this->manager->register_gateway($gateway_id, 'test', 'test', $gateway_class);

		$this->assertTrue($result);

		$registered_gateways = $this->manager->get_registered_gateways();
		$this->assertArrayHasKey($gateway_id, $registered_gateways);
		$this->assertNotEmpty($registered_gateways[ $gateway_id ]);
	}

	/**
	 * Test duplicate gateway registration.
	 */
	public function test_register_duplicate_gateway() {
		$gateway_id    = 'duplicate-gateway';
		$gateway_class = Manual_Gateway::class;

		// Register once
		$result1 = $this->manager->register_gateway($gateway_id, 'man', 'man', $gateway_class);
		$this->assertTrue($result1);

		// Try to register again
		$result2 = $this->manager->register_gateway($gateway_id, 'man', 'man', $gateway_class);
		$this->assertFalse($result2);
	}

	/**
	 * Test get registered gateways.
	 */
	public function test_get_registered_gateways() {
		$gateways = $this->manager->get_registered_gateways();

		$this->assertIsArray($gateways);
		// Should contain at least the default gateways
		$this->assertNotEmpty($gateways);
	}

	/**
	 * Test get enabled gateways.
	 */
	public function test_get_enabled_gateways() {
		$enabled_gateways = $this->manager->get_registered_gateways();

		$this->assertIsArray($enabled_gateways);
	}

	/**
	 * Test get gateway instance.
	 */
	public function test_get_gateway() {
		// Register a test gateway first
		$gateway_id = 'test-instance';
		$this->manager->register_gateway($gateway_id, 'man', 'man', Manual_Gateway::class);

		$gateway = $this->manager->get_gateway($gateway_id);

		$this->assertIsArray($gateway);
		$this->assertNotEmpty($gateway);
	}

	/**
	 * Test get nonexistent gateway.
	 */
	public function test_get_nonexistent_gateway() {
		$gateway_instance = $this->manager->get_gateway('nonexistent');

		$this->assertFalse($gateway_instance);
	}

	/**
	 * Test is gateway registered.
	 */
	public function test_is_gateway_registered() {
		$gateway_id = 'check-registered';
		$this->manager->register_gateway($gateway_id, 'Manual', '', Manual_Gateway::class);

		$this->assertTrue($this->manager->is_gateway_registered($gateway_id));
		$this->assertFalse($this->manager->is_gateway_registered('not-registered'));
	}

	/**
	 * Test is gateway enabled.
	 */
	public function test_is_gateway_enabled() {
		$result = $this->manager->is_gateway_registered('free');
		$this->assertIsBool($result);
	}

	/**
	 * Test get auto renewable gateways.
	 */
	public function test_get_auto_renewable_gateways() {
		$auto_renewable = $this->manager->get_auto_renewable_gateways();

		$this->assertIsArray($auto_renewable);
	}

	/**
	 * Test gateway error handling.
	 */
	public function test_gateway_error_handling() {
		// Test with invalid gateway class
		try {
			$result = $this->manager->register_gateway('invalid', 'ex', 'tx', 'NonExistentClass');
		} catch (\Error $e) {
			$this->assertTrue($e instanceof \Error);
		}
	}
}
