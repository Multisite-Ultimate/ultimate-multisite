<?php
/**
 * Installation steps view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-advanced-filters">
	<table class="widefat fixed striped wu-border-b" data-id="<?php echo esc_attr($page->get_current_section()); ?>">
		<thead>
		<tr>
			<?php if ($checks) : ?>
				<th class="check" style="width: 30px;"></th>
			<?php endif ?>
			<th class="item"><?php esc_html_e('Item', 'multisite-ultimate'); ?></th>
			<th class="status" style="width: 40%;"><?php esc_html_e('Status', 'multisite-ultimate'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($steps as $slug => $default) : ?>
			<tr <?php echo ! $default['done'] ? 'data-content="' . esc_attr($slug) . '"' : ''; ?> <?php echo wu_array_to_html_attrs(wu_get_isset($default, 'html_attr', [])); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php if ($checks) : ?>
					<td>
						<?php if ( ! $default['done']) : ?>
							<input type="checkbox" name="default_content[<?php echo esc_attr($slug); ?>]" id="default_content_<?php echo esc_attr($slug); ?>" value="1" <?php checked(true, isset($default['checked']) ? $default['checked'] : false); ?>>
						<?php endif ?>
					</td>
				<?php endif; ?>
				<td>
					<label class="wu-font-semibold wu-text-gray-700" for="default_content_<?php echo esc_attr($slug); ?>">
						<?php echo wp_kses($default['title'], ['code' => []]); ?>
					</label>
					<span class="wu-text-xs wu-block wu-mt-1">
							<?php echo esc_html($default['description']); ?>
						</span>
				</td>
				<?php if ($default['done']) : ?>
					<td class="status">
							<span class="wu-text-green-600">
								<?php echo esc_html($default['completed'] ?? __('Completed!', 'multisite-ultimate')); ?>
							</span>
					</td>
				<?php else : ?>
					<td class="status">
						<span><?php echo esc_html($default['pending']); ?></span>
						<div class="spinner"></div>
						<!-- Removed the help link as it was not properly escaped -->
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
