<?php

namespace WP_Ultimo\Objects;

use WP_UnitTestCase;
use WP_Ultimo\Objects\Limitations;

class Limitations_Test extends WP_UnitTestCase {

	/**
	 * Clear limitations cache before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		// Clear the static cache using reflection
		$reflection = new \ReflectionClass(Limitations::class);
		$cache_property = $reflection->getProperty('limitations_cache');
		$cache_property->setAccessible(true);
		$cache_property->setValue(null, []);
	}

	/**
	 * Data provider for constructor test scenarios.
	 *
	 * @return array
	 */
	public function constructorDataProvider(): array {
		return [
			'empty_modules' => [
				'modules_data' => [],
				'expected_modules_count' => 0,
			],
			'single_module' => [
				'modules_data' => [
					'plugins' => [
						'enabled' => true,
						'behavior' => 'default',
						'plugins_list' => [],
					],
				],
				'expected_modules_count' => 1,
			],
			'multiple_modules' => [
				'modules_data' => [
					'plugins' => [
						'enabled' => true,
						'behavior' => 'default',
					],
					'themes' => [
						'enabled' => false,
						'behavior' => 'not_available',
					],
					'users' => [
						'enabled' => true,
						'limit' => 10,
					],
				],
				'expected_modules_count' => 3,
			],
			'modules_with_json_string' => [
				'modules_data' => [
					'disk_space' => '{"enabled":true,"limit":1024}',
				],
				'expected_modules_count' => 1,
			],
		];
	}

	/**
	 * Test constructor with various module data.
	 *
	 * @dataProvider constructorDataProvider
	 */
	public function test_constructor(array $modules_data, int $expected_modules_count): void {
		$limitations = new Limitations($modules_data);

		$this->assertInstanceOf(Limitations::class, $limitations);

		// Use reflection to access protected modules property
		$reflection = new \ReflectionClass($limitations);
		$modules_property = $reflection->getProperty('raw_module_data');
		$modules_property->setAccessible(true);
		$modules = $modules_property->getValue($limitations);

		$this->assertCount($expected_modules_count, $modules);
	}

	/**
	 * Data provider for magic getter test scenarios.
	 *
	 * @return array
	 */
	public function magicGetterDataProvider(): array {
		return [
			'existing_module' => [
				'module_name' => 'plugins',
				'should_exist' => true,
			],
			'non_existing_module' => [
				'module_name' => 'non_existent_module',
				'should_exist' => false,
			],
			'themes_module' => [
				'module_name' => 'themes',
				'should_exist' => true,
			],
			'users_module' => [
				'module_name' => 'users',
				'should_exist' => true,
			],
		];
	}

	/**
	 * Test magic getter method.
	 *
	 * @dataProvider magicGetterDataProvider
	 */
	public function test_magic_getter(string $module_name, bool $should_exist): void {
		$limitations = new Limitations();
		$result = $limitations->{$module_name};

		if ($should_exist) {
			$this->assertNotFalse($result);
			$this->assertNotNull($result);
		} else {
			$this->assertFalse($result);
		}
	}

	/**
	 * Test serialization methods.
	 */
	public function test_serialization_methods(): void {
		$modules_data = [
			'plugins' => [
				'enabled' => true,
				'behavior' => 'default',
			],
			'users' => [
				'enabled' => true,
				'limit' => 5,
			],
		];

		$limitations = new Limitations($modules_data);
		
		// Test __serialize
		$serialized = $limitations->__serialize();
		$this->assertIsArray($serialized);
		$this->assertArrayHasKey('plugins', $serialized);
		$this->assertArrayHasKey('users', $serialized);

		// Test __unserialize
		$new_limitations = new Limitations();
		$new_limitations->__unserialize($serialized);

		$this->assertEquals($serialized, $new_limitations->__serialize());
	}

