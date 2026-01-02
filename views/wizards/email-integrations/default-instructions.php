<?php
/**
 * Email provider integrations default instructions view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;
?>
<h1>
	<?php
	printf(
		/* translators: %s is the provider name */
		esc_html__('%s Setup Instructions', 'ultimate-multisite'),
		esc_html($integration->get_title())
	);
	?>
</h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4">
	<?php esc_html_e('Follow these steps to configure the email provider integration:', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-white wu-p-4 wu--mx-6">

	<ol class="wu-list-decimal wu-pl-6 wu-space-y-4">

		<li>
			<strong><?php esc_html_e('Get your API credentials', 'ultimate-multisite'); ?></strong>
			<p class="wu-text-gray-600 wu-mt-1">
				<?php
				printf(
					/* translators: %s is the provider name */
					esc_html__('Log in to your %s account and navigate to the API or Settings section to obtain your API credentials.', 'ultimate-multisite'),
					esc_html($integration->get_title())
				);
				?>
			</p>
		</li>

		<li>
			<strong><?php esc_html_e('Configure DNS records', 'ultimate-multisite'); ?></strong>
			<p class="wu-text-gray-600 wu-mt-1">
				<?php esc_html_e('For each domain that will be used for email, you\'ll need to configure the appropriate DNS records (MX, SPF, DKIM, etc.).', 'ultimate-multisite'); ?>
			</p>
		</li>

		<li>
			<strong><?php esc_html_e('Enter credentials', 'ultimate-multisite'); ?></strong>
			<p class="wu-text-gray-600 wu-mt-1">
				<?php esc_html_e('In the next step, enter your API credentials to connect Ultimate Multisite with the email provider.', 'ultimate-multisite'); ?>
			</p>
		</li>

	</ol>

	<?php if ($integration->get_documentation_link()) : ?>
		<div class="wu-mt-6 wu-p-4 wu-bg-blue-50 wu-border wu-border-blue-200 wu-rounded">
			<p class="wu-text-blue-800 wu-m-0">
				<span class="dashicons dashicons-info wu-mr-1"></span>
				<?php
				// translators: %1$s is the provider name, %2$s is the documentation URL
				$instructions_text = __('For detailed setup instructions, see the <a href="%2$s" target="_blank">%1$s documentation</a>.', 'ultimate-multisite');
				printf(
					wp_kses($instructions_text, wu_kses_allowed_html()),
					esc_html($integration->get_title()),
					esc_url($integration->get_documentation_link())
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ($integration->get_affiliate_url()) : ?>
		<div class="wu-mt-4 wu-p-4 wu-bg-green-50 wu-border wu-border-green-200 wu-rounded">
			<p class="wu-text-green-800 wu-m-0">
				<?php echo wp_kses($integration->get_signup_instructions(), wu_kses_allowed_html()); ?>
			</p>
		</div>
	<?php endif; ?>

</div>
