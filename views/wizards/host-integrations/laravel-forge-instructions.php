<?php
/**
 * Laravel Forge instructions view.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;
?>
<h1><?php esc_html_e('Instructions', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">
	<?php esc_html_e('You\'ll need to get your', 'ultimate-multisite'); ?> <strong><?php esc_html_e('API Token', 'ultimate-multisite'); ?></strong>, <?php esc_html_e('as well as find the', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Server ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('and', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Site ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('for your WordPress application', 'ultimate-multisite'); ?>.
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-token">
	<?php esc_html_e('Step 1: Getting the API Token', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('Log into your Laravel Forge account and navigate to', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Account Settings', 'ultimate-multisite'); ?></strong> (<?php esc_html_e('click your profile icon in the top right corner', 'ultimate-multisite'); ?>).
</p>

<p class="wu-text-sm">
	<?php esc_html_e('In the sidebar, click on', 'ultimate-multisite'); ?> <strong><?php esc_html_e('API', 'ultimate-multisite'); ?></strong>.
</p>

<p class="wu-text-sm">
	<?php esc_html_e('Click', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Create API Token', 'ultimate-multisite'); ?></strong>, <?php esc_html_e('give it a name (e.g., "Ultimate Multisite"), and save the generated token securely.', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-yellow-100 wu-border wu-border-yellow-400 wu-text-yellow-800 wu-p-3 wu-rounded wu-my-4">
	<strong><?php esc_html_e('Important:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('The API token is only shown once. Make sure to copy and save it before closing the modal.', 'ultimate-multisite'); ?>
</div>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-2-getting-server-and-site-ids">
	<?php esc_html_e('Step 2: Getting the Server and Site IDs', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('To find your Server ID and Site ID, navigate to your site in the Forge dashboard.', 'ultimate-multisite'); ?>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('The URL will look like:', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-gray-100 wu-p-3 wu-rounded wu-font-mono wu-text-sm wu-my-4">
	https://forge.laravel.com/servers/<strong class="wu-text-blue-600">{SERVER_ID}</strong>/sites/<strong class="wu-text-green-600">{SITE_ID}</strong>
</div>

<p class="wu-text-sm">
	<?php esc_html_e('For example, if your URL is', 'ultimate-multisite'); ?> <code>https://forge.laravel.com/servers/847175/sites/12345678</code>, <?php esc_html_e('then:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-text-sm wu-list-disc wu-ml-6">
	<li><strong><?php esc_html_e('Server ID', 'ultimate-multisite'); ?>:</strong> 847175</li>
	<li><strong><?php esc_html_e('Site ID', 'ultimate-multisite'); ?>:</strong> 12345678</li>
</ul>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-3-load-balancer-optional">
	<?php esc_html_e('Step 3: Load Balancer Setup (Optional)', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('If you\'re using a load balancer with multiple backend servers, you\'ll need additional configuration:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-text-sm wu-list-disc wu-ml-6">
	<li><strong><?php esc_html_e('Load Balancer Server ID', 'ultimate-multisite'); ?>:</strong> <?php esc_html_e('The server ID of your load balancer', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('Load Balancer Site ID', 'ultimate-multisite'); ?>:</strong> <?php esc_html_e('The site ID on your load balancer (this will be created automatically for new domains)', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('Additional Server IDs', 'ultimate-multisite'); ?>:</strong> <?php esc_html_e('Comma-separated list of additional backend server IDs', 'ultimate-multisite'); ?></li>
</ul>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-4-deploy-commands-optional">
	<?php esc_html_e('Step 4: Deploy Commands (Optional)', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('You can optionally configure commands to run after a new domain site is created:', 'ultimate-multisite'); ?>
</p>

<ul class="wu-text-sm wu-list-disc wu-ml-6">
	<li><strong><?php esc_html_e('Deploy Command', 'ultimate-multisite'); ?>:</strong> <?php esc_html_e('A custom shell command to run. Use {domain} as a placeholder for the domain name.', 'ultimate-multisite'); ?></li>
	<li><strong><?php esc_html_e('Symlink Target', 'ultimate-multisite'); ?>:</strong> <?php esc_html_e('Path to symlink new domains to. Useful for shared codebases.', 'ultimate-multisite'); ?></li>
</ul>

<p class="wu-text-sm">
	<?php esc_html_e('Example symlink target:', 'ultimate-multisite'); ?> <code>/home/forge/main-site.com/public</code>
</p>

<p class="wu-text-sm">
	<?php esc_html_e('This will create a symlink from the new domain\'s public folder to your main site\'s public folder.', 'ultimate-multisite'); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="ssl-certificates">
	<?php esc_html_e('SSL Certificates', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('This integration automatically requests Let\'s Encrypt SSL certificates for new domains. The certificate will include both the domain and its www subdomain (when applicable).', 'ultimate-multisite'); ?>
</p>

<div class="wu-bg-blue-100 wu-border wu-border-blue-400 wu-text-blue-800 wu-p-3 wu-rounded wu-my-4">
	<strong><?php esc_html_e('Note:', 'ultimate-multisite'); ?></strong> <?php esc_html_e('Ensure your domain\'s DNS is properly configured and propagated before adding it, as Let\'s Encrypt requires the domain to resolve to your server for certificate issuance.', 'ultimate-multisite'); ?>
</div>
