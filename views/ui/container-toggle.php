<?php
/**
 * Jumper trigger view.
 *
 * @since 2.0.0
 */
?>
<small>
	<strong>
	<a id="wu-container-toggle" role="tooltip" aria-label='<?php esc_html_e('Toggle container', 'multisite-ultimate'); ?>' href="#" class="wu-tooltip wu-inline-block wu-py-1 wu-pl-2 md:wu-pr-3 wu-uppercase wu-text-gray-600 wu-no-underline">

		<span title="<?php esc_attr_e('Boxed', 'multisite-ultimate'); ?>" class="wu-use-container dashicons dashicons-wu-arrow-with-circle-left wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
		<span class="wu-font-bold wu-use-container">
		<?php esc_attr_e('Boxed', 'multisite-ultimate'); ?>
		</span>

		<span title="<?php esc_attr_e('Boxed', 'multisite-ultimate'); ?>" class="wu-no-container dashicons dashicons-wu-arrow-with-circle-right wu-text-sm wu-w-auto wu-h-auto wu-align-text-bottom wu-relative"></span>
		<span class="wu-font-bold wu-no-container">
		<?php esc_attr_e('Wide', 'multisite-ultimate'); ?>
		</span>

	</a>
	</strong>
</small>

<style>
body.has-wu-container .wu-no-container {
	display: none;
}
body:not(.has-wu-container) .wu-use-container {
	display: none;
}
</style>

<script>
(function($) {

	$(document).ready(function() {

	$('#wu-container-toggle').on('click', function(e) {

		e.preventDefault();

		wu_block_ui('#wpcontent');

		$.ajax(ajaxurl + '?action=wu_toggle_container&nonce=<?php echo esc_js(wp_create_nonce('wu_toggle_container')); ?>').done(function() {

		$('.wrap').toggleClass('admin-lg:wu-container admin-lg:wu-mx-auto');

		$('body').toggleClass('has-wu-container');

		wu_block_ui('#wpcontent').unblock();

		});;

	});

	});

}(jQuery));
</script>
