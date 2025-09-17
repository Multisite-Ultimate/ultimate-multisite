<?php
/**
 * Site Published Email Template - Customer
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php // translators: %s: Customer Name ?>
<p><?php esc_html(sprintf(__('Hey %s,', 'ultimate-multisite'), '{{customer_name}}')); ?></p>

<p><?php echo esc_html__('Thanks for creating an account! You\'re only a step away from being ready.', 'ultimate-multisite'); ?></p>

<p><?php echo esc_html__('In order to complete the activation of your account, you need to confirm your email address by clicking on the link below.', 'ultimate-multisite'); ?></p>

<p>
	<a href="{{verification_link}}" style="text-decoration: none;" rel="nofollow" data-cy="email-verification-link"><?php esc_html_e('Verify Email Address →', 'ultimate-multisite'); ?></a>
	<br>
	<?php // translators: %s: Verification Link ?>
	<small><?php echo wp_kses_post(sprintf(__('or copy the link %s and paste it onto your browser', 'ultimate-multisite'), '<code>{{verification_link}}</code>'), ''); ?></small>
</p>
