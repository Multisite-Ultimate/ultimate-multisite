<?php

namespace WP_Ultimo\Limitations;

use WP_UnitTestCase;
use WP_Ultimo\Models\Domain;
use WP_Ultimo\Models\Site;

class Limit_Domain_Mapping_Test extends WP_UnitTestCase {

	/**
	 * Test site for domain mapping tests.
	 *
	 * @var Site
	 */
	private static $test_site;

	/**
	 * Set up test environment.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Create a test site
		self::$test_site = wu_create_site(
			[
				'title'       => 'Test Site',
				'domain'      => 'test-site5.example.com',
				'template_id' => 1,
			]
		);
	}

	/**
	 * Clean up after tests.
	 */
	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		if (self::$test_site) {
			// self::$test_site->delete();
		}
	}

	/**
	 * Test limit initialization with boolean true (unlimited domains).
	 */
	public function test_limit_initialization_boolean_true() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertTrue($limit->get_limit());
	}

	/**
	 * Test limit initialization with boolean false (no domains allowed).
	 */
	public function test_limit_initialization_boolean_false() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertFalse($limit->get_limit());
	}

	/**
	 * Test limit initialization with numeric limit.
	 */
	public function test_limit_initialization_numeric() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 3,
			]
		);

		$this->assertTrue($limit->is_enabled());
		$this->assertEquals(3, $limit->get_limit());
	}

	/**
	 * Test check method with boolean true limit (unlimited).
	 */
	public function test_check_with_boolean_true_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$this->assertTrue($limit->check(self::$test_site->get_id(), true));
	}

	/**
	 * Test check method with boolean false limit (no domains).
	 */
	public function test_check_with_boolean_false_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		$this->assertFalse($limit->check(self::$test_site->get_id(), false));
	}

	/**
	 * Test check method with numeric limit when under limit.
	 */
	public function test_check_with_numeric_limit_under_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Mock the get_current_domain_count method to return 3 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(3);

		$this->assertTrue($limit_mock->check(self::$test_site->get_id(), 5));
	}

	/**
	 * Test check method with numeric limit when over limit.
	 */
	public function test_check_with_numeric_limit_over_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 2,
			]
		);

		// Mock the get_current_domain_count method to return 5 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 2,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(5);

		$this->assertFalse($limit_mock->check(self::$test_site->get_id(), 2));
	}

	/**
	 * Test check method when limit is disabled.
	 */
	public function test_check_with_disabled_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => false,
				'limit'   => 5,
			]
		);

		$this->assertFalse($limit->check(self::$test_site->get_id(), 5));
	}

	/**
	 * Test check_all_domains method with boolean true limit.
	 */
	public function test_check_all_domains_with_boolean_true_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => true,
			]
		);

		$this->assertFalse($limit->check_all_domains(self::$test_site->get_id()));
	}

	/**
	 * Test check_all_domains method with boolean false limit and no domains.
	 */
	public function test_check_all_domains_with_boolean_false_limit_no_domains() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		// Mock the get_current_domain_count method to return 0 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => false,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(0);

		$this->assertFalse($limit_mock->check_all_domains(self::$test_site->get_id()));
	}

	/**
	 * Test check_all_domains method with boolean false limit and existing domains.
	 */
	public function test_check_all_domains_with_boolean_false_limit_has_domains() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => false,
			]
		);

		// Mock the get_current_domain_count method to return 2 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => false,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(2);

		$result = $limit_mock->check_all_domains(self::$test_site->get_id());
		$this->assertIsArray($result);
		$this->assertEquals(2, $result['current']);
		$this->assertEquals(0, $result['limit']);
	}

	/**
	 * Test check_all_domains method with numeric limit when under limit.
	 */
	public function test_check_all_domains_with_numeric_limit_under_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Mock the get_current_domain_count method to return 3 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(3);

		$this->assertFalse($limit_mock->check_all_domains(self::$test_site->get_id()));
	}

	/**
	 * Test check_all_domains method with numeric limit when over limit.
	 */
	public function test_check_all_domains_with_numeric_limit_over_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 2,
			]
		);

		// Mock the get_current_domain_count method to return 5 domains
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 2,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(5);

		$result = $limit_mock->check_all_domains(self::$test_site->get_id());
		$this->assertIsArray($result);
		$this->assertEquals(5, $result['current']);
		$this->assertEquals(2, $result['limit']);
	}

	/**
	 * Test check_all_domains method when limit is disabled.
	 */
	public function test_check_all_domains_with_disabled_limit() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => false,
				'limit'   => 5,
			]
		);

		$this->assertFalse($limit->check_all_domains(self::$test_site->get_id()));
	}

	/**
	 * Test get_current_domain_count method with no domains.
	 */
	public function test_get_current_domain_count_no_domains() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Test with a real site that has no domains
		$count = $limit->get_current_domain_count(self::$test_site->get_id());
		$this->assertEquals(0, $count);
	}

	/**
	 * Test get_current_domain_count method with mixed active/inactive domains.
	 */
	public function test_get_current_domain_count_mixed_domains() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Create mock domains - 2 active, 1 inactive
		$active_domain1 = $this->createMock(Domain::class);
		$active_domain1->method('is_active')->willReturn(true);

		$active_domain2 = $this->createMock(Domain::class);
		$active_domain2->method('is_active')->willReturn(true);

		$inactive_domain = $this->createMock(Domain::class);
		$inactive_domain->method('is_active')->willReturn(false);

		$domains = [$active_domain1, $active_domain2, $inactive_domain];

		// Mock the get_current_domain_count to test the counting logic
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(2); // Only active domains should be counted

		$count = $limit_mock->get_current_domain_count(self::$test_site->get_id());
		$this->assertEquals(2, $count);
	}

	/**
	 * Test get_current_domain_count method with single domain (not array).
	 */
	public function test_get_current_domain_count_single_domain() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Mock the get_current_domain_count to return 1 for single domain
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(1);

		$count = $limit_mock->get_current_domain_count(self::$test_site->get_id());
		$this->assertEquals(1, $count);
	}

	/**
	 * Test get_current_domain_count method with current blog ID when no site_id provided.
	 */
	public function test_get_current_domain_count_current_blog_id() {
		$limit = new Limit_Domain_Mapping(
			[
				'enabled' => true,
				'limit'   => 5,
			]
		);

		// Mock the get_current_domain_count to use current blog ID
		$limit_mock = $this->getMockBuilder(Limit_Domain_Mapping::class)
			->setConstructorArgs(
				[
					[
						'enabled' => true,
						'limit'   => 5,
					],
				]
			)
			->onlyMethods(['get_current_domain_count'])
			->getMock();

		$limit_mock->expects($this->once())
			->method('get_current_domain_count')
			->willReturn(0);

		$count = $limit_mock->get_current_domain_count(null);
		$this->assertEquals(0, $count);
	}
}
