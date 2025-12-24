<?php
/**
 * Limit_Email_Accounts tests.
 *
 * @package WP_Ultimo\Tests
 */

namespace WP_Ultimo\Limitations;

use WP_UnitTestCase;

/**
 * Test class for Limit_Email_Accounts functionality.
 *
 * Tests email account limit initialization, checking, and slot calculations.
 */
class Limit_Email_Accounts_Test extends WP_UnitTestCase {

	/**
	 * Test limit initialization with enabled and numeric limit.
	 */
	public function test_limit_initialization_enabled_numeric(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertEquals(5, $limit->get_limit());
	}

	/**
	 * Test limit initialization with disabled.
	 */
	public function test_limit_initialization_disabled(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => false,
				'limit'   => 10,
			]
		);

		$this->assertFalse($limit->is_enabled());
	}

	/**
	 * Test limit initialization with zero (unlimited).
	 */
	public function test_limit_initialization_zero_unlimited(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 0,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertEquals(0, $limit->get_limit());
	}

	/**
	 * Test limit initialization with boolean true (unlimited).
	 */
	public function test_limit_initialization_boolean_true(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertTrue($limit->get_limit());
	}

	/**
	 * Test limit initialization with boolean false (none allowed).
	 */
	public function test_limit_initialization_boolean_false(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertFalse($limit->get_limit());
	}

	/**
	 * Test check method with boolean true limit (unlimited).
	 */
	public function test_check_with_boolean_true_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$this->assertTrue($limit->check(0, true));
		$this->assertTrue($limit->check(100, true));
	}

	/**
	 * Test check method with boolean false limit (none allowed).
	 */
	public function test_check_with_boolean_false_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$this->assertFalse($limit->check(0, false));
		$this->assertFalse($limit->check(1, false));
	}

	/**
	 * Test check method with zero limit (unlimited).
	 */
	public function test_check_with_zero_limit_unlimited(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 0,
			]
		);

		$this->assertTrue($limit->check(0, 0));
		$this->assertTrue($limit->check(100, 0));
		$this->assertTrue($limit->check(9999, 0));
	}

	/**
	 * Test check method with numeric limit when under limit.
	 */
	public function test_check_with_numeric_limit_under_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		$this->assertTrue($limit->check(0, 5)); // 0 < 5
		$this->assertTrue($limit->check(3, 5)); // 3 < 5
		$this->assertTrue($limit->check(4, 5)); // 4 < 5
	}

	/**
	 * Test check method with numeric limit when at limit.
	 */
	public function test_check_with_numeric_limit_at_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		$this->assertFalse($limit->check(5, 5)); // 5 is not < 5
	}

	/**
	 * Test check method with numeric limit when over limit.
	 */
	public function test_check_with_numeric_limit_over_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		$this->assertFalse($limit->check(6, 5));
		$this->assertFalse($limit->check(100, 5));
	}

	/**
	 * Test check method when limit is disabled.
	 */
	public function test_check_with_disabled_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => false,
				'limit'   => 5,
			]
		);

		$this->assertFalse($limit->check(0, 5));
		$this->assertFalse($limit->check(3, 5));
	}

	/**
	 * Test can_create_more with unlimited (true).
	 */
	public function test_can_create_more_unlimited_true(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		// With unlimited, should always return true
		$result = $limit->can_create_more(1, 1);
		$this->assertTrue($result);
	}

	/**
	 * Test can_create_more with zero (unlimited).
	 */
	public function test_can_create_more_zero_unlimited(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 0,
			]
		);

		$result = $limit->can_create_more(1, 1);
		$this->assertTrue($result);
	}

	/**
	 * Test can_create_more when disabled.
	 */
	public function test_can_create_more_disabled(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => false,
				'limit'   => 5,
			]
		);

		$result = $limit->can_create_more(1, 1);
		$this->assertFalse($result);
	}

	/**
	 * Test can_create_more with false limit.
	 */
	public function test_can_create_more_false_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$result = $limit->can_create_more(1, 1);
		$this->assertFalse($result);
	}

	/**
	 * Test get_remaining_slots with unlimited (true).
	 */
	public function test_get_remaining_slots_unlimited_true(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$result = $limit->get_remaining_slots(1, 1);
		$this->assertEquals('unlimited', $result);
	}

	/**
	 * Test get_remaining_slots with zero (unlimited).
	 */
	public function test_get_remaining_slots_zero_unlimited(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 0,
			]
		);

		$result = $limit->get_remaining_slots(1, 1);
		$this->assertEquals('unlimited', $result);
	}

	/**
	 * Test get_remaining_slots when disabled.
	 */
	public function test_get_remaining_slots_disabled(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => false,
				'limit'   => 5,
			]
		);

		$result = $limit->get_remaining_slots(1, 1);
		$this->assertEquals(0, $result);
	}

	/**
	 * Test get_remaining_slots with false limit.
	 */
	public function test_get_remaining_slots_false_limit(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$result = $limit->get_remaining_slots(1, 1);
		$this->assertEquals(0, $result);
	}

	/**
	 * Test get_remaining_slots with numeric limit using mock.
	 */
	public function test_get_remaining_slots_numeric(): void {
		$limit_mock = $this->getMockBuilder(Limit_Email_Accounts::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_account_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_account_count')
			->willReturn(2);

		$result = $limit_mock->get_remaining_slots(1, 1);
		$this->assertEquals(3, $result); // 5 - 2 = 3
	}

	/**
	 * Test get_remaining_slots returns zero when over limit.
	 */
	public function test_get_remaining_slots_over_limit(): void {
		$limit_mock = $this->getMockBuilder(Limit_Email_Accounts::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 2,
					],
				]
			)
			->onlyMethods(['get_current_account_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_account_count')
			->willReturn(5);

		$result = $limit_mock->get_remaining_slots(1, 1);
		$this->assertEquals(0, $result); // max(0, 2 - 5) = 0
	}

	/**
	 * Test default state.
	 */
	public function test_default_state(): void {
		$default = Limit_Email_Accounts::default_state();

		$this->assertIsArray($default);
		$this->assertArrayHasKey('enabled', $default);
		$this->assertArrayHasKey('limit', $default);
		$this->assertFalse($default['enabled']);
		$this->assertEquals(0, $default['limit']);
	}

	/**
	 * Test module ID.
	 */
	public function test_module_id(): void {
		$limit = new Limit_Email_Accounts(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Access the protected id property via reflection
		$reflection = new \ReflectionClass($limit);
		$property   = $reflection->getProperty('id');
		$property->setAccessible(true);

		$this->assertEquals('email_accounts', $property->getValue($limit));
	}
}
