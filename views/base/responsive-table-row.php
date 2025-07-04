<?php
/**
 * Empty List Table View
 *
 * @since 2.0.0
 */
?>
<div class="wu-block">

	<div class="wu-p-2 wu-flex">

	<?php if ($args['image']) : ?>

		<div class="wu-flex-shrink wu-mr-4 wu-items-center wu-justify-between wu-flex">

		<?php echo wp_kses_post($args['image']); ?>

		</div>

	<?php endif; ?>

	<div class="wu-flex-grow">

		<div class="wu-flex wu-items-center wu-justify-between">

		<span class="wu-font-semibold wu-truncate wu-text-gray-700">

			<?php echo wp_kses_post($args['title']); ?>

			<?php if ($args['id']) : ?>

			<span class="wu-font-normal wu-text-xs">(#<?php echo esc_html($args['id']); ?>)</span>

			<?php endif; ?>

		</span>

		<div class="wu-ml-2 wu-flex-shrink-0 wu-flex">

			<?php echo wp_kses_post($args['status']); ?>

		</div>

		</div>

		<div class="sm:wu-flex sm:wu-justify-between wu-mt-1">

		<div class="sm:wu-flex">

			<?php
			$first = true;
			foreach ($first_row as $slug => $item) :
				$w_classes = wu_get_isset($item, 'wrapper_classes', '');
				?>

							<?php if (wu_get_isset($item, 'url')) : ?>

				<a title="<?php echo esc_attr(wu_get_isset($item, 'value', '')); ?>" href="<?php echo esc_attr($item['url']); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight <?php echo ! $first ? 'sm:wu-mt-0 sm:wu-ml-6' : ''; ?> <?php echo esc_attr($w_classes); ?>" <?php echo wu_tooltip_text($item['label']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

				<span class="<?php echo esc_attr($item['icon']); ?>"></span>

								<?php echo esc_html($item['value']); ?>

				</a>

			<?php else : ?>

				<span class="wu-flex wu-items-center wu-text-xs wu-text-gray-600 <?php echo ! $first ? 'sm:wu-mt-0 sm:wu-ml-6' : ''; ?> <?php echo esc_attr($w_classes); ?>" <?php echo wu_get_isset($item, 'label') ? wu_tooltip_text($item['label']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

				<span class="<?php echo esc_attr($item['icon']); ?>"></span>

				<?php echo esc_html($item['value']); ?>

				</span>

			<?php endif; ?>

						<?php
						$first = false;
endforeach;
			?>

		</div>

		<div class="sm:wu-flex wu-items-center wu-text-xs wu-text-gray-600 sm:wu-mt-0">

			<?php
			$first = true;
			foreach ($second_row as $slug => $item) :
				$w_classes = wu_get_isset($item, 'wrapper_classes', '');
				?>

							<?php if (wu_get_isset($item, 'url')) : ?>

				<a title="<?php echo esc_attr(wu_get_isset($item, 'value', '')); ?>" href="<?php echo esc_attr($item['url']); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight <?php echo ! $first ? 'sm:wu-mt-0 sm:wu-ml-6' : ''; ?> <?php echo esc_attr($w_classes); ?>" <?php echo wu_tooltip_text($item['label']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

				<span class="<?php echo esc_attr($item['icon']); ?>"></span>

								<?php echo esc_html($item['value']); ?>

				</a>

			<?php else : ?>

				<span class="wu-flex wu-items-center wu-text-xs wu-text-gray-600 <?php echo ! $first ? 'sm:wu-mt-0 sm:wu-ml-6' : ''; ?> <?php echo esc_attr($w_classes); ?> " <?php echo wu_get_isset($item, 'label') ? wu_tooltip_text($item['label']) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

				<span class="<?php echo esc_attr($item['icon']); ?>"></span>

				<?php echo esc_html($item['value']); ?>

				</span>

			<?php endif; ?>

						<?php
						$first = false;
endforeach;
			?>

		</div>

		</div>

	</div>

	<?php if ($args['url']) : ?>

		<div class="wu-flex wu-ml-5 wu-flex-shrink-0 wu-items-center wu-justify-between">

		<a href="<?php echo esc_attr($args['url']); ?>" title="<?php esc_attr_e('View', 'multisite-ultimate'); ?>">
			<svg class="wu-h-5 wu-w-5 wu-text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
			<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
			</svg>
		</a>

		</div>

	<?php endif; ?>

	</div>    

</div>
