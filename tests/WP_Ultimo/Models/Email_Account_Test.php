<?php
/**
 * Email Account model tests.
 *
 * @package WP_Ultimo\Tests
 */

namespace WP_Ultimo\Models;

use WP_UnitTestCase;
use WP_Ultimo\Database\Email_Accounts\Email_Account_Status;

/**
 * Test class for Email_Account model functionality.
 *
 * Tests email account creation, validation, and relationships.
 */
class Email_Account_Test extends WP_UnitTestCase {

	/**
	 * Test customer for email account tests.
	 *
	 * @var Customer
	 */
	private static $test_customer;

	/**
	 * Test membership for email account tests.
	 *
	 * @var Membership
	 */
	private static $test_membership;

	/**
	 * Set up test environment.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Create a test user
		$user_id = self::factory()->user->create(
			[
				'user_login' => 'emailtestuser',
				'user_email' => 'emailtest@example.com',
			]
		);

		// Create a test customer
		self::$test_customer = wu_create_customer(
			[
				'user_id'            => $user_id,
				'type'               => 'customer',
				'email_verification' => 'none',
			]
		);

		// Create a test membership
		self::$test_membership = wu_create_membership(
			[
				'customer_id' => self::$test_customer->get_id(),
				'status'      => 'active',
			]
		);
	}

	/**
	 * Test email account creation with valid data.
	 */
	public function test_email_account_creation_with_valid_data(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(self::$test_customer->get_id());
		$email_account->set_membership_id(self::$test_membership->get_id());
		$email_account->set_email_address('user@example.com');
		$email_account->set_provider('cpanel');
		$email_account->set_status('active');
		$email_account->set_quota_mb(1024);

		$this->assertEquals(self::$test_customer->get_id(), $email_account->get_customer_id());
		$this->assertEquals(self::$test_membership->get_id(), $email_account->get_membership_id());
		$this->assertEquals('user@example.com', $email_account->get_email_address());
		$this->assertEquals('cpanel', $email_account->get_provider());
		$this->assertEquals('active', $email_account->get_status());
		$this->assertEquals(1024, $email_account->get_quota_mb());
	}

	/**
	 * Test email address parsing.
	 */
	public function test_email_address_parsing(): void {
		$email_account = new Email_Account();
		$email_account->set_email_address('testuser@mydomain.com');

		$this->assertEquals('testuser@mydomain.com', $email_account->get_email_address());
		$this->assertEquals('testuser', $email_account->get_username());
		$this->assertEquals('mydomain.com', $email_account->get_domain());
	}

	/**
	 * Test domain extraction from email address.
	 */
	public function test_domain_extraction(): void {
		$email_account = new Email_Account();
		$email_account->set_email_address('info@subdomain.example.org');

		$this->assertEquals('subdomain.example.org', $email_account->get_domain());
	}

	/**
	 * Test status setters and getters.
	 */
	public function test_status_functionality(): void {
		$email_account = new Email_Account();

		// Test all valid statuses
		$statuses = ['pending', 'provisioning', 'active', 'suspended', 'failed'];

		foreach ($statuses as $status) {
			$email_account->set_status($status);
			$this->assertEquals($status, $email_account->get_status());
		}
	}

	/**
	 * Test status label retrieval.
	 */
	public function test_status_label(): void {
		$email_account = new Email_Account();

		$email_account->set_status('active');
		$this->assertNotEmpty($email_account->get_status_label());

		$email_account->set_status('pending');
		$this->assertNotEmpty($email_account->get_status_label());

		$email_account->set_status('failed');
		$this->assertNotEmpty($email_account->get_status_label());
	}

	/**
	 * Test status class retrieval.
	 */
	public function test_status_class(): void {
		$email_account = new Email_Account();

		$email_account->set_status('active');
		$this->assertNotEmpty($email_account->get_status_class());

		$email_account->set_status('failed');
		$this->assertNotEmpty($email_account->get_status_class());
	}

	/**
	 * Test purchase type functionality.
	 */
	public function test_purchase_type_functionality(): void {
		$email_account = new Email_Account();

		// Default should be membership_included
		$email_account->set_purchase_type('membership_included');
		$this->assertEquals('membership_included', $email_account->get_purchase_type());

		$email_account->set_purchase_type('per_account');
		$this->assertEquals('per_account', $email_account->get_purchase_type());
	}

