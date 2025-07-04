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
	$slug    = 'taxes_by_code';
	$headers = [
		__('Tax', 'multisite-ultimate'),
		__('Rate', 'multisite-ultimate'),
		__('Orders', 'multisite-ultimate'),
		__('Tax Total', 'multisite-ultimate'),
	];

	foreach ($taxes_by_rate as $tax_line) {
		$line = [
			wu_get_isset($tax_line, 'title', 'No Name'),
			$tax_line['tax_rate'],
			$tax_line['order_count'],
			wu_format_currency($tax_line['tax_total']),
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
			<th><?php esc_html_e('Tax', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Rate', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Orders', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Tax Total', 'multisite-ultimate'); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php if ($taxes_by_rate) : ?>

				<?php foreach ($taxes_by_rate as $tax_line) : ?>

				<tr>
					<td><?php echo esc_html(wu_get_isset($tax_line, 'title', 'No Name')); ?></td>
					<td><?php echo esc_html($tax_line['tax_rate']); ?>%</td>
					<td><?php echo esc_html($tax_line['order_count']); ?></td>
					<td><?php echo esc_html(wu_format_currency($tax_line['tax_total'])); ?></td>
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
