<?php
/**
 * Domain mapping view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<!-- Title Element -->
	<div class="wu-p-4 wu-flex wu-items-center <?php echo esc_attr(wu_env_picker('', 'wu-bg-gray-100')); ?>">

		<?php if ($title) : ?>

		<h3 class="wu-m-0 <?php echo esc_attr(wu_env_picker('', 'wu-widget-title')); ?>">

			<?php echo esc_html($title); ?>

		</h3>

		<?php endif; ?>

		<div class="wu-ml-auto">

		<a title="<?php esc_html_e('Add Domain', 'multisite-ultimate'); ?>" href="<?php echo esc_attr($modal['url']); ?>" class="wu-text-sm wu-no-underline wubox button">

			<?php esc_html_e('Add Domain', 'multisite-ultimate'); ?>

		</a>

		</div>

	</div>
	<!-- Title Element - End -->

	<div class="wu-border-t wu-border-solid wu-border-0 wu-border-gray-200">

		<table class="wu-m-0 wu-my-2 wu-p-0 wu-w-full">

		<tbody class="wu-align-baseline">

			<?php if ($domains) : ?>

				<?php
				foreach ($domains as $key => $domain) :
					$item = $domain['domain_object'];
					?>

					<tr>

					<td class="wu-px-1">

						<?php

						$label = $item->get_stage_label();

						if ( ! $item->is_active()) {
							$label = sprintf('%s <small>(%s)</small>', $label, __('Inactive', 'multisite-ultimate'));
						}

						$class = $item->get_stage_class();

						$status = "<span class='wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-leading-none wu-font-mono $class'>{$label}</span>";

						$second_row_actions = [];

						if ( ! $item->is_primary_domain()) {
							$second_row_actions['make_primary'] = [
								'wrapper_classes' => 'wubox',
								'icon'            => 'dashicons-wu-edit1 wu-align-middle wu-mr-1',
								'label'           => '',
								'url'             => $domain['primary_link'],
								'value'           => __('Make Primary', 'multisite-ultimate'),
							];
						}

						$second_row_actions['remove'] = [
							'wrapper_classes' => 'wu-text-red-500 wubox',
							'icon'            => 'dashicons-wu-trash-2 wu-align-middle wu-mr-1',
							'label'           => '',
							'value'           => __('Delete', 'multisite-ultimate'),
							'url'             => $domain['delete_link'],
						];

						echo wu_responsive_table_row( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							[
								'id'     => false,
								'title'  => strtolower($item->get_domain()),
								'url'    => false,
								'status' => $status,
							],
							[
								'primary' => [
									'wrapper_classes' => $item->is_primary_domain() ? 'wu-text-blue-600' : '',
									'icon'            => $item->is_primary_domain() ? 'dashicons-wu-filter_1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-plus-square wu-align-text-bottom wu-mr-1',
									'label'           => '',
									'value'           => $item->is_primary_domain() ? __('Primary', 'multisite-ultimate') . wu_tooltip(__('All other mapped domains will redirect to the primary domain.', 'multisite-ultimate'), 'dashicons-editor-help wu-align-middle wu-ml-1') : __('Alias', 'multisite-ultimate'),
								],
								'secure'  => [
									'wrapper_classes' => $item->is_secure() ? 'wu-text-green-500' : '',
									'icon'            => $item->is_secure() ? 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1',
									'label'           => '',
									'value'           => $item->is_secure() ? __('Secure (HTTPS)', 'multisite-ultimate') : __('Not Secure (HTTP)', 'multisite-ultimate'),
								],
							],
							$second_row_actions
						);

						?>

					</td>

					</tr>

				<?php endforeach; ?>

			<?php else : ?>

			<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4 wu-m-4 wu-mt-6">
				<span><?php echo esc_html__('No domains added.', 'multisite-ultimate'); ?></span>
			</div>

			<?php endif; ?>

		</tbody>

	</table>

	</div>

	</div>

</div>
