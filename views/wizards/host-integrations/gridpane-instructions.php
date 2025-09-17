<?php
/**
 * Gridpane instructions view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<h1><?php esc_html_e('Instructions', 'ultimate-multisite'); ?></h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4 wu-mb-6"><?php esc_html_e('Setting up GridPane with WP Ultimo is as easy as toggling a switch', 'ultimate-multisite'); ?>!</p>

<p class="wu-text-sm">
	<?php esc_html_e('On the GridPane panel, go to', 'ultimate-multisite'); ?> <a class="wu-no-underline" href="https://my.gridpane.com/sites" target="_blank"><?php esc_html_e('Sites', 'ultimate-multisite'); ?></a>. <?php esc_html_e('Click on your network site to bring up the options modal', 'ultimate-multisite'); ?>.
</p>

<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('gridpane-1.webp', 'img/hosts')); ?>">
</div>

<p class="wu-text-sm">

	<?php esc_html_e('Go to the', 'ultimate-multisite'); ?> <strong><?php esc_html_e('Multisite', 'ultimate-multisite'); ?></strong> <?php esc_html_e('tab and toggle the', 'ultimate-multisite'); ?> <strong><?php esc_html_e('WP Ultimo Integration', 'ultimate-multisite'); ?></strong> <?php esc_html_e('switch', 'ultimate-multisite'); ?>.

</p>

<div class="">
	<img class="wu-w-full" src="<?php echo esc_url(wu_get_asset('gridpane-2.webp', 'img/hosts')); ?>">
</div>

<p class="wu-text-sm">
	<?php esc_html_e("You're all set", 'ultimate-multisite'); ?>!
</p>
