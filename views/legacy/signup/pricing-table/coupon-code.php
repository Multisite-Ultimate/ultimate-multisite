<?php
/**
 * Coupon code view.
 *
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

if (isset($_GET['coupon']) && wu_get_coupon(sanitize_text_field(wp_unslash($_GET['coupon']))) !== false && isset($_GET['step']) && 'plan' === $_GET['step']) : // phpcs:ignore WordPress.Security.NonceVerification
	?>

<div id="coupon-code-app">
</div>

	<?php
endif;
