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
	 * Test email account creation with valid data.
	 */
	public function test_email_account_creation_with_valid_data(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(1);
		$email_account->set_membership_id(1);
		$email_account->set_email_address('user@example.com');
		$email_account->set_provider('cpanel');
		$email_account->set_status('active');
		$email_account->set_quota_mb(1024);

		$this->assertEquals(1, $email_account->get_customer_id());
		$this->assertEquals(1, $email_account->get_membership_id());
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
	 * Test default status value.
	 */
	public function test_default_status(): void {
		$email_account = new Email_Account();

		// Default status should be 'pending'
		$this->assertEquals('pending', $email_account->get_status());
	}

	/**
	 * Test to_array method.
	 */
	public function test_to_array(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(1);
		$email_account->set_email_address('test@example.com');
		$email_account->set_provider('cpanel');
		$email_account->set_status('active');

		$array = $email_account->to_array();

		$this->assertIsArray($array);
		$this->assertEquals(1, $array['customer_id']);
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
	 * Test get_customer returns false for invalid customer.
	 */
	public function test_invalid_customer_returns_false(): void {
		$email_account = new Email_Account();
		$email_account->set_customer_id(99999);

		$this->assertFalse($email_account->get_customer());
	}

	/**
	 * Test get_membership returns false for invalid membership.
	 */
	public function test_invalid_membership_returns_false(): void {
		$email_account = new Email_Account();
		$email_account->set_membership_id(99999);

		$this->assertFalse($email_account->get_membership());
	}

	/**
	 * Test get_customer relationship with real customer.
	 */
	public function test_get_customer_relationship(): void {
		$user_id = self::factory()->user->create(
			[
				'user_login' => 'emailcustomertest',
				'user_email' => 'emailcustomertest@example.com',
			]
		);

		$customer = new Customer();
		$customer->set_user_id($user_id);
		$customer->set_type('customer');
		$customer->set_email_verification('none');
		$result = $customer->save();

		if (is_wp_error($result)) {
			$this->markTestSkipped('Could not create test customer: ' . $result->get_error_message());
		}

		$email_account = new Email_Account();
		$email_account->set_customer_id($customer->get_id());

		$retrieved = $email_account->get_customer();
		$this->assertInstanceOf(Customer::class, $retrieved);
		$this->assertEquals($customer->get_id(), $retrieved->get_id());

		// Remove hook to avoid query to non-existent table during cleanup
		remove_action('wu_customer_post_delete', [\WP_Ultimo\Managers\Email_Account_Manager::get_instance(), 'handle_customer_deleted'], 10);

		// Clean up
		$customer->delete();
	}

	/**
	 * Test get_membership relationship with real membership.
	 */
	public function test_get_membership_relationship(): void {
		$user_id = self::factory()->user->create(
			[
				'user_login' => 'emailmembershiptest',
				'user_email' => 'emailmembershiptest@example.com',
			]
		);

		$customer = new Customer();
		$customer->set_user_id($user_id);
		$customer->set_type('customer');
		$customer->set_email_verification('none');
		$result = $customer->save();

		if (is_wp_error($result)) {
			$this->markTestSkipped('Could not create test customer: ' . $result->get_error_message());
		}

		$membership = new Membership();
		$membership->set_customer_id($customer->get_id());
		$membership->set_status('active');
		$result = $membership->save();

		if (is_wp_error($result)) {
			// Remove hook to avoid query to non-existent table during cleanup
			remove_action('wu_customer_post_delete', [\WP_Ultimo\Managers\Email_Account_Manager::get_instance(), 'handle_customer_deleted'], 10);
			$customer->delete();
			$this->markTestSkipped('Could not create test membership: ' . $result->get_error_message());
		}

		$email_account = new Email_Account();
		$email_account->set_membership_id($membership->get_id());

		$retrieved = $email_account->get_membership();
		$this->assertInstanceOf(Membership::class, $retrieved);
		$this->assertEquals($membership->get_id(), $retrieved->get_id());

		// Remove hooks to avoid query to non-existent table during cleanup
		remove_action('wu_membership_post_delete', [\WP_Ultimo\Managers\Email_Account_Manager::get_instance(), 'handle_membership_deleted'], 10);
		remove_action('wu_customer_post_delete', [\WP_Ultimo\Managers\Email_Account_Manager::get_instance(), 'handle_customer_deleted'], 10);

		// Clean up
		$membership->delete();
		$customer->delete();
	}
}
