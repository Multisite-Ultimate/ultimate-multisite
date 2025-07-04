<?php
/**
 * Ready view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-bg-white wu-p-4 wu--mx-6 wu-flex wu-content-center" style="height: 400px;">

	<div class="wu-self-center wu-text-center wu-w-full">

	<span class="dashicons dashicons-yes-alt wu-text-green-400 wu-w-auto wu-h-auto wu-text-5xl wu-mb-2"></span>

	<h1 class="wu-text-gray-800">
		<?php // translators: %s customer's name ?>
		<?php echo esc_html(sprintf(__('We are ready, %s!', 'multisite-ultimate'), apply_filters('wu_setup_step_done_name', $page->customer->first ?? __('my friend', 'multisite-ultimate')))); ?>
	</h1>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %1$s developer's name, %2$s company name ?>
		<?php printf(esc_html__('Multisite Ultimate would not be possible without the work of %1$s and %2$s.', 'multisite-ultimate'), '<a href="https://wpultimo.com/" target="_blank">Arindo Duque</a>', '<a href="https://nextpress.co" target="_blank">NextPress</a>'); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %s GitHub link ?>
		<?php printf(esc_html__('Multisite Ultimate is maintained by volunteer open source developers. Please consider sponsoring the project on %s.', 'multisite-ultimate'), '<a href="https://github.com/superdav42/wp-multisite-waas" target="_blank">GitHub</a>'); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %s support page link ?>
		<?php printf(esc_html__('Paid support is available. Go to %s to find an expert who can assist in setting up Multisite Ultimate or custom development.', 'multisite-ultimate'), sprintf('<a href="https://wpmultisitewaas.org/support" target="_blank">%s</a>', esc_html__('The Support Page', 'multisite-ultimate'))); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php esc_html_e('You now have everything you need in place to start building your Website as a Service business!', 'multisite-ultimate'); ?>
	</p>

	<p>
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wpmultisitewaas.org" data-text="<?php echo esc_attr('I just created my own premium WordPress site network with #wpmultisitewaas'); ?>" data-via="WPUltimo" data-size="large">Tell the World!</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</p>

	</div>

</div>

<!-- Submit Box -->
<div class="wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<span class="wu-float-right">

	<a href="<?php echo esc_url(network_admin_url('index.php')); ?>" class="button button-primary button-large">
	<?php esc_html_e('Thanks!', 'multisite-ultimate'); ?>
	</a>

	</span>

</div>
<!-- End Submit Box -->

