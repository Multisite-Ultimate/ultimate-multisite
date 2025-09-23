<?php
/**
 * Test case for Hash Helper.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Helpers;

use WP_Ultimo\Helpers\Hash;
use WP_UnitTestCase;

/**
 * Test Hash Helper functionality.
 */
class Hash_Test extends WP_UnitTestCase {

	/**
	 * Test basic hash encoding and decoding.
	 */
	public function test_basic_encode_decode() {
		$original_id = 12345;

		$hash       = Hash::encode($original_id);
		$decoded_id = Hash::decode($hash);

		$this->assertEquals($original_id, $decoded_id);
		$this->assertIsString($hash);
		$this->assertNotEmpty($hash);
	}

	/**
	 * Test hash encoding produces consistent results.
	 */
	public function test_consistent_encoding() {
		$id = 999;

		$hash1 = Hash::encode($id);
		$hash2 = Hash::encode($id);

		$this->assertEquals($hash1, $hash2);
	}

	/**
	 * Test hash encoding with different groups.
	 */
	public function test_different_groups_produce_different_hashes() {
		$id = 123;

		$hash1 = Hash::encode($id, 'group1');
		$hash2 = Hash::encode($id, 'group2');

		$this->assertNotEquals($hash1, $hash2);
	}

	/**
	 * Test decode with matching groups.
	 */
	public function test_decode_with_matching_groups() {
		$id    = 456;
		$group = 'test-group';

		$hash       = Hash::encode($id, $group);
		$decoded_id = Hash::decode($hash, $group);

		$this->assertEquals($id, $decoded_id);
	}

	/**
	 * Test decode with mismatched groups returns different result.
	 */
	public function test_decode_with_mismatched_groups() {
		$id = 789;

		$hash       = Hash::encode($id, 'group1');
		$decoded_id = Hash::decode($hash, 'group2');

		$this->assertNotEquals($id, $decoded_id);
	}

	/**
	 * Test encoding zero.
	 */
	public function test_encode_zero() {
		$id = 0;

		$hash       = Hash::encode($id);
		$decoded_id = Hash::decode($hash);

		$this->assertEquals($id, $decoded_id);
		$this->assertIsString($hash);
	}

	/**
	 * Test encoding large numbers.
	 */
	public function test_encode_large_numbers() {
		$id = 999999999;

		$hash       = Hash::encode($id);
		$decoded_id = Hash::decode($hash);

		$this->assertEquals($id, $decoded_id);
	}

	/**
	 * Test hash length constant.
	 */
	public function test_hash_length_constant() {
		$this->assertEquals(10, Hash::LENGTH);
	}

	/**
	 * Test default group encoding.
	 */
	public function test_default_group_encoding() {
		$id = 555;

		$hash1 = Hash::encode($id);
		$hash2 = Hash::encode($id, 'wp-ultimo');

		$this->assertEquals($hash1, $hash2);
	}

	/**
	 * Test multiple consecutive IDs produce different hashes.
	 */
	public function test_consecutive_ids_different_hashes() {
		$id1 = 100;
		$id2 = 101;

		$hash1 = Hash::encode($id1);
		$hash2 = Hash::encode($id2);

		$this->assertNotEquals($hash1, $hash2);
	}

	/**
	 * Test hash contains only allowed characters.
	 */
	public function test_hash_character_set() {
		$id   = 12345;
		$hash = Hash::encode($id);

		// Should only contain uppercase letters and numbers
		$this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $hash);
	}

	/**
	 * Test decoding invalid hash.
	 */
	public function test_decode_invalid_hash() {
		$invalid_hash = 'invalid-hash-string';
		$result       = Hash::decode($invalid_hash);

		// Should return false or empty when decoding fails
		$this->assertFalse($result);
	}

	/**
	 * Test encoding negative numbers.
	 */
	public function test_encode_negative_numbers() {
		// Hashids typically doesn't handle negative numbers well
		$id = -123;

		$hash       = Hash::encode($id);
		$decoded_id = Hash::decode($hash);

		// The behavior may vary, but it should at least not crash
		$this->assertIsString($hash);
	}

	/**
	 * Test round trip with various ID ranges.
	 */
	public function test_round_trip_various_ranges() {
		$test_ids = [1, 10, 100, 1000, 10000, 99999];

		foreach ($test_ids as $id) {
			$hash       = Hash::encode($id);
			$decoded_id = Hash::decode($hash);

			$this->assertEquals($id, $decoded_id, "Failed for ID: {$id}");
		}
	}

	/**
	 * Test hash uniqueness across range of IDs.
	 */
	public function test_hash_uniqueness() {
		$hashes = [];

		for ($i = 1; $i <= 100; $i++) {
			$hash = Hash::encode($i);
			$this->assertNotContains($hash, $hashes, "Duplicate hash found for ID: {$i}");
			$hashes[] = $hash;
		}

		// Ensure we generated 100 unique hashes
		$this->assertEquals(100, count(array_unique($hashes)));
	}

	/**
	 * Test encoding with empty string group.
	 */
	public function test_encode_empty_group() {
		$id = 123;

		$hash       = Hash::encode($id, '');
		$decoded_id = Hash::decode($hash, '');

		$this->assertEquals($id, $decoded_id);
	}

	/**
	 * Test encoding with very long group name.
	 */
	public function test_encode_long_group_name() {
		$id         = 456;
		$long_group = str_repeat('very-long-group-name-', 10);

		$hash       = Hash::encode($id, $long_group);
		$decoded_id = Hash::decode($hash, $long_group);

		$this->assertEquals($id, $decoded_id);
	}
}
