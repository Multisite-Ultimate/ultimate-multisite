<?php
/**
 * Ready view.
 *
 * @since 2.0.0
 */

?>
<div class="wu-bg-white wu-p-4 wu--mx-6 wu-flex wu-content-center" style="height: 400px;">
	<div class="wu-self-center wu-text-center wu-w-full">
		<span class="dashicons dashicons-warning wu-w-auto wu-h-auto wu-text-5xl wu-mb-2"></span>
		<h1 class="wu-text-gray-800">
			<?php esc_html_e('Caution!', 'multisite-ultimate'); ?>
		</h1>
		<p class="wu-text-lg wu-text-gray-600 wu-my-4">
			<?php esc_html_e('This action is irreversible and may cause unexpected behavior in your data, be sure of what you are doing and have a backup in case of some trouble!', 'multisite-ultimate'); ?>
		</p>
		<p class="wu-text-lg wu-text-gray-600 wu-my-4">
			<?php esc_html_e('This will forcely rerun our Migration Wizard on your installation. If you tried to migrate after install but your v1 data is missing, this can resolve.', 'multisite-ultimate'); ?>
		</p>
	</div>
</div>
<!-- Submit Box -->
<div class="wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">
	<span class="wu-float-right">
		<button name="next" value="1" class="wu-next-button button button-primary button-large wu-ml-2">
			<?php esc_html_e('Proceed', 'multisite-ultimate'); ?>
		</button>
	</span>
</div>
<!-- End Submit Box -->