<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

	<ul class="lg:wu-flex wu-my-0 wu-mx-0">

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative" <?php echo wu_tooltip_text(__('MRR stands for Monthly Recurring Revenue', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<div>

		<strong class="wu-text-gray-800 wu-text-2xl md:wu-text-xl">
			<?php echo wu_format_currency($mrr); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('MRR', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

		<div>

		<strong class="wu-text-gray-800 wu-text-2xl md:wu-text-xl">
			<?php echo wu_format_currency($gross_revenue); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('Gross Revenue', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

		<div>

		<strong class="wu-text-gray-800 wu-text-2xl md:wu-text-xl">
			<?php echo wu_format_currency($refunds); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('Refunded', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	</ul>

	<div class="wu--mx-3 wu--mb-3 wu-mt-2">

	<table class="wp-list-table widefat fixed striped wu-border-t-1 wu-border-l-0 wu-border-r-0">

		<thead>
			<tr>
			<th><?php esc_html_e('Product', 'multisite-ultimate'); ?></th>
			<th class="wu-text-right"><?php esc_html_e('Revenue', 'multisite-ultimate'); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php if (wu_get_products()) : ?>

				<?php foreach ($product_stats as $stats) : ?>

				<tr>
				<td>
					<?php echo esc_html($stats['label']); ?>
				</td>
				<td class="wu-text-right">
					<?php echo wu_format_currency($stats['revenue']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
				</tr>

			<?php endforeach; ?>

			<?php else : ?>

			<tr>
				<td colspan="2">
				<?php esc_html_e('No Products found.', 'multisite-ultimate'); ?>
				</td>
			</tr>

			<?php endif; ?>

		</tbody>

	</table>

	</div>

</div>