	/**
	 * Data provider for build_modules test scenarios.
	 *
	 * @return array
	 */
	public function buildModulesDataProvider(): array {
		return [
			'valid_modules' => [
				'modules_data' => [
					'plugins' => ['enabled' => true],
					'themes' => ['enabled' => false],
				],
				'expected_count' => 2,
			],
			'empty_modules' => [
				'modules_data' => [],
				'expected_count' => 0,
			],
			'mixed_valid_invalid' => [
				'modules_data' => [
					'plugins' => ['enabled' => true],
					'invalid_module' => ['enabled' => true],
				],
				'expected_count' => 1,
			],
		];
	}

	/**
	 * Test build_modules method.
	 *
	 * @dataProvider buildModulesDataProvider
	 */
	public function test_build_modules(array $modules_data, int $expected_count): void {
		$limitations = new Limitations();
		$result = $limitations->build_modules($modules_data);

		$this->assertInstanceOf(Limitations::class, $result);

		// Use reflection to access protected modules property
		$reflection = new \ReflectionClass($limitations);
		$modules_property = $reflection->getProperty('raw_module_data');
		$modules_property->setAccessible(true);
		$modules = $modules_property->getValue($limitations);

		$this->assertEquals($modules_data, $modules);
	}

	/**
	 * Data provider for build method test scenarios.
	 *
	 * @return array
	 */
	public function buildMethodDataProvider(): array {
		return [
			'valid_module_array' => [
				'data' => ['enabled' => true, 'limit' => 10],
				'module_name' => 'users',
				'should_succeed' => true,
			],
			'valid_module_json' => [
				'data' => '{"enabled":true,"limit":5}',
				'module_name' => 'users',
				'should_succeed' => true,
			],
			'invalid_module' => [
				'data' => ['enabled' => true],
				'module_name' => 'non_existent_module',
				'should_succeed' => false,
			],
			'plugins_module' => [
				'data' => ['enabled' => true, 'behavior' => 'default'],
				'module_name' => 'plugins',
				'should_succeed' => true,
			],
		];
	}

	/**
	 * Test static build method.
	 *
	 * @dataProvider buildMethodDataProvider
	 */
	public function test_build_method($data, string $module_name, bool $should_succeed): void {
		$result = Limitations::build($data, $module_name);

		if ($should_succeed) {
			$this->assertNotFalse($result);
			$this->assertIsObject($result);
		} else {
			$this->assertFalse($result);
		}
	}

	/**
	 * Data provider for exists method test scenarios.
	 *
	 * @return array
	 */
	public function existsMethodDataProvider(): array {
		return [
			'existing_module' => [
				'modules_data' => ['plugins' => ['enabled' => true]],
				'module_name' => 'plugins',
				'should_exist' => true,
			],
			'non_existing_module' => [
				'modules_data' => ['plugins' => ['enabled' => true]],
				'module_name' => 'themes',
				'should_exist' => false,
			],
			'empty_limitations' => [
				'modules_data' => [],
				'module_name' => 'plugins',
				'should_exist' => false,
			],
		];
	}

	/**
	 * Test exists method.
	 *
	 * @dataProvider existsMethodDataProvider
	 */
	public function test_exists_method(array $modules_data, string $module_name, bool $should_exist): void {
		$limitations = new Limitations($modules_data);
		$result      = $limitations->exists($module_name);

		if ($should_exist) {
			$this->assertNotFalse($result);
			$this->assertTrue($result);
		} else {
			$this->assertFalse($result);
		}
	}

	/**
	 * Data provider for has_limitations method test scenarios.
	 *
	 * @return array
	 */
	public function hasLimitationsDataProvider(): array {
		return [
			'no_limitations' => [
				'modules_data' => [],
				'expected' => false,
			],
			'enabled_limitations' => [
				'modules_data' => [
					'users' => ['enabled' => true, 'limit' => 5],
				],
				'expected' => true,
			],
			'multiple_enabled' => [
				'modules_data' => [
					'users' => ['enabled' => true, 'limit' => 5],
					'disk_space' => ['enabled' => true, 'limit' => 1024],
				],
				'expected' => true,
			],
		];
	}