	/**
	 * Test quota functionality.
	 */
	public function test_quota_functionality(): void {
		$email_account = new Email_Account();

		// Test zero quota (unlimited)
		$email_account->set_quota_mb(0);
		$this->assertEquals(0, $email_account->get_quota_mb());

		// Test positive quota
		$email_account->set_quota_mb(2048);
		$this->assertEquals(2048, $email_account->get_quota_mb());
	}

	/**
	 * Test external ID functionality.
	 */
	public function test_external_id_functionality(): void {
		$email_account = new Email_Account();

		$external_id = 'provider-account-12345';
		$email_account->set_external_id($external_id);

		$this->assertEquals($external_id, $email_account->get_external_id());
	}

	/**
	 * Test site ID functionality.
	 */
	public function test_site_id_functionality(): void {
		$email_account = new Email_Account();

		$email_account->set_site_id(123);
		$this->assertEquals(123, $email_account->get_site_id());

		$email_account->set_site_id(null);
		$this->assertNull($email_account->get_site_id());
	}

	/**
	 * Test payment ID functionality.
	 */
	public function test_payment_id_functionality(): void {
		$email_account = new Email_Account();

		$email_account->set_payment_id(456);
		$this->assertEquals(456, $email_account->get_payment_id());

		$email_account->set_payment_id(null);
		$this->assertNull($email_account->get_payment_id());
	}

	/**
	 * Test validation rules exist.
	 */
	public function test_validation_rules_exist(): void {
		$email_account = new Email_Account();
		$rules         = $email_account->validation_rules();

		$this->assertIsArray($rules);
		$this->assertArrayHasKey('customer_id', $rules);
		$this->assertArrayHasKey('email_address', $rules);
		$this->assertArrayHasKey('provider', $rules);
		$this->assertArrayHasKey('status', $rules);
	}

	/**
	 * Test customer relationship.
	 */
	public function test_get_customer_relationship(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(self::$test_customer->get_id());

		$customer = $email_account->get_customer();
		$this->assertInstanceOf(Customer::class, $customer);
		$this->assertEquals(self::$test_customer->get_id(), $customer->get_id());
	}

	/**
	 * Test membership relationship.
	 */
	public function test_get_membership_relationship(): void {
		$email_account = new Email_Account();
		$email_account->set_membership_id(self::$test_membership->get_id());

		$membership = $email_account->get_membership();
		$this->assertInstanceOf(Membership::class, $membership);
		$this->assertEquals(self::$test_membership->get_id(), $membership->get_id());
	}

	/**
	 * Test default state method.
	 */
	public function test_default_state(): void {
		$default = Email_Account::default_state();

		$this->assertIsArray($default);
		$this->assertArrayHasKey('status', $default);
		$this->assertEquals('pending', $default['status']);
	}

	/**
	 * Test to_array method.
	 */
	public function test_to_array(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(self::$test_customer->get_id());
		$email_account->set_email_address('test@example.com');
		$email_account->set_provider('cpanel');
		$email_account->set_status('active');

		$array = $email_account->to_array();

		$this->assertIsArray($array);
		$this->assertEquals(self::$test_customer->get_id(), $array['customer_id']);
		$this->assertEquals('test@example.com', $array['email_address']);
		$this->assertEquals('cpanel', $array['provider']);
		$this->assertEquals('active', $array['status']);
	}

	/**
	 * Test provider values.
	 */
	public function test_provider_values(): void {
		$email_account = new Email_Account();

		$providers = ['cpanel', 'purelymail', 'google_workspace', 'microsoft365'];

		foreach ($providers as $provider) {
			$email_account->set_provider($provider);
			$this->assertEquals($provider, $email_account->get_provider());
		}
	}

	/**
	 * Test empty email address handling.
	 */
	public function test_empty_email_address(): void {
		$email_account = new Email_Account();
		$email_account->set_email_address('');

		$this->assertEquals('', $email_account->get_email_address());
		$this->assertEquals('', $email_account->get_username());
		$this->assertEquals('', $email_account->get_domain());
	}

	/**
	 * Test invalid customer relationship returns null.
	 */
	public function test_invalid_customer_returns_null(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(99999);

		$this->assertNull($email_account->get_customer());
	}

	/**
	 * Test invalid membership relationship returns null.
	 */
	public function test_invalid_membership_returns_null(): void {
		$email_account = new Email_Account();
		$email_account->set_membership_id(99999);

		$this->assertNull($email_account->get_membership());
	}
}
