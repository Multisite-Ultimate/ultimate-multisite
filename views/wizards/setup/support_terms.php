<?php
/**
 * Support terms view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<div class="wu--mt-7">
	<p><?php esc_html_e('This plugin comes with support for issues you may have. Support can be requested via email on <a class="wu-no-underline" href="mailto:support@wpultimo.com" target="_blank">support@wpultimo.com</a> and includes:', 'ultimate-multisite'); ?></p>

	<ul class="support-available">
	<li class="wu-text-green-700">
		<span class="dashicons-wu-check"></span>
		<?php esc_html_e('Availability of the author to answer questions', 'ultimate-multisite'); ?>
	</li>
	<li class="wu-text-green-700">
		<span class="dashicons-wu-check"></span>
		<?php esc_html_e('Answering technical questions about item features', 'ultimate-multisite'); ?>
	</li>
	<li class="wu-text-green-700">
		<span class="dashicons-wu-check"></span>
		<?php esc_html_e('Assistance with reported bugs and issues', 'ultimate-multisite'); ?>
	</li>
	</ul>

	<p><?php esc_html_e('Support <strong>DOES NOT</strong> Include:', 'ultimate-multisite'); ?></p>

	<ul class="support-unavailable">
	<li class="wu-text-red-500">
		<span class="dashicons-wu-circle-with-cross wu-align-middle"></span>
		<?php esc_html_e('Customization services', 'ultimate-multisite'); ?>
	</li>
	<li class="wu-text-red-500">
		<span class="dashicons-wu-circle-with-cross wu-align-middle"></span>
		<?php esc_html_e('Installation services', 'ultimate-multisite'); ?>
	</li>
	<li class="wu-text-red-500">
		<span class="dashicons-wu-circle-with-cross wu-align-middle"></span>
		<?php esc_html_e('Support for 3rd party plugins (i.e. plugins you install yourself later on)', 'ultimate-multisite'); ?>
	</li>
	</ul>

</div>
