<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<div class="wu-styling">

	<div class="wu-widget-inset">

	<?php

	$data    = [];
	$slug    = 'taxes_by_day';
	$headers = [
		__('Day', 'ultimate-multisite'),
		__('Orders', 'ultimate-multisite'),
		__('Total Sales', 'ultimate-multisite'),
		__('Tax Total', 'ultimate-multisite'),
		__('Net Profit', 'ultimate-multisite'),
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
			<th class="wu-w-1/3"><?php esc_html_e('Day', 'ultimate-multisite'); ?></th>
			<th><?php esc_html_e('Orders', 'ultimate-multisite'); ?></th>
			<th><?php esc_html_e('Total Sales', 'ultimate-multisite'); ?></th>
			<th><?php esc_html_e('Tax Total', 'ultimate-multisite'); ?></th>
			<th><?php esc_html_e('Net Profit', 'ultimate-multisite'); ?></th>
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
					<?php esc_html_e('No Taxes found.', 'ultimate-multisite'); ?>
				</td>
				</tr>

			<?php endif; ?>

		</tbody>

	</table>

	</div>

</div>
