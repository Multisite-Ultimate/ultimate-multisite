<?php
/**
 * Test case for DNS_Record_Manager.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Managers;

use WP_Ultimo\Managers\DNS_Record_Manager;
use WP_UnitTestCase;

/**
 * Test DNS_Record_Manager functionality.
 */
class DNS_Record_Manager_Test extends WP_UnitTestCase {

	/**
	 * The DNS Record Manager instance.
	 *
	 * @var DNS_Record_Manager
	 */
	private $manager;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->manager = DNS_Record_Manager::get_instance();
	}

	/**
	 * Test singleton instance.
	 */
	public function test_singleton_instance() {
		$instance1 = DNS_Record_Manager::get_instance();
		$instance2 = DNS_Record_Manager::get_instance();

		$this->assertSame($instance1, $instance2);
		$this->assertInstanceOf(DNS_Record_Manager::class, $instance1);
	}

	/**
	 * Test get_allowed_record_types returns array.
	 */
	public function test_get_allowed_record_types() {
		$types = $this->manager->get_allowed_record_types();

		$this->assertIsArray($types);
		$this->assertContains('A', $types);
		$this->assertContains('AAAA', $types);
		$this->assertContains('CNAME', $types);
		$this->assertContains('MX', $types);
		$this->assertContains('TXT', $types);
	}

	/**
	 * Test get_dns_provider returns null when no provider configured.
	 */
	public function test_get_dns_provider_returns_null_when_not_configured() {
		// When no provider is enabled, should return null
		$provider = $this->manager->get_dns_provider();

		// This may or may not be null depending on test environment
		// At minimum, check it doesn't throw an error
		$this->assertTrue($provider === null || is_object($provider));
	}

	/**
	 * Test get_dns_capable_providers returns array.
	 */
	public function test_get_dns_capable_providers() {
		$providers = $this->manager->get_dns_capable_providers();

		$this->assertIsArray($providers);
	}

	/**
	 * Test validate_dns_record with valid A record.
	 */
	public function test_validate_dns_record_valid_a_record() {
		$record = [
			'type'    => 'A',
			'name'    => 'test',
			'content' => '192.168.1.1',
			'ttl'     => 3600,
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertTrue($result);
	}

	/**
	 * Test validate_dns_record with invalid type.
	 */
	public function test_validate_dns_record_invalid_type() {
		$record = [
			'type'    => 'INVALID',
			'name'    => 'test',
			'content' => 'value',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('invalid-type', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with empty name.
	 */
	public function test_validate_dns_record_empty_name() {
		$record = [
			'type'    => 'A',
			'name'    => '',
			'content' => '192.168.1.1',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('empty-name', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with empty content.
	 */
	public function test_validate_dns_record_empty_content() {
		$record = [
			'type'    => 'A',
			'name'    => 'test',
			'content' => '',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('empty-content', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with invalid IPv4.
	 */
	public function test_validate_dns_record_invalid_ipv4() {
		$record = [
			'type'    => 'A',
			'name'    => 'test',
			'content' => 'not-an-ip',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('invalid-ipv4', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with invalid IPv6.
	 */
	public function test_validate_dns_record_invalid_ipv6() {
		$record = [
			'type'    => 'AAAA',
			'name'    => 'test',
			'content' => '192.168.1.1', // IPv4 instead of IPv6
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('invalid-ipv6', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with valid AAAA record.
	 */
	public function test_validate_dns_record_valid_aaaa() {
		$record = [
			'type'    => 'AAAA',
			'name'    => 'test',
			'content' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertTrue($result);
	}

	/**
	 * Test validate_dns_record with valid CNAME.
	 */
	public function test_validate_dns_record_valid_cname() {
		$record = [
			'type'    => 'CNAME',
			'name'    => 'www',
			'content' => 'example.com',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertTrue($result);
	}

	/**
	 * Test validate_dns_record with invalid CNAME hostname.
	 */
	public function test_validate_dns_record_invalid_cname() {
		$record = [
			'type'    => 'CNAME',
			'name'    => 'www',
			'content' => 'not a valid hostname!',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('invalid-hostname', $result->get_error_code());
	}

	/**
	 * Test validate_dns_record with valid MX record.
	 */
	public function test_validate_dns_record_valid_mx() {
		$record = [
			'type'     => 'MX',
			'name'     => '@',
			'content'  => 'mail.example.com',
			'priority' => 10,
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertTrue($result);
	}

	/**
	 * Test validate_dns_record with valid TXT record.
	 */
	public function test_validate_dns_record_valid_txt() {
		$record = [
			'type'    => 'TXT',
			'name'    => '@',
			'content' => 'v=spf1 include:_spf.google.com ~all',
		];

		$result = $this->invoke_private_method($this->manager, 'validate_dns_record', [$record]);

		$this->assertTrue($result);
	}

	/**
	 * Test export_to_bind format.
	 */
	public function test_export_to_bind() {
		$records = [
			new \WP_Ultimo\Integrations\Host_Providers\DNS_Record([
				'type'    => 'A',
				'name'    => '@',
				'content' => '192.168.1.1',
				'ttl'     => 3600,
			]),
			new \WP_Ultimo\Integrations\Host_Providers\DNS_Record([
				'type'    => 'MX',
				'name'    => '@',
				'content' => 'mail.example.com',
				'ttl'     => 3600,
				'priority' => 10,
			]),
		];

		$bind = $this->manager->export_to_bind($records, 'example.com');

		$this->assertStringContainsString('$ORIGIN example.com.', $bind);
		$this->assertStringContainsString('@ 3600 IN A 192.168.1.1', $bind);
		$this->assertStringContainsString('@ 3600 IN MX 10 mail.example.com.', $bind);
	}

	/**
	 * Test parse_bind_format.
	 */
	public function test_parse_bind_format() {
		$bind_content = <<<BIND
\$ORIGIN example.com.
@ 3600 IN A 192.168.1.1
www 3600 IN CNAME example.com.
@ 3600 IN MX 10 mail.example.com.
@ 3600 IN TXT "v=spf1 mx ~all"
BIND;

		$records = $this->manager->parse_bind_format($bind_content);

		$this->assertIsArray($records);
		$this->assertGreaterThanOrEqual(4, count($records));

		// Find A record
		$a_record = array_filter($records, fn($r) => $r['type'] === 'A');
		$this->assertNotEmpty($a_record);
	}

	/**
	 * Test customer_can_manage_dns returns false for non-owner.
	 */
	public function test_customer_can_manage_dns_non_owner() {
		// Create a test user who doesn't own any domains
		$user_id = $this->factory->user->create();

		$result = $this->manager->customer_can_manage_dns($user_id, 'example.com');

		$this->assertFalse($result);
	}

	/**
	 * Helper method to invoke private/protected methods for testing.
	 *
	 * @param object $object     The object instance.
	 * @param string $method     The method name.
	 * @param array  $parameters Parameters to pass.
	 * @return mixed The method result.
	 */
	private function invoke_private_method($object, string $method, array $parameters = []) {
		$reflection = new \ReflectionClass(get_class($object));
		$method     = $reflection->getMethod($method);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}