	/**
	 * Test has_limitations method.
	 *
	 * @dataProvider hasLimitationsDataProvider
	 */
	public function test_has_limitations(array $modules_data, bool $expected): void {
		$limitations = new Limitations($modules_data);
		$result = $limitations->has_limitations();

		$this->assertEquals($expected, $result);
	}

	/**
	 * Data provider for is_module_enabled method test scenarios.
	 *
	 * @return array
	 */
	public function isModuleEnabledDataProvider(): array {
		return [
			'enabled_module' => [
				'modules_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'module_name' => 'users',
				'expected' => true,
			],
			'non_existing_module' => [
				'modules_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'module_name' => 'non_existent',
				'expected' => false,
			],
		];
	}

	/**
	 * Test is_module_enabled method.
	 *
	 * @dataProvider isModuleEnabledDataProvider
	 */
	public function test_is_module_enabled(array $modules_data, string $module_name, bool $expected): void {
		$limitations = new Limitations($modules_data);
		$result = $limitations->is_module_enabled($module_name);

		$this->assertEquals($expected, $result);
	}

	/**
	 * Data provider for merge method test scenarios.
	 *
	 * @return array
	 */
	public function mergeMethodDataProvider(): array {
		return [
			'simple_merge_addition' => [
				'base_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'merge_data' => [['users' => ['enabled' => true, 'limit' => 3]]],
				'override' => false,
				'expected_limit' => 8,
			],
			'simple_merge_override' => [
				'base_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'merge_data' => [['users' => ['enabled' => true, 'limit' => 3]]],
				'override' => true,
				'expected_limit' => 3,
			],
			'merge_with_disabled' => [
				'base_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'merge_data' => [['users' => ['enabled' => false, 'limit' => 3]]],
				'override' => false,
				'expected_enabled' => true,
			],
			'merge_unlimited_value' => [
				'base_data' => ['users' => ['enabled' => true, 'limit' => 0]],
				'merge_data' => [['users' => ['enabled' => true, 'limit' => 5]]],
				'override' => false,
				'expected_limit' => 0,
			],
			'merge_multiple_limitations' => [
				'base_data' => ['users' => ['enabled' => true, 'limit' => 5]],
				'merge_data' => [
					['users' => ['enabled' => true, 'limit' => 3]],
					['users' => ['enabled' => true, 'limit' => 2]],
				],
				'override' => false,
				'expected_limit' => 10,
			],
		];
	}

	/**
	 * Test merge method.
	 *
	 * @dataProvider mergeMethodDataProvider
	 */
	public function test_merge_method(array $base_data, array $merge_data, bool $override, $expected_value): void {
		$limitations = new Limitations($base_data);
		
		$result = $limitations->merge($override, ...$merge_data);
		
		$this->assertInstanceOf(Limitations::class, $result);
		
		$result_array = $result->to_array();
		
		if (is_int($expected_value)) {
			$this->assertEquals($expected_value, $result_array['users']['limit']);
		}
	}

	/**
	 * Test merge with Limitations objects.
	 */
	public function test_merge_with_limitations_objects(): void {
		$base_limitations = new Limitations(['users' => ['enabled' => true, 'limit' => 5]]);
		$merge_limitations = new Limitations(['users' => ['enabled' => true, 'limit' => 3]]);
		
		$result = $base_limitations->merge(false, $merge_limitations);
		
		$this->assertInstanceOf(Limitations::class, $result);
		
		$result_array = $result->to_array();
		$this->assertEquals(8, $result_array['users']['limit']);
	}

	/**
	 * Test merge with invalid data.
	 */
	public function test_merge_with_invalid_data(): void {
		$limitations = new Limitations(['users' => ['enabled' => true, 'limit' => 5]]);
		
		$result = $limitations->merge(false, 'invalid_string', null, 123);
		
		$this->assertInstanceOf(Limitations::class, $result);
		
		$result_array = $result->to_array();
		$this->assertEquals(5, $result_array['users']['limit']);
	}

