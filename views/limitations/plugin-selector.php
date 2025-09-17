<?php
/** global $plugins */
defined( 'ABSPATH' ) || exit;
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
						<?php esc_html_e('Network Active', 'ultimate-multisite'); ?>
					</span>

					<?php endif; ?>

				</span>

				<span class="wu-my-2 wu-block">

					<?php echo esc_html(wp_strip_all_tags($plugin_data['Description'])); ?>

				</span>

				</div>

				<div class="wu-block wu-mt-4">

				<span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
					<?php // translators: %s current version. ?>
					<?php printf(esc_html__('Version %s', 'ultimate-multisite'), esc_html($plugin_data['Version'])); ?>
				</span>

				<span class="wu-text-xs wu-text-gray-700 wu-my-1 wu-mr-4 wu-block">
					<?php // translators: %s name of the author ?>
					<?php printf(esc_html__('by %s', 'ultimate-multisite'), esc_html(wp_strip_all_tags($plugin_data['Author']))); ?>
				</span>

				</div>

			</div>

			<div class="sm:wu-ml-4 sm:wu-w-1/3 wu-mt-4 sm:wu-mt-0">

				<h3 class="wu-mb-1 wu-text-2xs wu-uppercase wu-text-gray-600">

				<?php esc_html_e('Visibility', 'ultimate-multisite'); ?>

				</h3>

				<select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][visibility]" class="wu-w-full">
				<option <?php selected('visible' === $object->get_limitations()->plugins->{$plugin_path}->visibility); ?> value="visible"><?php esc_html_e('Visible', 'ultimate-multisite'); ?></option>
				<option <?php selected('hidden' === $object->get_limitations()->plugins->{$plugin_path}->visibility); ?> value="hidden"><?php esc_html_e('Hidden', 'ultimate-multisite'); ?></option>
				</select>

				<h3 class="wu-my-1 wu-mt-4 wu-text-2xs wu-uppercase wu-text-gray-600">

				<?php esc_html_e('Behavior', 'ultimate-multisite'); ?>

				</h3>

				<select name="modules[plugins][limit][<?php echo esc_attr($plugin_path); ?>][behavior]" class="wu-w-full">
				<option <?php selected('default' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="default"><?php esc_html_e('Default', 'ultimate-multisite'); ?></option>
				<option <?php disabled(is_plugin_active_for_network($plugin_path)); ?> <?php selected('force_active' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_active"><?php esc_html_e('Force Activate', 'ultimate-multisite'); ?></option>
				<option <?php disabled(is_plugin_active_for_network($plugin_path)); ?> <?php selected('force_inactive' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_inactive"><?php esc_html_e('Force Inactivate', 'ultimate-multisite'); ?></option>
				<option <?php selected('force_active_locked' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_active_locked"><?php esc_html_e('Force Activate & Lock', 'ultimate-multisite'); ?></option>
				<option <?php selected('force_inactive_locked' === $object->get_limitations()->plugins->{$plugin_path}->behavior); ?> value="force_inactive_locked"><?php esc_html_e('Force Inactivate & Lock', 'ultimate-multisite'); ?></option>
				</select>

			</div>

			</div>

		</div>

		<?php if ('product' !== $object->model && $object->get_limitations(false)->plugins->exists($plugin_path)) : ?>

			<p class="wu-m-0 wu-mt-4 wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded">
				<?php esc_html_e('This value is being applied only to this entity. Changes made to the membership or product permissions will not affect this particular value.', 'ultimate-multisite'); ?>
			</p>

		<?php endif; ?>

		</div>

	</li>

	<?php endforeach; ?>

</ul>
