<?php
/**
 * Product List Actions
 *
 * @since 2.0.0
 */
?>
<div>

	<div class="wu-flex wu-p-4 wu-bg-gray-100 wu--mx-3 wu--mb-3 wu-mt-3 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid">

	<div class="wu-ml-auto">

		<a 
		class="button wu-ml-2 wubox"
		title="<?php esc_attr_e('Add new Product', 'multisite-ultimate'); ?>"
		href="<?php echo esc_attr(wu_get_form_url('edit_membership_product', ['id' => $membership->get_id()])); ?>"
		>
		<span class="dashicons-wu-circle-with-plus wu-align-text-bottom"></span>
		<?php esc_html_e('Add new Product', 'multisite-ultimate'); ?>
		</a>

	</div>

	</div>

</div>
