<?php
/**
 * Email account helper functions tests.
 *
 * @package WP_Ultimo\Tests
 */

namespace WP_Ultimo\Functions;

use WP_UnitTestCase;
use WP_Ultimo\Models\Email_Account;

/**
 * Test class for email account helper functions.
 *
 * Tests wu_get_email_account, wu_create_email_account, and related functions.
 */
class Email_Account_Functions_Test extends WP_UnitTestCase {

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
	 * Test wu_get_email_account returns false for invalid ID.
	 *
	 * Note: This test is skipped as it requires the email accounts table to exist.
	 */
	public function test_wu_get_email_account_invalid_id(): void {
		$this->markTestSkipped('Requires email accounts database table');
	}

	/**
	 * Test wu_get_email_accounts returns empty array when no accounts.
	 *
	 * Note: This test is skipped as it requires the email accounts table to exist.
	 */
	public function test_wu_get_email_accounts_empty(): void {
		$this->markTestSkipped('Requires email accounts database table');
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
	 * Test wu_get_email_account_by_email returns false for non-existent.
	 *
	 * Note: This test is skipped as it requires the email accounts table to exist.
	 */
	public function test_wu_get_email_account_by_email_not_found(): void {
		$this->markTestSkipped('Requires email accounts database table');
	}

	/**
	 * Test that wu_get_enabled_email_providers returns array.
	 */
	public function test_wu_get_enabled_email_providers_returns_array(): void {
		$providers = wu_get_enabled_email_providers();

		$this->assertIsArray($providers);
	}

	/**
	 * Test wu_count_email_accounts returns zero when no accounts.
	 *
	 * Note: This test is skipped as it requires the email accounts table to exist.
	 */
	public function test_wu_count_email_accounts_returns_zero(): void {
		$this->markTestSkipped('Requires email accounts database table');
	}

	/**
	 * Test password encryption with special characters.
	 */
	public function test_password_encryption_special_chars(): void {
		$password = 'P@$$w0rd!#%^&*()_+-=[]{}|;:,.<>?~`';

		$encrypted = wu_encrypt_email_password($password);
		$decrypted = wu_decrypt_email_password($encrypted);

		$this->assertEquals($password, $decrypted);
	}

	/**
	 * Test password encryption with empty string.
	 */
	public function test_password_encryption_empty(): void {
		$password = '';

		$encrypted = wu_encrypt_email_password($password);
		$decrypted = wu_decrypt_email_password($encrypted);

		$this->assertEquals($password, $decrypted);
	}

	/**
	 * Test password encryption with long password.
	 */
	public function test_password_encryption_long(): void {
		$password = str_repeat('a', 1000);

		$encrypted = wu_encrypt_email_password($password);
		$decrypted = wu_decrypt_email_password($encrypted);

		$this->assertEquals($password, $decrypted);
	}

	/**
	 * Test wu_generate_email_password with custom length.
	 */
	public function test_wu_generate_email_password_custom_length(): void {
		$password = wu_generate_email_password(24);

		$this->assertIsString($password);
		$this->assertEquals(24, strlen($password));
	}
}
