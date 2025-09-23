<?php

namespace WP_Ultimo\Models;

use WP_UnitTestCase;

/**
 * Test class for Domain model functionality.
 *
 * Tests SSL certificate validation for custom domains including
 * valid certificates, invalid certificates, and empty domain handling.
 */
class Domain_Test extends WP_UnitTestCase {

	/**
	 * Test that has_valid_ssl_certificate returns true for valid SSL certificates.
	 */
	public function test_has_valid_ssl_certificate_with_valid_certificate(): void {
		// Mocking a domain with a valid SSL certificate.
		$domain = new Domain();
		$domain->set_domain('dogs.4thelols.uk');

		// Assert that it returns true for a valid SSL certificate.
		$this->assertTrue($domain->has_valid_ssl_certificate());
	}

	/**
	 * Test that has_valid_ssl_certificate returns false when the SSL certificate is invalid.
	 */
	public function test_has_valid_ssl_certificate_with_invalid_certificate(): void {
		// Mocking a domain with an invalid SSL certificate.
		$domain = new Domain();
		$domain->set_domain('eeeeeeeeeeeeeeeeauauexample.com');

		// Assert that it returns false for an invalid SSL certificate.
		$this->assertFalse($domain->has_valid_ssl_certificate());
	}

	/**
	 * Test that has_valid_ssl_certificate handles empty domain.
	 */
	public function test_has_valid_ssl_certificate_with_empty_domain(): void {
		// Mocking a domain with an empty value.
		$domain = new Domain();
		$domain->set_domain('');

		// Assert that it returns false for an empty domain.
		$this->assertFalse($domain->has_valid_ssl_certificate());
	}
}
