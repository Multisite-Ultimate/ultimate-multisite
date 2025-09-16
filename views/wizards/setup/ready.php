<?php
/**
 * Ready view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wu-bg-white wu-p-4 wu--mx-6 wu-flex wu-content-center" style="height: 400px;">

	<div class="wu-self-center wu-text-center wu-w-full">

	<span class="dashicons dashicons-yes-alt wu-text-green-400 wu-w-auto wu-h-auto wu-text-5xl wu-mb-2"></span>

	<h1 class="wu-text-gray-800">
		<?php // translators: %s customer's name ?>
		<?php echo esc_html(sprintf(__('We are ready, %s!', 'ultimate-multisite'), apply_filters('wu_setup_step_done_name', $page->customer->first ?? __('my friend', 'ultimate-multisite')))); ?>
	</h1>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %1$s developer's name, %2$s company name ?>
		<?php printf(esc_html__('Multisite Ultimate would not be possible without the work of %1$s and %2$s.', 'ultimate-multisite'), '<a href="https://wpultimo.com/" target="_blank">Arindo Duque</a>', '<a href="https://nextpress.co" target="_blank">NextPress</a>'); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %s GitHub link ?>
		<?php printf(esc_html__('Multisite Ultimate is maintained by volunteer open source developers. Please consider sponsoring the project on %s.', 'ultimate-multisite'), '<a href="https://github.com/superdav42/wp-multisite-waas" target="_blank">GitHub</a>'); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php // translators: %s support page link ?>
		<?php printf(esc_html__('Paid support is available. Go to %s to find an expert who can assist in setting up Multisite Ultimate or custom development.', 'ultimate-multisite'), sprintf('<a href="https://multisiteultimate.com/support" target="_blank">%s</a>', esc_html__('The Support Page', 'ultimate-multisite'))); ?>
	</p>

	<p class="wu-text-lg wu-text-gray-600 wu-my-4">
		<?php esc_html_e('You now have everything you need in place to start building your Website as a Service business!', 'ultimate-multisite'); ?>
	</p>

	</div>

</div>

<!-- Submit Box -->
<div class="wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

	<span class="wu-float-right">

	<a href="<?php echo esc_url(network_admin_url('index.php')); ?>" class="button button-primary button-large" data-testid="button-primary">
	<?php esc_html_e('Thanks!', 'ultimate-multisite'); ?>
	</a>

	</span>

</div>
<!-- End Submit Box -->

