<?php
/**
 * Runcloud instructions view.
 *
 * @since 2.0.0
 */
?>
<h1>
<?php esc_html_e('Instructions', 'multisite-ultimate'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">

	<?php esc_html_e('You’ll need to get your', 'multisite-ultimate'); ?> <strong><?php esc_html_e('API Key', 'multisite-ultimate'); ?></strong> <?php esc_html_e('and', 'multisite-ultimate'); ?> <strong><?php esc_html_e('Zone ID', 'multisite-ultimate'); ?></strong> <?php esc_html_e('for your Cloudflare DNS zone.', 'multisite-ultimate'); ?>

</p>

<p class="wu-text-sm wu-bg-blue-100 wu-p-4 wu-text-blue-600 wu-rounded">
	<strong><?php esc_html_e('Before we start...', 'multisite-ultimate'); ?></strong><br>
	<?php // translators: %s the url ?>
	<?php wp_kses_post(sprintf(__('This integration is really aimed at people that do not have access to an Enterprise Cloudflare account, since that particular tier supports proxying on wildcard DNS entries, which makes adding each subdomain unecessary. If you own an enterprise tier account, you can simply follow <a class="wu-no-underline" href="%s" target="_blank">this tutorial</a> to create the wildcard entry and deactivate this integration entirely.', 'multisite-ultimate'), 'https://support.cloudflare.com/hc/en-us/articles/200169356-How-do-I-use-WordPress-Multi-Site-WPMU-With-Cloudflare')); ?>
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key-and-secret">
	<?php esc_html_e('Getting the Zone ID and API Key', 'multisite-ultimate'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('On the Cloudflare overview page of your Zone (the domain managed), you\'ll see a block on the sidebar containing the Zone ID. Copy that value.', 'multisite-ultimate'); ?>
</p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.08.14.png">
</div>

<p class="wu-text-center"><i><?php esc_html_e('DNS Zone ID on the Sidebar', 'multisite-ultimate'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('On that same sidebar block, you will see the Get your API token link. Click on it to go to the token generation screen.', 'multisite-ultimate'); ?></p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.12.19.png">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Go to the API Tokens tab, then click on Create Token', 'multisite-ultimate'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('We want an API token that will allow us to edit DNS records, so select the Edit zone DNS template.', 'multisite-ultimate'); ?></p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.15.03.png">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Use the Edit Zone DNS template', 'multisite-ultimate'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('On the next screen, set the permissions to Edit, and select the zone that corresponds to your target domain. Then, move to the next step.', 'multisite-ultimate'); ?></p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.17.58.png">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Permission and Zone Settings', 'multisite-ultimate'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('Finally, click Create Token.', 'multisite-ultimate'); ?></p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.19.52.png">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Finishing up.', 'multisite-ultimate'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('Copy the API Token (it won\'t be shown again, so you need to copy it now!). We will use it on the next step alongside with the Zone ID', 'multisite-ultimate'); ?></p>

<div class="">
	<img class="wu-w-full" src="https://wpultimo.com/wp-content/uploads/2021/04/Captura-de-Pantalla-2021-04-01-a-las-23.21.47.png">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Done!', 'multisite-ultimate'); ?></i></p>
