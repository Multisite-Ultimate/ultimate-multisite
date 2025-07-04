<?php
/**
 * Host integrations configuration results view.
 *
 * @since 2.0.0
 */
?>
<h1>
	<?php esc_html_e('We are almost there!', 'multisite-ultimate'); ?>
</h1>

<p class="wu-text-lg wu-text-gray-600 wu-my-4">
	<?php esc_html_e('You should have all the information we need in hand right now. The next step is to configure it.', 'multisite-ultimate'); ?>
</p>

<div class="wu-mt-6">

	<?php if ( ! $integration->is_enabled()) : ?>

	<li class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
		<span class="dashicons dashicons-yes-alt wu-text-green-400 wu-self-center wu-mr-2"></span>
		<span>
		<?php esc_html_e('All set! We have made all the adjustments to and the Integration should work.', 'multisite-ultimate'); ?>
		</span>
	</li>

	<?php else : ?>

	<li class="wu-flex wu-rounded wu-content-center wu-py-2 wu-px-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-m-0">
		<span><?php esc_html_e('You will need to edit your wp-config.php file manually. Copy the contents of the box below and paste it on your wp-config.php file, right before the line containing:', 'multisite-ultimate'); ?> <code>/* That\'s all, stop editing! Happy publishing. */</code></span>
	</li>

	<h3 class="wu-mt-6"><?php esc_html_e('Your wp-config.php settings:', 'multisite-ultimate'); ?></h3>

	<pre class="wu-overflow-auto wu-p-4 wu-rounded wu-content-center wu-bg-gray-800 wu-text-white wu-font-mono wu-border wu-border-solid wu-border-gray-300"><?php echo esc_html($integration->get_constants_string($post)); ?></pre>

	<?php endif; ?>

</div>

<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<a href="<?php echo esc_url(wu_network_admin_url('wp-ultimo-settings&tab=integrations')); ?>" class="wu-self-center button button-large wu-float-left"><?php esc_html_e('&larr; Cancel', 'multisite-ultimate'); ?></a>

	<span class="wu-self-center wu-content-center wu-flex">

	<button name="submit" value="3" class="wu-ml-2 button button-primary button-large">
		<?php esc_html_e('Test Integration &rarr;', 'multisite-ultimate'); ?>
	</button>

	</span>

</div>
<!-- End Submit Box -->

