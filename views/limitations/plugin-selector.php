<?php
/** global $plugins */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

	<?php foreach ($plugins as $plugin_path => $plugin_data) : ?>

	<li class="item wu-box-border wu-m-0">

		<div class="wu-m-2 wu-bg-gray-100 wu-p-4 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

		<div class="wu-items-center wu-justify-between">

			<div class="wu-block sm:wu-flex wu-items-center">

			<div class="wu-flex-1 wu-flex wu-flex-col wu-justify-between">

				<div>

				<span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">

					<?php echo esc_html($plugin_data['Name']); ?> 

					<?php if (is_plugin_active_for_network($plugin_path)) : ?>

					<span class="wu-text-xs wu-normal-case wu-font-normal wu-ml-2 wu-text-green-600">
						<?php esc_html_e('Network Active', 'multisite-ultimate'); ?>
					</span>

					<?php endif; ?>

				</span>

				<span class="wu-my-2 wu-block">

					<?php echo wp_strip_all_tags($plugin_data['Description']); ?>

				</span>

				</div>

				<div class="wu-block wu-mt-4">

				<span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
					<?php printf(esc_html__('Version %s', 'multisite-ultimate'), esc_html($plugin_data['Version'])); ?>
				</span>

				<span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
					<?php printf(esc_html__('by %s', 'multisite-ultimate'), wp_strip_all_tags($plugin_data['Author'])); ?>
				</span>

				</div>

			</div>

			<div class="sm:wu-ml-4 sm:wu-w-1/3 wu-mt-4 sm:wu-mt-0">

				<h3 class="wu-mb-1 wu-text-2xs wu-uppercase wu-text-gray-600">

				<?php esc_html_e('Visibility', 'multisite-ultimate'); ?>

				</h3>

				<select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][visibility]" class="wu-w-full">
				<option <?php selected('visible' === $object->get_limitations()->plugins->{$plugin_path}->visibility); ?> value="visible"><?php esc_html_e('Visible', 'multisite-ultimate'); ?></option>
				<option <?php selected('hidden' === $object->get_limitations()->plugins->{$plugin_path}->visibility); ?> value="hidden"><?php esc_html_e('Hidden', 'multisite-ultimate'); ?></option>
				</select>

				<h3 class="wu-my-1 wu-mt-4 wu-text-2xs wu-uppercase wu-text-gray-600">

				<?php esc_html_e('Behavior', 'multisite-ultimate'); ?>

				</h3>

				<select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][behavior]" class="wu-w-full">
				<option <?php selected('default' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="default"><?php esc_html_e('Default', 'multisite-ultimate'); ?></option>
				<option <?php disabled(is_plugin_active_for_network($plugin_path)); ?> <?php selected('force_active' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_active"><?php esc_html_e('Force Activate', 'multisite-ultimate'); ?></option>
				<option <?php disabled(is_plugin_active_for_network($plugin_path)); ?> <?php selected('force_inactive' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_inactive"><?php esc_html_e('Force Inactivate', 'multisite-ultimate'); ?></option>
				<option <?php selected('force_active_locked' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_active_locked"><?php esc_html_e('Force Activate & Lock', 'multisite-ultimate'); ?></option>
				<option <?php selected('force_inactive_locked' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_inactive_locked"><?php esc_html_e('Force Inactivate & Lock', 'multisite-ultimate'); ?></option>
				</select>

			</div>

			</div>

		</div>

		<?php if ('product' !== $object->model && $object->get_limitations(false)->plugins->exists($plugin_path)) : ?>

			<p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
				<?php esc_html_e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'multisite-ultimate'); ?>
			</p>

		<?php endif; ?>

		</div>

	</li>

	<?php endforeach; ?>

</ul>
