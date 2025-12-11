<?php
/**
 * Runcloud instructions view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<h1><?php esc_html_e('Instructions', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6">
	<?php esc_html_e('You’ll need to get your', 'ultimate-multisite'); ?> <strong><?php esc_html_e('API Token', 'ultimate-multisite'); ?></strong> , <?php esc_html_e('as well as find the', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Server ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('and', 'ultimate-multisite'); ?> <strong><?php esc_html_e('APP ID', 'ultimate-multisite'); ?></strong> <?php esc_html_e('for your WordPress application', 'ultimate-multisite'); ?>.
</p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key-and-secret">
	<?php esc_html_e('Getting the API Token', 'ultimate-multisite'); ?>
</h3>

<p class="wu-text-sm">
	<?php esc_html_e('On your RunCloud admin panel, click the cog icon (settings) to go to the settings page', 'ultimate-multisite'); ?>.
</p>

<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('runcloud-1.webp', 'img/hosts')); ?>">
</div>

<p class="wu-text-center"><i><?php esc_html_e('Settings Page Link', 'ultimate-multisite'); ?></i></p>

<p class="wu-text-sm"><?php esc_html_e('On the new page, click in the', 'ultimate-multisite'); ?><b> <?php esc_html_e('API', 'ultimate-multisite'); ?> </b> <?php esc_html_e('menu item on the left', 'ultimate-multisite'); ?>.</p>

<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('runcloud-2.webp', 'img/hosts')); ?>">
</div>
<p class="wu-text-center"><i><?php esc_html_e('API page link', 'ultimate-multisite'); ?></i></p>
<p class="wu-text-sm"> <?php esc_html_e('Create a new token', 'ultimate-multisite'); ?> <b> <?php esc_html_e('API Token', 'ultimate-multisite'); ?> </b>, <?php esc_html_e('we will need them in the next steps', 'ultimate-multisite'); ?>. <b> <?php esc_html_e('Make sure the RunCloud API toggle is turned ON', 'ultimate-multisite'); ?>, </b> <?php esc_html_e('otherwise RunCloud won’t accept Ultimate Multisite API calls', 'ultimate-multisite'); ?>.</p>
<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('runcloud-3.webp', 'img/hosts')); ?>">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Copy the API Token', 'ultimate-multisite'); ?></i></p>

<h3 class="wu-m-0 wu-py-4 wu-text-lg" id="step-1-getting-the-api-key-and-secret">
	<?php esc_html_e('Getting the Server and App IDs', 'ultimate-multisite'); ?>
</h3>
<p class="wu-text-sm"><?php esc_html_e('To find what are the server and app ids for your application, navigate to your web application manage page inside the RunCloud panel. Once you are there, you’ll be able to extract the values from the URL', 'ultimate-multisite'); ?>.</p>
<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('runcloud-4.webp', 'img/hosts')); ?>">
</div>
<p class="wu-text-center"><i><?php esc_html_e('Server ID is the first one, the second one is App ID.', 'ultimate-multisite'); ?></i></p>
<p class="wu-text-sm"><?php esc_html_e('Save the Server and APP id values as they will be necessary in the next step', 'ultimate-multisite'); ?>.</p>
