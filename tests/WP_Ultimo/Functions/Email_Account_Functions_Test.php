<?php
/**
 * Email account helper functions tests.
 *
 * @package WP_Ultimo\Tests
 */

namespace WP_Ultimo\Functions;

use WP_UnitTestCase;
use WP_Ultimo\Models\Customer;
use WP_Ultimo\Models\Membership;
use WP_Ultimo\Models\Email_Account;

/**
 * Test class for email account helper functions.
 *
 * Tests wu_get_email_account, wu_create_email_account, and related functions.
 */
class Email_Account_Functions_Test extends WP_UnitTestCase {

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
				'user_login' => 'emailfunctestuser',
				'user_email' => 'emailfunctest@example.com',
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
	 * Test wu_generate_email_password generates valid password.
	 */
	public function test_wu_generate_email_password(): void {
		$password = wu_generate_email_password();

		$this->assertIsString($password);
		$this->assertGreaterThanOrEqual(12, strlen($password));
	}

	/**
	 * Test wu_generate_email_password generates unique passwords.
	 */
	public function test_wu_generate_email_password_unique(): void {
		$passwords = [];

		for ($i = 0; $i < 10; $i++) {
			$passwords[] = wu_generate_email_password();
		}

		// All passwords should be unique
		$unique = array_unique($passwords);
		$this->assertCount(10, $unique);
	}

	/**
	 * Test wu_count_email_accounts returns zero when no accounts exist.
	 */
	public function test_wu_count_email_accounts_returns_zero(): void {
		$count = wu_count_email_accounts(self::$test_customer->get_id(), self::$test_membership->get_id());

		$this->assertEquals(0, $count);
	}

	/**
	 * Test wu_get_email_account returns null for invalid ID.
	 */
	public function test_wu_get_email_account_invalid_id(): void {
		$account = wu_get_email_account(99999);

		$this->assertNull($account);
	}

	/**
	 * Test wu_get_email_accounts returns empty array.
	 */
	public function test_wu_get_email_accounts_empty(): void {
		$accounts = wu_get_email_accounts(
			[
				'customer_id' => self::$test_customer->get_id(),
			]
		);

		$this->assertIsArray($accounts);
		$this->assertEmpty($accounts);
	}

