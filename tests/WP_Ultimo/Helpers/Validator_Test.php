<?php
/**
 * Test case for Validator Helper.
 *
 * @package WP_Ultimo
 * @subpackage Tests
 */

namespace WP_Ultimo\Tests\Helpers;

use WP_Ultimo\Helpers\Validator;
use WP_UnitTestCase;

/**
 * Test Validator Helper functionality.
 */
class Validator_Test extends WP_UnitTestCase {

	/**
	 * Test validator instance.
	 *
	 * @var Validator
	 */
	private $validator;

	/**
	 * Set up test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->validator = new Validator();
	}

	/**
	 * Test validator initialization.
	 */
	public function test_validator_initialization() {
		$this->assertInstanceOf(Validator::class, $this->validator);
	}

	/**
	 * Test basic required field validation.
	 */
	public function test_required_field_validation() {
		$data = [
			'name' => '',
		];

		$rules = [
			'name' => 'required',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertTrue($errors->has_errors());
		$this->assertContains('name', $errors->get_error_codes());
	}

	/**
	 * Test successful validation.
	 */
	public function test_successful_validation() {
		$data = [
			'name'  => 'John Doe',
			'email' => 'john@example.com',
		];

		$rules = [
			'name'  => 'required',
			'email' => 'required|email',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertFalse($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertFalse($errors->has_errors());
	}

	/**
	 * Test email validation.
	 */
	public function test_email_validation() {
		$data = [
			'email' => 'invalid-email',
		];

		$rules = [
			'email' => 'required|email',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertContains('email', $errors->get_error_codes());
	}

	/**
	 * Test min/max validation.
	 */
	public function test_min_max_validation() {
		$data = [
			'password' => '12',
			'age'      => 150,
		];

		$rules = [
			'password' => 'required|min:6',
			'age'      => 'required|integer|max:120',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertContains('password', $errors->get_error_codes());
		$this->assertContains('age', $errors->get_error_codes());
	}

	/**
	 * Test alpha_dash validation.
	 */
	public function test_alpha_dash_validation() {
		$data = [
			'username' => 'user@name!',
		];

		$rules = [
			'username' => 'required|alpha_dash',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertContains('username', $errors->get_error_codes());
	}

	/**
	 * Test successful alpha_dash validation.
	 */
	public function test_successful_alpha_dash_validation() {
		$data = [
			'username' => 'user_name-123',
		];

		$rules = [
			'username' => 'required|alpha_dash',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertFalse($result->fails());
	}

	/**
	 * Test integer validation.
	 */
	public function test_integer_validation() {
		$data = [
			'number' => 'not-a-number',
		];

		$rules = [
			'number' => 'required|integer',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);
		$this->assertContains('number', $errors->get_error_codes());
	}

	/**
	 * Test validation with aliases.
	 */
	public function test_validation_with_aliases() {
		$data = [
			'user_email' => '',
		];

		$rules = [
			'user_email' => 'required|email',
		];

		$aliases = [
			'user_email' => 'Email Address',
		];

		$result = $this->validator->validate($data, $rules, $aliases);

		$this->assertTrue($result->fails());

		$errors         = $result->get_errors();
		$error_messages = $errors->get_error_messages();

		// Should use the alias in error messages
		$this->assertNotEmpty($error_messages);
	}

	/**
	 * Test multiple errors for same field.
	 */
	public function test_multiple_errors_same_field() {
		$data = [
			'email' => 'a',
		];

		$rules = [
			'email' => 'required|email|min:5',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertTrue($result->fails());

		$errors = $result->get_errors();
		$this->assertInstanceOf(\WP_Error::class, $errors);

		$error_messages = $errors->get_error_messages('email');
		$this->assertGreaterThan(1, count($error_messages));
	}

	/**
	 * Test get validation method.
	 */
	public function test_get_validation() {
		$data = [
			'name' => 'John',
		];

		$rules = [
			'name' => 'required',
		];

		$this->validator->validate($data, $rules);

		$validation = $this->validator->get_validation();
		$this->assertNotNull($validation);
	}

	/**
	 * Test complex validation scenario.
	 */
	public function test_complex_validation_scenario() {
		$data = [
			'username' => 'user123',
			'email'    => 'user@example.com',
			'password' => 'password123',
			'age'      => 25,
			'website'  => '',
		];

		$rules = [
			'username' => 'required|alpha_dash|min:3',
			'email'    => 'required|email',
			'password' => 'required|min:8',
			'age'      => 'required|integer|min:18|max:100',
			'website'  => 'url', // Optional field
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertFalse($result->fails());
	}

	/**
	 * Test successful required_without validation.
	 */
	public function test_successful_required_without_validation() {
		$data = [
			'email' => 'user@example.com',
			'phone' => '',
		];

		$rules = [
			'email' => 'required_without:phone|email',
			'phone' => 'required_without:email',
		];

		$result = $this->validator->validate($data, $rules);

		$this->assertFalse($result->fails());
	}
}
