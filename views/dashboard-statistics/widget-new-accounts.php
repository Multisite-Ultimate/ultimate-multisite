<?php
/**
 * Total widget view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">
	<ul class="md:wu-flex wu-my-0 wu-mx-0">
		<li class="wu-p-2 wu-w-full md:wu-w-full wu-relative">
			<div>
				<strong class="wu-text-gray-800 wu-text-2xl md:wu-text-xl">
					<?php echo esc_html($new_accounts); ?>
				</strong>
			</div>
			<div class="wu-text-sm wu-text-gray-600">
				<span class="wu-block"><?php esc_html_e('New Memberships', 'multisite-ultimate'); ?></span>
			</div>
		</li>
	</ul>
	<div class="wu--mx-3 wu--mb-3 wu-mt-2">
		<table class="wp-list-table widefat fixed striped wu-border-t-1 wu-border-l-0 wu-border-r-0">
			<thead>
			<tr>
				<th><?php esc_html_e('Product Name', 'multisite-ultimate'); ?></th>
				<th class="wu-text-right"><?php esc_html_e('New Memberships', 'multisite-ultimate'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ($products) : ?>
				<?php foreach ($products as $product) : ?>
					<tr>
						<td><?php echo esc_html($product->name); ?></td>
						<td class="wu-text-right"><?php echo esc_html($product->count); ?></td>
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
