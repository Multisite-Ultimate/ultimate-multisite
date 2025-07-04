<?php
/**
 * Grid item view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-border-transparent wu-flex wu-flex-col wu-justify-end" tabindex="0">

	<div class="wu-border wu-border-solid wu-border-gray-300 wu-pb-8 wu-bg-white wu-flex wu-flex-col wu-h-full" >

	<div class="wu-relative wu-flex-grow">

			<?php
			$featured_image = $item->get_featured_image('wu-thumb-medium');

			if ($featured_image) {
				?>
				<img
					style="opacity: 0.6; height: 16rem;"
					class="wu-w-full"
					src="<?php echo esc_url($featured_image); ?>"
				/>

				<div class="wu-my-4 wu-mx-3 wu-inline-block wu-absolute wu-bottom-0 wu-right-0 wu-rounded wu-px-2 wu-py-1 wu-uppercase wu-text-xs wu-font-bold <?php echo esc_attr($item->get_type_class()); ?>">
					<?php echo esc_html($item->get_type_label()); ?>
				</div>
				<?php
			} else {
				?>
				<div class="wu-w-full wu-bg-gray-200 wu-rounded wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-mr-3" style="height: 16rem;">
					<span class="dashicons-wu-image wu-text-6xl"></span>
				</div>

				<div class="wu-my-4 wu-mx-3 wu-inline-block wu-absolute wu-bottom-0 wu-right-0 wu-rounded wu-px-2 wu-py-1 wu-uppercase wu-text-xs wu-font-bold <?php echo esc_attr($item->get_type_class()); ?>">
					<?php echo esc_html($item->get_type_label()); ?>
				</div>
				<?php
			}
			?>


	</div>

	<div class="wu-text-base wu-mt-1 wu-px-3 wu-mt-3">

		<div>
		<span class="wu-font-semibold"><?php echo esc_html($item->get_name()); ?></span>
		<!-- <small><?php echo esc_html($item->get_price_description()); ?></small> -->
		</div>

		<div class="wu-text-xs wu-my-1">
		<?php echo esc_html($item->get_price_description()); ?>
		</div>

	</div>

	<div class="site-secondary-info wu-mt-3"></div>

	<div class="wu-flex wu-justify-between wu-items-center wu--mb-8 wu-p-4 wu-bg-gray-100 wu-border wu-border-solid wu-border-gray-300 wu-border-l-0 wu-border-r-0 wu-border-b-0">

		<!-- <label>
			<input class="wu-rounded-none" type="checkbox" name="bulk-delete[]" value="<?php echo esc_attr($item->get_id()); ?>" />
			<?php esc_html_e('Select Site', 'multisite-ultimate'); ?>
		</label> -->

		<a href="<?php echo esc_attr(wu_network_admin_url('wp-ultimo-edit-product', ['id' => $item->get_id()])); ?>" class="button button-primary">
			<?php esc_html_e('Read More', 'multisite-ultimate'); ?>
		</a>

	</div>
	</div>
</div>