	/**
	 * Test to_array method.
	 */
	public function test_to_array_method(): void {
		$modules_data = [
			'plugins' => ['enabled' => true, 'behavior' => 'default'],
			'users' => ['enabled' => true, 'limit' => 10],
		];
		
		$limitations = new Limitations($modules_data);
		$result = $limitations->to_array();
		
		$this->assertIsArray($result);
		$this->assertArrayHasKey('plugins', $result);
		$this->assertArrayHasKey('users', $result);
	}

	/**
	 * Test early_get_limitations method with mocked database.
	 */
	public function test_early_get_limitations_method(): void {
		global $wpdb;
		
		// Mock wpdb
		$wpdb = $this->createMock(\wpdb::class);
		$wpdb->base_prefix = 'wp_';
		
		$serialized_data = serialize(['users' => ['enabled' => true, 'limit' => 5]]);
		
		$wpdb->expects($this->once())
			->method('prepare')
			->willReturn("SELECT meta_value FROM wp_wu_customermeta WHERE meta_key = 'wu_limitations' AND wu_customer_id = 123 LIMIT 1");
			
		$wpdb->expects($this->once())
			->method('get_var')
			->willReturn($serialized_data);
		
		$result = Limitations::early_get_limitations('customer', 123);
		
		$this->assertIsArray($result);
		$this->assertArrayHasKey('users', $result);
		$this->assertTrue($result['users']['enabled']);
		$this->assertEquals(5, $result['users']['limit']);
	}

	/**
	 * Test early_get_limitations with site slug.
	 */
	public function test_early_get_limitations_with_site_slug(): void {
		global $wpdb;
		
		// Mock wpdb
		$wpdb = $this->createMock(\wpdb::class);
		$wpdb->base_prefix = 'wp_';
		
		$wpdb->expects($this->once())
			->method('prepare')
			->with(
				$this->stringContains('wp_blogmeta'),
				123
			)
			->willReturn("SELECT meta_value FROM wp_blogmeta WHERE meta_key = 'wu_limitations' AND blog_id = 123 LIMIT 1");
			
		$wpdb->expects($this->once())
			->method('get_var')
			->willReturn('');
		
		$result = Limitations::early_get_limitations('site', 123);
		
		$this->assertIsArray($result);
	}

	/**
	 * Test remove_limitations method.
	 */
	public function test_remove_limitations_method(): void {
		global $wpdb;
		
		// Mock wpdb
		$wpdb = $this->createMock(\wpdb::class);
		$wpdb->base_prefix = 'wp_';
		
		$wpdb->expects($this->once())
			->method('prepare')
			->willReturn("DELETE FROM wp_wu_customermeta WHERE meta_key = 'wu_limitations' AND wu_customer_id = 123 LIMIT 1");
			
		$wpdb->expects($this->once())
			->method('get_var');
		
		Limitations::remove_limitations('customer', 123);
		
		// If we reach here without exception, the test passes
		$this->assertTrue(true);
	}

	/**
	 * Test get_empty method.
	 */
	public function test_get_empty_method(): void {
		$result = Limitations::get_empty();
		
		$this->assertInstanceOf(Limitations::class, $result);
		
		// Verify it has access to all repository modules
		$repository = Limitations::repository();
		
		foreach (array_keys($repository) as $module_name) {
			$module = $result->{$module_name};
			$this->assertNotFalse($module, "Module {$module_name} should be accessible");
		}
	}

	/**
	 * Test repository method.
	 */
	public function test_repository_method(): void {
		$repository = Limitations::repository();
		
		$this->assertIsArray($repository);
		$this->assertNotEmpty($repository);
		
		// Check for expected standard modules
		$expected_modules = [
			'post_types',
			'plugins',
			'sites',
			'themes',
			'visits',
			'disk_space',
			'users',
			'site_templates',
			'domain_mapping',
			'customer_user_role',
		];
		
		foreach ($expected_modules as $module) {
			$this->assertArrayHasKey($module, $repository);
			$this->assertIsString($repository[$module]);
		}
	}

