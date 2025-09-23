<?php
use PHPUnit\Framework\TestCase;

final class Checkout_Request_Test extends TestCase {

    public function test_request_or_session_accepts_empty_string(): void {
        // Ensure empty string in request overrides any default/session value
        $_REQUEST['discount_code'] = '';

        $checkout = new \WP_Ultimo\Checkout\Checkout();

        $value = $checkout->request_or_session('discount_code', 'DEFAULT');

        $this->assertSame('', $value, 'Expected empty discount_code to be honored (clear code).');

        unset($_REQUEST['discount_code']);
    }
}

