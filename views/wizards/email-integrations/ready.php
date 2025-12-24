<?php
/**
 * Email provider integrations ready view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;
?>
<h1><?php esc_html_e('Setup Complete!', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4">
	<?php
	printf(
		/* translators: %s is the provider name */
		esc_html__('The %s email provider integration is now configured and ready to use.', 'ultimate-multisite'),
		esc_html($integration->get_title())
	);
	?>
</p>

<div class="wu-bg-white wu-p-6 wu--mx-6">

	<div class="wu-text-center wu-py-8">
		<span class="dashicons dashicons-yes-alt wu-text-6xl wu-text-green-500"></span>
		<h2 class="wu-text-xl wu-text-gray-800 wu-mt-4"><?php esc_html_e('You\'re all set!', 'ultimate-multisite'); ?></h2>
		<p class="wu-text-gray-600 wu-mt-2">
			<?php esc_html_e('Your customers can now create email accounts using this provider.', 'ultimate-multisite'); ?>
		</p>
	</div>

	<?php if ($integration->get_affiliate_url()) : ?>
		<div class="wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded wu-p-4 wu-mt-4">
			<p class="wu-text-blue-800 wu-m-0">
				<?php echo wp_kses($integration->get_signup_instructions(), wu_kses_allowed_html()); ?>
			</p>
		</div>
	<?php endif; ?>

</div>

<!-- Submit Box -->
<div class="wu-flex wu-justify-end wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url(wu_network_admin_url('wp-ultimo-settings&tab=email-accounts')); ?>" class="button button-primary button-large">
		<?php esc_html_e('Back to Settings &rarr;', 'ultimate-multisite'); ?>
	</a>

</div>
<!-- End Submit Box -->
