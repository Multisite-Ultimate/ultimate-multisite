<?php
/** global $themes */
?>

<ul data-columns="1" class='items wu--mx-1 wu-overflow-hidden wu-multiselect-content wu-static wu-my-2'>

	<?php foreach ($templates as $site_template) : ?>

		<?php

		$key_name = 'site_' . $site_template->get_id();

		$template_settings = $product->get_limitations()->site_templates->{$key_name};

		?>

	<li class="item wu-box-border wu-m-0">

		<div class="wu-m-2 wu-bg-gray-100 wu-p-4 wu-border-gray-300 wu-border-solid wu-border wu-rounded">

		<div class="wu-flex wu-justify-between wu-items-center">

			<div class="wu-w-1/6 wu-mr-4">

			<img class="wu-rounded wu-w-full wu-image-preview" src="<?php echo esc_url($site_template->get_featured_image('thumb')); ?>" data-image="<?php echo esc_url($site_template->get_featured_image()); ?>">

			</div>

			<div class="wu-flex-1">

			<span class="wu-font-bold wu-block wu-text-xs wu-uppercase wu-text-gray-700">

				<?php echo esc_html($site_template->get_title()); ?>

			</span>

			<span class="wu-mt-2 wu-block">

				<?php echo esc_html(wp_trim_words(wp_strip_all_tags($site_template->get_description()), 40)); ?>

			</span>

			<span class="wu-mt-2 wu-block wu-text-xs">

				<?php echo ! $site_template->get_categories() ? esc_html__('No categories', 'multisite-ultimate') : esc_html(implode(', ', $site_template->get_categories())); ?>

			</span>

			</div> 

			<div class="sm:wu-ml-4 sm:wu-w-1/3 wu-mt-4 sm:wu--mt-1" v-if="site_template_selection_mode === 'choose_available_templates'">

			<h3 class="wu-my-1 wu-text-2xs wu-uppercase wu-text-gray-600">

				<?php esc_html_e('Behavior', 'multisite-ultimate'); ?>

			</h3>

			<select 
				v-on:change="pre_selected_template = ($event.target.value === 'pre_selected' ? '<?php echo esc_attr($site_template->get_id()); ?>' : '')" 
				name="modules[site_templates][limit][<?php echo esc_attr($site_template->get_id()); ?>][behavior]"
				class="wu-w-full"
			>
				<option <?php selected('available' === $template_settings->behavior); ?> value="available"><?php esc_html_e('Available', 'multisite-ultimate'); ?></option>
				<option <?php selected('not_available' === $template_settings->behavior); ?> value="not_available"><?php esc_html_e('Not Available', 'multisite-ultimate'); ?></option>
				<option :disabled="pre_selected_template !== '' && pre_selected_template !== false && pre_selected_template != '<?php echo esc_attr($site_template->get_id()); ?>'" <?php selected('pre_selected' === $template_settings->behavior); ?> value="pre_selected"><?php esc_html_e('Pre-Selected', 'multisite-ultimate'); ?></option>
			</select>

			</div>

			<div class="sm:wu-ml-4 wu-flex-shrink wu-mt-4 sm:wu-mt-0" v-if="site_template_selection_mode === 'assign_template'">

			<div class="wu-toggle wu-mt-1">

				<input 
				<?php checked((int) $site_template->get_id() === (int) $product->get_limitations()->site_templates->get_pre_selected_site_template()); ?>
				class="wu-tgl wu-tgl-ios" 
				value="pre_selected" 
				id="wu-tg-<?php echo esc_attr($site_template->get_id()); ?>" 
				type="checkbox"
				v-on:click="pre_selected_template = <?php echo esc_attr($site_template->get_id()); ?>"
				v-bind:checked="pre_selected_template == <?php echo esc_attr($site_template->get_id()); ?>"
				:name="pre_selected_template == <?php echo esc_attr($site_template->get_id()); ?> ? 'modules[site_templates][limit][<?php echo esc_attr($site_template->get_id()); ?>][behavior]' : ''" />

				<label class="wu-tgl-btn wp-ui-highlight wu-bg-blue-500" for="wu-tg-<?php echo esc_attr($site_template->get_id()); ?>"></label>

			</div>

			</div>

		</div>

		</div>

	</li>

	<?php endforeach; ?>

</ul>