	/**
	 * Test merge_recursive protected method using reflection.
	 */
	public function test_merge_recursive_method(): void {
		$limitations = new Limitations();
		
		// Use reflection to access protected method
		$reflection = new \ReflectionClass($limitations);
		$method = $reflection->getMethod('merge_recursive');
		$method->setAccessible(true);
		
		$array1 = [
			'enabled' => true,
			'limit' => 5,
			'nested' => [
				'value' => 10,
			],
		];
		
		$array2 = [
			'enabled' => true,
			'limit' => 3,
			'nested' => [
				'value' => 5,
			],
		];
		
		$method->invokeArgs($limitations, [&$array1, &$array2, true]);
		
		$this->assertEquals(8, $array1['limit']);
		$this->assertEquals(15, $array1['nested']['value']);
	}

	/**
	 * Test merge_recursive with force enabled modules.
	 */
	public function test_merge_recursive_force_enabled(): void {
		$limitations = new Limitations();
		
		// Set current_merge_id to test force enabled logic
		$reflection = new \ReflectionClass($limitations);
		$property = $reflection->getProperty('current_merge_id');
		$property->setAccessible(true);
		$property->setValue($limitations, 'plugins');
		
		$method = $reflection->getMethod('merge_recursive');
		$method->setAccessible(true);
		
		$array1 = ['enabled' => false];
		$array2 = ['enabled' => false];
		
		$method->invokeArgs($limitations, [&$array1, &$array2, true]);
		
		$this->assertTrue($array1['enabled']);
	}

	/**
	 * Test merge_recursive with visibility priority.
	 */
	public function test_merge_recursive_visibility_priority(): void {
		$limitations = new Limitations();
		
		$reflection = new \ReflectionClass($limitations);
		$method = $reflection->getMethod('merge_recursive');
		$method->setAccessible(true);
		
		$array1 = ['enabled' => true, 'visibility' => 'hidden'];
		$array2 = ['enabled' => true, 'visibility' => 'visible'];
		
		$method->invokeArgs($limitations, [&$array1, &$array2, true]);
		
		$this->assertEquals('visible', $array1['visibility']);
	}

	/**
	 * Test merge_recursive with behavior priority.
	 */
	public function test_merge_recursive_behavior_priority(): void {
		$limitations = new Limitations();
		
		// Set current_merge_id to plugins for behavior testing
		$reflection = new \ReflectionClass($limitations);
		$property = $reflection->getProperty('current_merge_id');
		$property->setAccessible(true);
		$property->setValue($limitations, 'plugins');
		
		$method = $reflection->getMethod('merge_recursive');
		$method->setAccessible(true);
		
		$array1 = ['enabled' => true, 'behavior' => 'default'];
		$array2 = ['enabled' => true, 'behavior' => 'force_active'];
		
		$method->invokeArgs($limitations, [&$array1, &$array2, true]);
		
		$this->assertEquals('force_active', $array1['behavior']);
	}

	/**
	 * Test caching in early_get_limitations.
	 */
	public function test_early_get_limitations_caching(): void {
		global $wpdb;
		
		// Mock wpdb
		$wpdb = $this->createMock(\wpdb::class);
		$wpdb->base_prefix = 'wp_';
		
		$serialized_data = serialize(['users' => ['enabled' => true]]);
		
		$wpdb->expects($this->once()) // Should only be called once due to caching
			->method('prepare')
			->willReturn("SELECT meta_value FROM wp_wu_customermeta WHERE meta_key = 'wu_limitations' AND wu_customer_id = 123 LIMIT 1");
			
		$wpdb->expects($this->once()) // Should only be called once due to caching
			->method('get_var')
			->willReturn($serialized_data);
		
		// First call
		$result1 = Limitations::early_get_limitations('customer', 123);
		
		// Second call should use cache
		$result2 = Limitations::early_get_limitations('customer', 123);
		
		$this->assertEquals($result1, $result2);
	}
}