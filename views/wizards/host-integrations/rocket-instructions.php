<?php
/**
 * Rocket.net instructions view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<h1><?php esc_html_e('Instructions', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">
	<?php esc_html_e('You\'ll need to get your', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Rocket.net account credentials', 'ultimate-multisite'); ?></strong> <?php esc_html_e('and', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Site ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('from your Rocket.net control panel.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="about-rocket-api">
	<?php esc_html_e('About the Rocket.net API', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('Rocket.net is one of the few Managed WordPress platforms that is 100% API-driven. The same API that powers their control panel is available to you for managing domains, SSL certificates, and more.', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-blue-100 wu-border-l-4 wu-border-blue-500 wu-p-4 wu-my-4">
	<p class="wu-text-sm wu-m-0">
		<strong><?php esc_html_e('Note:', 'ultimate-multisite'); ?></strong>
		<?php esc_html_e('The Rocket.net API uses JWT authentication. Your credentials are only used to generate secure access tokens and are never stored by Ultimate Multisite.', 'ultimate-multisite'); ?>
	</p>
</div>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-account-credentials">
	<?php esc_html_e('Step 1: Prepare Your Account Credentials', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('You will need:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-6 wu-text-sm">
	<li><strong><?php esc_html_e('Email:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('Your Rocket.net account email address', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('Password:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('Your Rocket.net account password', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('Site ID:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('The numeric ID of your WordPress site on Rocket.net', 'ultimate-multisite'); ?></li>
</ul>

<div class="wu-bg-yellow-100 wu-border-l-4 wu-border-yellow-500 wu-p-4 wu-my-4">
	<p class="wu-text-sm wu-m-0">
		<strong><?php esc_html_e('Security Tip:', 'ultimate-multisite'); ?></strong>
		<?php esc_html_e('Consider creating a dedicated Rocket.net user account specifically for API access with appropriate permissions. This follows security best practices for API integrations.', 'ultimate-multisite'); ?>
	</p>
</div>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-2-finding-site-id">
	<?php esc_html_e('Step 2: Finding Your Site ID', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('To find your Site ID:', 'ultimate-multisite'); ?>
</p>

<ol class="wu-list-decimal wu-ml-6 wu-text-sm wu-space-y-2">
	<li><?php esc_html_e('Log in to your Rocket.net control panel', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Navigate to your WordPress site', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Look at the URL in your browser - the Site ID is the numeric value in the URL path', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('For example, if the URL is', 'ultimate-multisite'); ?> <code>https://control.rocket.net/sites/12345/overview</code>, <?php esc_html_e('your Site ID is', 'ultimate-multisite'); ?> <strong>12345</strong></li>
</ol>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-3-configure-integration">
	<?php esc_html_e('Step 3: Configure the Integration', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('In the next step, you will enter:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-6 wu-text-sm wu-space-y-2">
	<li><strong><?php esc_html_e('WU_ROCKET_EMAIL:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('Your Rocket.net account email', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('WU_ROCKET_PASSWORD:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('Your Rocket.net account password', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('WU_ROCKET_SITE_ID:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('The Site ID you found in the previous step', 'ultimate-multisite'); ?></li>
</ul>

<p class="wu-text-sm wu-mt-4">
	<?php esc_html_e('These values will be added to your wp-config.php file as PHP constants for secure storage.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="features">
	<?php esc_html_e('What This Integration Does', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('Once configured, this integration will:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-6 wu-text-sm wu-space-y-1">
	<li><?php esc_html_e('Automatically add custom domains to your Rocket.net site when mapped in Ultimate Multisite', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Automatically remove domains from Rocket.net when unmapped', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Enable automatic SSL certificate provisioning for all mapped domains', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Keep your Rocket.net configuration in sync with your WordPress multisite network', 'ultimate-multisite'); ?></li>
</ul>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="resources">
	<?php esc_html_e('Additional Resources', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('For more information about the Rocket.net API:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-6 wu-text-sm">
	<li>
		<a href="https://support.rocket.net/hc/en-us/articles/41705974971035-How-to-use-the-Rocket-net-API" target="_blank" rel="noopener noreferrer" class="wu-text-blue-600 hover:wu-underline">
			<?php esc_html_e('Rocket.net API Guide', 'ultimate-multisite'); ?>
		</a>
	</li>
	<li>
		<a href="https://rocketdotnet.readme.io/" target="_blank" rel="noopener noreferrer" class="wu-text-blue-600 hover:wu-underline">
			<?php esc_html_e('Rocket.net API Documentation', 'ultimate-multisite'); ?>
		</a>
	</li>
</ul>
