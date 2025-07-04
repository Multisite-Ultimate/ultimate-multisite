<?php
/**
 * Branding footer view.
 *
 * @since 2.0.0
 */
?>

<div id="wp-ultimo-footer" class="wu-pt-6 wu-pb-1 wu--mx-5 wu-mb-0 wu-text-gray-500 wu-text-center wu-bg-gray-100 wu-border-0 wu-border-gray-300 wu-border-t wu-border-solid">

	<ul id="wu-footer-nav" class="wu-text-xs wu-pb-0">
	<li class="wu-inline-block wu-mx-1 wu-font-medium">
		<?php // translators: %s: version number of plugin. ?>
		<?php printf(esc_html__('Version %s', 'multisite-ultimate'), wu_get_version()); ?>
	</li>

	<?php if (WP_Ultimo()->is_loaded()) : ?>

		<li class="wu-inline-block wu-mx-1">
		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-system-info')); ?>" class="wu-text-gray-500 hover:wu-text-gray-600">
			<?php esc_html_e('System Info', 'multisite-ultimate'); ?>
		</a>
		</li>
		<li class="wu-inline-block wu-mx-1">
		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-shortcodes')); ?>" class="wu-text-gray-500 hover:wu-text-gray-600">
			<?php esc_html_e('Available Shortcodes', 'multisite-ultimate'); ?>
		</a>
		</li>

	<?php endif; ?>

	<?php if (WP_Ultimo()->is_loaded()) : ?>

		<li class="wu-inline-block wu-mx-1">
		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-settings')); ?>" class="wu-text-gray-500 hover:wu-text-gray-600">
			<?php esc_html_e('Settings', 'multisite-ultimate'); ?>
		</a>
		</li>

	<?php endif; ?>

	<?php if (WP_Ultimo()->is_loaded()) : ?>

		<li class="wu-inline-block wu-mx-1">
		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-jobs')); ?>" class="wu-text-gray-500 hover:wu-text-gray-600">
			<?php esc_html_e('Job Queue', 'multisite-ultimate'); ?>
		</a>
		</li>

	<?php endif; ?>

	<?php do_action('wu_footer_left'); ?>
	<li class="wu-inline-block wu-mx-2">
		• <a href="https://wpmultisitewaas.org/support" class="wu-text-gray-500 hover:wu-text-gray-600">Support</a>
	</li>
	</ul>

</div>
