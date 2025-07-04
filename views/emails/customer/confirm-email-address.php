<?php
/**
 * Site Published Email Template - Customer
 *
 * @since 2.0.0
 */
?>
<?php // translators: %s: Customer Name ?>
<p><?php esc_html(sprintf(__('Hey %s,', 'multisite-ultimate'), '{{customer_name}}')); ?></p>

<p><?php echo esc_html__('Thanks for creating an account! You\'re only a step away from being ready.', 'multisite-ultimate'); ?></p>

<p><?php echo esc_html__('In order to complete the activation of your account, you need to confirm your email address by clicking on the link below.', 'multisite-ultimate'); ?></p>

<p>
	<a href="{{verification_link}}" style="text-decoration: none;" rel="nofollow" data-cy="email-verification-link"><?php esc_html_e('Verify Email Address →', 'multisite-ultimate'); ?></a>
	<br>
	<?php // translators: %s: Verification Link ?>
	<small><?php echo wp_kses_post(sprintf(__('or copy the link %s and paste it onto your browser', 'multisite-ultimate'), '<code>{{verification_link}}</code>'), ''); ?></small>
</p>
