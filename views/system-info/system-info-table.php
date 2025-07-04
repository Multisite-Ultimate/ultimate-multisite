<?php
/**
 * System info table view.
 *
 * @since 2.0.0
 */

?>
<table class='wu-table-auto striped wu-w-full'>

	<?php if (empty($data)) : ?>

		<tr>
			<td colspan="2" class="wu-px-4 wu-py-2">
				<?php esc_html_e('No items found.', 'multisite-ultimate'); ?>
			</td>
		</tr>

	<?php endif; ?>

	<?php foreach ($data as $key => $value) : ?>

		<tr>

				<td class='wu-px-4 wu-py-2 wu-w-4/12'> <?php echo esc_html($value['title']); ?> </td>

				<td class='wu-px-4 wu-py-2 wu-text-center wu-w-5'>

					<?php echo wu_tooltip($value['tooltip']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				</td>

				<?php if ('Yes' === $value['value'] || 'Enabled' === $value['value']) : ?>

					<td class='wu-px-4 wu-py-2'>
						<span class="dashicons dashicons-yes wu-text-green-400"></span>
					</td>

				<?php elseif ('No' === $value['value'] || 'Disabled' === $value['value']) : ?>

					<td class='wu-px-4 wu-py-2'>
						<span class="dashicons dashicons-no-alt wu-text-red-600"></span>
					</td>

				<?php else : ?>

					<td class='wu-px-4 wu-py-2'> <?php echo esc_html($value['value']); ?> </td>

				<?php endif; ?>

		</tr>

	<?php endforeach; ?>

</table>
