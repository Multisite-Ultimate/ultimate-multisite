<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

	<div class="wu-widget-inset">

	<?php

	$data    = [];
	$slug    = 'taxes_by_day';
	$headers = [
		__('Day', 'multisite-ultimate'),
		__('Orders', 'multisite-ultimate'),
		__('Total Sales', 'multisite-ultimate'),
		__('Tax Total', 'multisite-ultimate'),
		__('Net Profit', 'multisite-ultimate'),
	];

	foreach ($taxes_by_day as $day => $tax_line) {
		$line = [
			date_i18n(get_option('date_format'), strtotime($day)),
			$tax_line['order_count'],
			wu_format_currency($tax_line['total']),
			wu_format_currency($tax_line['tax_total']),
			wu_format_currency($tax_line['net_profit']),
		];

		$data[] = $line;
	}

	$page->render_csv_button(
		[
			'headers' => $headers,
			'data'    => $data,
			'slug'    => $slug,
		]
	);

	?>

	<table class="wp-list-table widefat fixed striped wu-border-none">

		<thead>
			<tr>
			<th class="wu-w-1/3"><?php esc_html_e('Day', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Orders', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Total Sales', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Tax Total', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Net Profit', 'multisite-ultimate'); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php if ($taxes_by_day) : ?>

				<?php foreach ($taxes_by_day as $day => $tax_line) : ?>

				<tr>
					<td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($day))); ?></td>
					<td><?php echo intval($tax_line['order_count']); ?></td>
					<td><?php echo esc_html(wu_format_currency($tax_line['total'])); ?></td>
					<td><?php echo esc_html(wu_format_currency($tax_line['tax_total'])); ?></td>
					<td><?php echo esc_html(wu_format_currency($tax_line['net_profit'])); ?></td>
				</tr>

			<?php endforeach; ?>

			<?php else : ?>

				<tr>
				<td colspan="4">
					<?php esc_html_e('No Taxes found.', 'multisite-ultimate'); ?>
				</td>
				</tr>

			<?php endif; ?>

		</tbody>

	</table>

	</div>

</div>
