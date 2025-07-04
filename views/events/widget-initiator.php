<?php
/**
 * Widget initiator view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-bg-gray-100 wu--mt-3 wu--mb-6 wu--mx-3">

	<ul class="wu-widget-list">

	<li class="wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid">

		<h3 class="wu-mt-0 wu-mb-2 wu-text-2xs wu-uppercase"><?php esc_html_e('Initiator', 'multisite-ultimate'); ?></h3>

		<?php if ($object->get_initiator() == 'manual') : ?>

		<a href='<?php echo esc_url(wu_network_admin_url('wp-ultimo-edit-customer', ['id' => $object->get_author_id()])); ?>' class='wu-table-card wu-text-gray-700 wu-p-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-no-underline'>

			<div class="wu-flex wu-relative wu-h-7 wu-w-7 wu-rounded-full wu-ring-2 wu-ring-white wu-bg-gray-300 wu-items-center wu-justify-center wu-mr-3">

			<?php

			$avatar = get_avatar(
				$object->get_author_id(),
				32,
				'identicon',
				'',
				[
					'force_display' => true,
					'class'         => 'wu-rounded-full',
				]
			);

			echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			?>

			<span role="tooltip" aria-label="<?php echo esc_attr($object->get_initiator() . ' - ' . $object->get_severity_label()); ?>" class="wu-absolute wu-rounded-full wu--mb-2 wu--mr-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-3 wu-w-3 wu-uppercase wu-text-2xs wu-p-1 wu-border-solid wu-border-2 wu-border-white <?php echo esc_attr($object->get_severity_class()); ?>">

				<?php echo esc_html(substr($object->get_severity_label(), 0, 1)); ?>

			</span>

			</div>

			<div class='wu-pl-2'>

			<strong class='wu-block'> <?php echo esc_html($object->get_author_display_name()); ?> <small class='wu-font-normal'>(#<?php echo esc_html($object->get_author_id()); ?>)</small></strong>

			<small><?php echo esc_html($object->get_author_email_address()); ?></small>

			</div>

		</a>

		<?php else : ?>

		<div class='wu-table-card wu-text-gray-700 wu-p-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>

			<div class="wu-flex wu-relative wu-h-7 wu-w-7 wu-rounded-full wu-ring-2 wu-ring-white wu-bg-gray-300 wu-items-center wu-justify-center wu-mr-3">

				<span class="dashicons-wu-tools wu-text-gray-700 wu-text-xl"></span>

				<span role="tooltip" aria-label="<?php echo esc_attr($object->get_initiator() . ' - ' . $object->get_severity_label()); ?>" class="wu-absolute wu-rounded-full wu--mb-2 wu--mr-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-3 wu-w-3 wu-uppercase wu-text-2xs wu-p-1 wu-border-solid wu-border-2 wu-border-white <?php echo esc_attr($object->get_severity_class()); ?>">

				<?php echo esc_html(substr($object->get_severity_label(), 0, 1)); ?>

				</span>

			</div>

			<div class=''>

			<strong class='wu-block'><?php echo esc_html(ucfirst($object->get_initiator())); ?></strong>

						<small><?php esc_html_e('Automatically started', 'multisite-ultimate'); ?></small>

			</div>

		</div>

		<?php endif; ?>

	</li>

	<?php if ($object->get_object()) : ?>

		<li class="wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid">
		<?php // translators: %s is a placeholder for the object type name. ?>
		<h3 class="wu-mt-1 wu-mb-2 wu-text-2xs wu-uppercase"><?php printf(esc_html__('Target %s', 'multisite-ultimate'), esc_html(wu_slug_to_name($object->get_object_type()))); ?></h3>

		<?php

			$base_list_table = new \WP_Ultimo\List_Tables\Base_List_Table();

			$type = $object->get_object_type();

		switch ($type) {
			case 'membership':
				echo $base_list_table->column_membership($object); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;

			case 'payment':
				echo $base_list_table->column_payment($object); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				break;
		}

		?>

		</li>

	<?php endif; ?>

	</ul>

</div>
