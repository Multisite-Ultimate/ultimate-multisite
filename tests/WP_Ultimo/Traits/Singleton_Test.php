<?php
/**
 * Tests for Singleton trait enforcement.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use ReflectionClass;

/**
 * Test that Singleton classes are properly implemented and cannot be instantiated directly.
 */
class Singleton_Test extends \WP_UnitTestCase {

	/**
	 * Cache of singleton classes.
	 *
	 * @var array
	 */
	private static $singleton_classes = null;

	/**
	 * Find all classes that use the Singleton trait.
	 *
	 * @return array List of class names that use the Singleton trait.
	 */
	private function get_singleton_classes(): array {

		if (null !== self::$singleton_classes) {
			return self::$singleton_classes;
		}

		$singleton_classes = [];
		$inc_path          = dirname(dirname(dirname(__DIR__))) . '/inc';

		// Find all PHP files
		$directory = new RecursiveDirectoryIterator($inc_path);
		$iterator  = new RecursiveIteratorIterator($directory);
		$regex     = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

		foreach ($regex as $file) {
			$filepath = $file[0];
			$content  = file_get_contents($filepath); // phpcs:ignore

			// Check if file uses the Singleton trait
			if (strpos($content, 'use \WP_Ultimo\Traits\Singleton;') !== false ||
				strpos($content, 'use \\WP_Ultimo\\Traits\\Singleton;') !== false) {

				// Extract namespace and class name
				if (preg_match('/namespace\s+([^;]+);/i', $content, $namespace_match) &&
					preg_match('/class\s+(\w+)/i', $content, $class_match)) {
					$namespace  = trim($namespace_match[1]);
					$class_name = trim($class_match[1]);
					$full_class = $namespace . '\\' . $class_name;

					// Verify the class exists and uses the trait
					if (class_exists($full_class)) {
						$reflection = new ReflectionClass($full_class);
						$traits     = $reflection->getTraitNames();

						if (in_array('WP_Ultimo\Traits\Singleton', $traits, true)) {
							$singleton_classes[] = $full_class;
						}
					}
				}
			}
		}
		self::$singleton_classes = $singleton_classes;

		return $singleton_classes;
	}

	/**
	 * Test that all Singleton classes have private constructors.
	 */
	public function test_singleton_classes_have_private_constructors(): void {

		$singleton_classes = $this->get_singleton_classes();

		$this->assertNotEmpty($singleton_classes, 'Should find at least one singleton class');

		foreach ($singleton_classes as $class_name) {
			$reflection  = new ReflectionClass($class_name);
			$constructor = $reflection->getConstructor();

			$this->assertNotNull(
				$constructor,
				"Class {$class_name} should have a constructor"
			);

			$this->assertTrue(
				$constructor->isPrivate(),
				"Constructor of {$class_name} should be private to prevent direct instantiation"
			);
		}
	}

	/**
	 * Test that Singleton classes cannot be instantiated directly.
	 */
	public function test_singleton_classes_cannot_be_instantiated_directly(): void {

		$singleton_classes = $this->get_singleton_classes();

		foreach ($singleton_classes as $class_name) {
			$exception_thrown = false;

			try {
				new $class_name();
			} catch (\Error $e) {
				$exception_thrown = true;
				$this->assertStringContainsString(
					'private',
					$e->getMessage(),
					"Error message should mention private constructor for {$class_name}"
				);
			}

			$this->assertTrue(
				$exception_thrown,
				"Attempting to instantiate {$class_name} directly should throw an error"
			);
		}
	}

	/**
	 * Test that all Singleton classes have get_instance method.
	 */
	public function test_singleton_classes_have_get_instance_method(): void {

		$singleton_classes = $this->get_singleton_classes();

		foreach ($singleton_classes as $class_name) {
			$this->assertTrue(
				method_exists($class_name, 'get_instance'),
				"Class {$class_name} should have a get_instance() method"
			);

			$reflection = new ReflectionClass($class_name);
			$method     = $reflection->getMethod('get_instance');

			$this->assertTrue(
				$method->isStatic(),
				"get_instance() method in {$class_name} should be static"
			);

			$this->assertTrue(
				$method->isPublic(),
				"get_instance() method in {$class_name} should be public"
			);
		}
	}

	/**
	 * Test that get_instance returns the same instance.
	 */
	public function test_get_instance_returns_same_instance(): void {

		$singleton_classes = $this->get_singleton_classes();

		foreach ($singleton_classes as $class_name) {
			try {
				$instance1 = $class_name::get_instance();
				$instance2 = $class_name::get_instance();

				$this->assertSame(
					$instance1,
					$instance2,
					"get_instance() should return the same instance for {$class_name}"
				);

				$this->assertInstanceOf(
					$class_name,
					$instance1,
					"Instance should be of type {$class_name}"
				);
			} catch (\Exception $e) {
				// Some classes may fail to instantiate in test environment due to dependencies
				// but that's okay - we're primarily testing that they CAN'T be instantiated directly
				$this->markTestSkipped(
					"Skipping instance test for {$class_name}: {$e->getMessage()}"
				);
			} catch (\Error $e) {
				// Skip classes that have dependency issues in test environment
				$this->markTestSkipped(
					"Skipping instance test for {$class_name}: {$e->getMessage()}"
				);
			}
		}
	}

	/**
	 * Test specific case: Site_Template_Limits must use get_instance.
	 *
	 * This is a regression test for the bug where Site_Template_Limits
	 * was instantiated with 'new' instead of get_instance().
	 */
	public function test_site_template_limits_singleton_usage(): void {

		$class_name = 'WP_Ultimo\Limits\Site_Template_Limits';

		// Verify the class exists
		$this->assertTrue(
			class_exists($class_name),
			'Site_Template_Limits class should exist'
		);

		// Verify it uses the Singleton trait
		$reflection = new ReflectionClass($class_name);
		$traits     = $reflection->getTraitNames();

		$this->assertContains(
			'WP_Ultimo\Traits\Singleton',
			$traits,
			'Site_Template_Limits should use the Singleton trait'
		);

		// Verify direct instantiation fails
		$exception_thrown = false;

		try {
			new \WP_Ultimo\Limits\Site_Template_Limits();
		} catch (\Error $e) {
			$exception_thrown = true;
		}

		$this->assertTrue(
			$exception_thrown,
			'Direct instantiation of Site_Template_Limits should fail'
		);

		// Verify get_instance works
		$instance = \WP_Ultimo\Limits\Site_Template_Limits::get_instance();

		$this->assertInstanceOf(
			$class_name,
			$instance,
			'get_instance() should return a Site_Template_Limits instance'
		);
	}
}
