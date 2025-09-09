<?php

namespace WP_Ultimo\Managers;

use WP_UnitTestCase;
use WP_Ultimo\Settings;

class Domain_Manager_Test extends WP_UnitTestCase {

	private Domain_Manager $domain_manager;

	public function setUp(): void {
		parent::setUp();
		$this->domain_manager = Domain_Manager::get_instance();
	}

	/**
	 * Test should_create_www_subdomain with 'always' setting.
	 */
	public function test_should_create_www_subdomain_always(): void {
		// Mock the setting to 'always'
		wu_save_setting('auto_create_www_subdomain', 'always');

		// Test various domain types
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('example.com'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('subdomain.example.com'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('test.co.uk'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('deep.sub.example.com'));
	}

	/**
	 * Test should_create_www_subdomain with 'never' setting.
	 */
	public function test_should_create_www_subdomain_never(): void {
		// Mock the setting to 'never'
		wu_save_setting('auto_create_www_subdomain', 'never');

		// Test various domain types - all should return false
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('example.com'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('subdomain.example.com'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('test.co.uk'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('deep.sub.example.com'));
	}

	/**
	 * Test should_create_www_subdomain with 'main_only' setting for main domains.
	 */
	public function test_should_create_www_subdomain_main_only_main_domains(): void {
		// Mock the setting to 'main_only'
		wu_save_setting('auto_create_www_subdomain', 'main_only');

		// Test main domains - should return true
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('example.com'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('test.org'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('site.net'));
	}

	/**
	 * Test should_create_www_subdomain with 'main_only' setting for known multi-part TLDs.
	 */
	public function test_should_create_www_subdomain_main_only_multi_part_tlds(): void {
		// Mock the setting to 'main_only'
		wu_save_setting('auto_create_www_subdomain', 'main_only');

		// Test known multi-part TLD domains - should return true
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('example.co.uk'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('test.com.au'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('site.co.nz'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('company.com.br'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('business.co.in'));
	}

	/**
	 * Test should_create_www_subdomain with 'main_only' setting for subdomains.
	 */
	public function test_should_create_www_subdomain_main_only_subdomains(): void {
		// Mock the setting to 'main_only'
		wu_save_setting('auto_create_www_subdomain', 'main_only');

		// Test subdomains - should return false
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('subdomain.example.com'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('api.test.org'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('blog.site.net'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('deep.sub.example.com'));
	}

	/**
	 * Test should_create_www_subdomain with 'main_only' setting for complex subdomains with multi-part TLDs.
	 */
	public function test_should_create_www_subdomain_main_only_complex_subdomains(): void {
		// Mock the setting to 'main_only'
		wu_save_setting('auto_create_www_subdomain', 'main_only');

		// Test complex subdomains with multi-part TLDs - should return false
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('subdomain.example.co.uk'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('api.test.com.au'));
		$this->assertFalse($this->domain_manager->should_create_www_subdomain('blog.site.co.nz'));
	}

	/**
	 * Test should_create_www_subdomain with default setting (should default to 'always').
	 */
	public function test_should_create_www_subdomain_default(): void {
		// Remove any existing setting to test default behavior
		wu_save_setting('auto_create_www_subdomain', null);

		// Should default to 'always' behavior
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('example.com'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('subdomain.example.com'));
	}

	/**
	 * Test should_create_www_subdomain with invalid setting (should default to 'always').
	 */
	public function test_should_create_www_subdomain_invalid_setting(): void {
		// Set an invalid setting value
		wu_save_setting('auto_create_www_subdomain', 'invalid_option');

		// Should default to 'always' behavior
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('example.com'));
		$this->assertTrue($this->domain_manager->should_create_www_subdomain('subdomain.example.com'));
	}
}
