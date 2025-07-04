<?php
/**
 * Displays the Toolbox UI.
 *
 * @package WP_Ultimo/Views
 * @subpackage Toolbox
 * @since 2.0.0
 */

?>

<div id="wu-toolbox" class="wu-styling">

	<div
	class="wu-fixed wu-bottom-0 wu-right-0 wu-mr-4 wu-mb-4 wu-bg-white wu-px-4 wu-py-2 wu-pl-2 wu-shadow-md wu-rounded-full wu-uppercase wu-text-xs wu-text-gray-700">

	<ul class="wu-inline-block wu-m-0 wu-p-0 wu-align-middle wu-mx-1">
		<li class="wu-inline-block wu-m-0 wu-p-0">
		<span id="wu-toolbox-toggle" class="dashicons-before dashicons-wu-wp-ultimo" <?php echo wu_tooltip_text(__('Toggle Toolbox', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></span>
		</li>
	</ul>

	<ul id="wu-toolbox-links" class="wu-inline-block wu-m-0 wu-p-0 wu-align-middle wu-mx-1">

		<li class="wu-inline-block wu-m-0 wu-p-0 wu-px-2">

		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-edit-site', ['id' => $current_site->get_id()])); ?>"
			class="wu-inline-block wu-uppercase wu-text-gray-600 wu-no-underline">
			<span title="<?php esc_attr_e('Current Site', 'multisite-ultimate'); ?>"
			class="dashicons-wu-browser wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
			<span class="">
			<?php echo $current_site->get_title(); ?>
			</span>
		</a>

		</li>

		<?php if ($customer) : ?>

		<li class="wu-inline-block wu-m-0 wu-p-0 wu-px-2">

			<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-edit-customer', ['id' => $customer->get_id()])); ?>"
			class="wu-inline-block wu-uppercase wu-text-gray-600 wu-no-underline">
			<span title="<?php esc_attr_e('Current Site', 'multisite-ultimate'); ?>"
				class="dashicons-wu-user wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
			<span class="">
				<?php echo $customer->get_display_name(); ?>
			</span>
			</a>

		</li>

		<?php endif; ?>

		<?php if ($membership) : ?>

		<li class="wu-inline-block wu-m-0 wu-p-0 wu-px-2">

			<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-edit-membership', ['id' => $membership->get_id()])); ?>"
			class="wu-inline-block wu-uppercase wu-text-gray-600 wu-no-underline">
			<span title="<?php esc_attr_e('Current Site', 'multisite-ultimate'); ?>"
				class="dashicons-wu-circular-graph wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
			<span class="">
				<?php // translators: %s hash of membership. ?>
				<?php printf(__('Membership <Strong>%s</strong>', 'multisite-ultimate'), $membership->get_hash()); ?>
			</span>
			<span id="wu-toolbox-membership-status" class="wu-inline-block wu-w-3 wu-h-3 wu-rounded-full wu-align-text-top <?php echo esc_attr($membership->get_status_class()); ?>" <?php echo wu_tooltip_text($membership->get_status_label()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				&nbsp;
			</span>
			</a>

		</li>

		<?php endif; ?>

	</ul>

	<ul class="wu-inline-block wu-m-0 wu-p-0 wu-align-middle wu-mx-1">
		<li class="wu-inline-block wu-m-0 wu-p-0">

		<a id="wu-jumper-button-trigger" href="#"
			class="wu-inline-block wu-uppercase wu-text-gray-600 wu-no-underline">
			<span title="<?php esc_attr_e('Jumper', 'multisite-ultimate'); ?>"
			class="dashicons dashicons-wu-flash wu-text-sm wu-w-auto wu-h-auto wu-align-text-top wu-relative wu--mr-1"></span>
			<span class="wu-font-bold">
			<?php esc_attr_e('Jumper', 'multisite-ultimate'); ?>
			</span>
		</a>

		</li>
	</ul>

	</div>

</div>

<script>
if (typeof jQuery !== 'undefined') {
	(function($) {
	$(document).ready(function() {
		$('body').on('click', '#wu-toolbox-toggle', function() {
		$(this).parents('#wu-toolbox').toggleClass('wu-toolbox-closed');
		});
	});
	})(jQuery);
}
</script>

<style>
#wu-toolbox-links {
	transition: width 2s;
}
.wu-toolbox-closed #wu-toolbox-links {
	transition: width 2s;
	width: 1px;
	height: 1px;
	overflow: hidden;
}
#wu-toolbox-membership-status {
	margin-top: 2px;
}
</style>
