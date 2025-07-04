<?php
/**
 * Total payments view.
 *
 * @since 2.0.0
 */
?>
<div id="payments-tax-breakthrough" class="wu-widget-inset">

	<table class="wp-list-table widefat striped payments wu-border-0">
	<tbody>

		<?php if ( ! empty($tax_breakthrough)) : ?>

			<?php foreach ($tax_breakthrough as $tax_rate => $tax_total) : ?>
			<tr>
			<td><?php echo esc_html($tax_rate); ?>%</td>
			<td><?php echo esc_html(wu_format_currency($tax_total)); ?></td>
			</tr>
		<?php endforeach; ?>

			<?php if ( ! empty($payment)) : ?>
			<tr>
			<td><span class="wu-font-bold wu-uppercase wu-text-xs wu-text-gray-700"><?php esc_html_e('Total', 'multisite-ultimate'); ?></span></td>
			<td><?php echo esc_html(wu_format_currency($payment->get_tax_total())); ?></td>
			</tr>
		<?php endif; ?>

		<?php else : ?>

		<tr>
			<td colspan="2">
			<?php esc_html_e('No tax rates.', 'multisite-ultimate'); ?>
			</td>
		</tr>

		<?php endif; ?>

	</tbody>
	</table>

</div>

<style>
#wu-line_item_list_table .tablenav.bottom,
#wu-line_item_list_table tfoot {
	display: none;
}
</style>