	/**
	 * Test wu_create_email_account creates valid account.
	 */
	public function test_wu_create_email_account(): void {
		$account = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => 'testcreate@example.com',
				'provider'      => 'cpanel',
				'status'        => 'pending',
				'quota_mb'      => 1024,
			]
		);

		$this->assertInstanceOf(Email_Account::class, $account);
		$this->assertGreaterThan(0, $account->get_id());
		$this->assertEquals('testcreate@example.com', $account->get_email_address());
		$this->assertEquals('cpanel', $account->get_provider());
		$this->assertEquals('pending', $account->get_status());
		$this->assertEquals(1024, $account->get_quota_mb());

		// Clean up
		$account->delete();
	}

	/**
	 * Test wu_get_email_account retrieves created account.
	 */
	public function test_wu_get_email_account_retrieves(): void {
		$created = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => 'testretrieve@example.com',
				'provider'      => 'purelymail',
				'status'        => 'active',
			]
		);

		$retrieved = wu_get_email_account($created->get_id());

		$this->assertInstanceOf(Email_Account::class, $retrieved);
		$this->assertEquals($created->get_id(), $retrieved->get_id());
		$this->assertEquals('testretrieve@example.com', $retrieved->get_email_address());

		// Clean up
		$created->delete();
	}

	/**
	 * Test wu_count_email_accounts counts correctly.
	 */
	public function test_wu_count_email_accounts_counts(): void {
		// Create two accounts
		$account1 = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => 'count1@example.com',
				'provider'      => 'cpanel',
				'status'        => 'active',
			]
		);

		$account2 = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => 'count2@example.com',
				'provider'      => 'cpanel',
				'status'        => 'active',
			]
		);

		$count = wu_count_email_accounts(self::$test_customer->get_id(), self::$test_membership->get_id());

		$this->assertEquals(2, $count);

		// Clean up
		$account1->delete();
		$account2->delete();
	}

	/**
	 * Test wu_get_email_accounts returns accounts.
	 */
	public function test_wu_get_email_accounts_returns(): void {
		$account = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => 'getaccounts@example.com',
				'provider'      => 'google_workspace',
				'status'        => 'active',
			]
		);

		$accounts = wu_get_email_accounts(
			[
				'customer_id' => self::$test_customer->get_id(),
			]
		);

		$this->assertIsArray($accounts);
		$this->assertNotEmpty($accounts);

		// Clean up
		$account->delete();
	}

	/**
	 * Test wu_encrypt_email_password and wu_decrypt_email_password.
	 */
	public function test_password_encryption_decryption(): void {
		$original_password = 'MySecureP@ssw0rd!';

		$encrypted = wu_encrypt_email_password($original_password);
		$this->assertNotEquals($original_password, $encrypted);
		$this->assertNotEmpty($encrypted);

		$decrypted = wu_decrypt_email_password($encrypted);
		$this->assertEquals($original_password, $decrypted);
	}

	/**
	 * Test wu_store_email_password_token and wu_get_email_password_from_token.
	 */
	public function test_password_token_storage(): void {
		$password   = 'TokenTestP@ssword';
		$account_id = 12345;

		$token = wu_store_email_password_token($account_id, $password);
		$this->assertNotEmpty($token);
		$this->assertIsString($token);

		$retrieved = wu_get_email_password_from_token($token, $account_id);
		$this->assertEquals($password, $retrieved);

		// Token should be deleted after retrieval
		$second_try = wu_get_email_password_from_token($token, $account_id);
		$this->assertFalse($second_try);
	}

	/**
	 * Test wu_get_email_password_from_token with wrong account ID.
	 */
	public function test_password_token_wrong_account(): void {
		$password   = 'WrongAccountTest';
		$account_id = 11111;

		$token = wu_store_email_password_token($account_id, $password);

		// Try to retrieve with wrong account ID
		$retrieved = wu_get_email_password_from_token($token, 99999);
		$this->assertFalse($retrieved);
	}

	/**
	 * Test wu_get_email_account_by_email function.
	 */
	public function test_wu_get_email_account_by_email(): void {
		$email = 'findbyemail@example.com';

		$account = wu_create_email_account(
			[
				'customer_id'   => self::$test_customer->get_id(),
				'membership_id' => self::$test_membership->get_id(),
				'email_address' => $email,
				'provider'      => 'microsoft365',
				'status'        => 'active',
			]
		);

		$found = wu_get_email_account_by_email($email);

		$this->assertInstanceOf(Email_Account::class, $found);
		$this->assertEquals($account->get_id(), $found->get_id());
		$this->assertEquals($email, $found->get_email_address());

		// Clean up
		$account->delete();
	}

	/**
	 * Test wu_get_email_account_by_email returns null for non-existent.
	 */
	public function test_wu_get_email_account_by_email_not_found(): void {
		$found = wu_get_email_account_by_email('nonexistent@example.com');

		$this->assertNull($found);
	}

	/**
	 * Test that wu_get_enabled_email_providers returns array.
	 */
	public function test_wu_get_enabled_email_providers_returns_array(): void {
		$providers = wu_get_enabled_email_providers();

		$this->assertIsArray($providers);
	}

	/**
	 * Test wu_create_email_account with invalid customer returns error.
	 */
	public function test_wu_create_email_account_invalid_customer(): void {
		$account = wu_create_email_account(
			[
				'customer_id'   => 0,
				'email_address' => 'invalid@example.com',
				'provider'      => 'cpanel',
				'status'        => 'pending',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $account);
	}
}
