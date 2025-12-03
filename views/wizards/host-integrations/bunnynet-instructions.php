<?php
/**
 * BunnyNet instructions view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<h1>
<?php esc_html_e('Instructions', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">

	<?php esc_html_e('You'll need to get your', 'ultimate-multisite'); ?> <strong><?php esc_html_e('API Key', 'ultimate-multisite'); ?></strong> <?php esc_html_e('and', 'ultimate-multisite'); ?> <strong><?php esc_html_e('DNS Zone ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('for your BunnyNet DNS zone.', 'ultimate-multisite'); ?>

</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key">
	<?php esc_html_e('Step 1: Getting Your API Key', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('Log in to your BunnyNet account and navigate to your Account Settings page.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('In the Account Settings, you\'ll find your Account API Key. This is the key you\'ll use to authenticate with the BunnyNet API.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm wu-bg-yellow-100 wu-p-4 wu-text-yellow-800 wu-rounded">
	<strong><?php esc_html_e('Important:', 'ultimate-multisite'); ?></strong>
	<?php esc_html_e('Keep your API key secure and never share it publicly. This key provides access to your entire BunnyNet account.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-2-creating-dns-zone">
	<?php esc_html_e('Step 2: Creating or Finding Your DNS Zone', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('Navigate to the DNS section in your BunnyNet dashboard.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('If you haven\'t already created a DNS zone for your domain, click on "Add DNS Zone" and enter your domain name.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('Once the zone is created (or if you already have one), click on it to view the zone details.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-3-getting-zone-id">
	<?php esc_html_e('Step 3: Getting Your DNS Zone ID', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('In your DNS Zone details page, you\'ll find the Zone ID. This is typically displayed near the top of the page or in the zone\'s properties.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('Copy this Zone ID - it\'s a numeric value that uniquely identifies your DNS zone.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-4-dns-configuration">
	<?php esc_html_e('Step 4: DNS Configuration', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('To use BunnyNet DNS with your domain, you need to update your domain\'s nameservers at your domain registrar to point to BunnyNet\'s nameservers:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-8 wu-text-sm">
	<li>bunnycdn-dns-a.net</li>
	<li>bunnycdn-dns-b.net</li>
</ul>

<p class="wu-text-sm wu-bg-blue-100 wu-p-4 wu-text-blue-600 wu-rounded wu-mt-4">
	<strong><?php esc_html_e('Note:', 'ultimate-multisite'); ?></strong>
	<?php esc_html_e('DNS propagation can take up to 24-48 hours. After updating your nameservers, wait for the changes to fully propagate before testing the integration.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-5-subdomain-setup">
	<?php esc_html_e('Step 5: Understanding Subdomain Management', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('This integration will automatically create DNS records in your BunnyNet zone for each new subdomain created in your WordPress Multisite network.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('When a new site is created, the integration will:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-list-disc wu-ml-8 wu-text-sm">
	<li><?php esc_html_e('Create an A record pointing to your server\'s IP address', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Optionally create a www version of the subdomain (if configured)', 'ultimate-multisite'); ?></li>
	<li><?php esc_html_e('Automatically remove DNS records when sites are deleted', 'ultimate-multisite'); ?></li>
</ul>

<p class="wu-text-sm wu-bg-green-100 wu-p-4 wu-text-green-800 wu-rounded wu-mt-4">
	<strong><?php esc_html_e('Ready to proceed?', 'ultimate-multisite'); ?></strong>
	<?php esc_html_e('Once you have your API Key and Zone ID, enter them in the configuration step to complete the integration setup.', 'ultimate-multisite'); ?>
</p>
