<?php
/**
 * Model Mocking Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a mock site object.
 *
 * @since 2.0.0
 * @param string|int $seed Number used to return different site names and urls.
 * @return \WP_Ultimo\Models\Site
 */
function wu_mock_site($seed = false) {

	$atts = apply_filters(
		'wu_mock_site',
		[
			'title'       => __('Example Site', 'multisite-ultimate'),
			'description' => __('This is an example of a site description.', 'multisite-ultimate'),
			'domain'      => __('examplesite.dev', 'multisite-ultimate'),
			'path'        => '/',
		]
	);

	if ($seed) {
		$atts['title'] .= " {$seed}";
		$atts['domain'] = str_replace('.dev', "{$seed}.dev", (string) $atts['domain']);
	}

	return new \WP_Ultimo\Models\Site($atts);
}

/**
 * Returns a mock membership object.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Membership
 */
function wu_mock_membership() {

	return new \WP_Ultimo\Models\Membership(
		[
			'billing_address' => new \WP_Ultimo\Objects\Billing_Address(
				[
					'company_name'  => 'Company Co.',
					'billing_email' => 'company@co.dev',
				]
			),
		]
	);
}

/**
 * Returns a mock product object.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Product
 */
function wu_mock_product() {

	$product = new \WP_Ultimo\Models\Product(
		[
			'name' => __('Test Product', 'multisite-ultimate'),
		]
	);

	$product->_mocked = true;

	return $product;
}

/**
 * Returns a mock customer object.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Customer
 */
function wu_mock_customer() {

	$customer = new \WP_Ultimo\Models\Customer(
		[
			'billing_address' => new \WP_Ultimo\Objects\Billing_Address(
				[
					'company_name'  => 'Company Co.',
					'billing_email' => 'company@co.dev',
				]
			),
		]
	);

	$customer->_user = (object) [
		'data' => (object) [
			'ID'                  => '1',
			'user_login'          => 'mockeduser',
			'user_pass'           => 'passwordhash',
			'user_nicename'       => 'mockeduser',
			'user_email'          => 'mockeduser@dev.dev',
			'user_url'            => 'https://url.com',
			'user_registered'     => '2020-12-31 12:00:00',
			'user_activation_key' => '',
			'user_status'         => '0',
			'display_name'        => 'John McMocked',
			'spam'                => '0',
			'deleted'             => '0',
		],
	];

	return $customer;
}

/**
 * Returns a mock payment object.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Payment
 */
function wu_mock_payment() {

	$payment = new \WP_Ultimo\Models\Payment();

	$line_item = new \WP_Ultimo\Checkout\Line_Item(
		[
			'product' => wu_mock_product(),
		]
	);

	$payment->set_line_items(
		[
			$line_item,
		]
	);

	return $payment;
}

/**
 * Returns a mock domain object.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Payment
 */
function wu_mock_domain() {

	$domain = new \WP_Ultimo\Models\Domain(
		[
			'blog_id'        => 1,
			'domain'         => 'example.com',
			'active'         => true,
			'primary_domain' => true,
			'secure'         => true,
			'stage'          => 'checking-dns',
		]
	);

	return $domain;
}
