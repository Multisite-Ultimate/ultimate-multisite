<?php
/**
 * Limits and quotas view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<!-- Title Element -->
	<div class="wu-p-4 wu-flex wu-items-center <?php echo esc_attr(wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-b wu-border-gray-200')); ?>">

		<?php if ($title) : ?>

		<h3 class="wu-m-0 <?php echo esc_attr(wu_env_picker('', 'wu-widget-title')); ?>">

			<?php echo esc_html($title); ?>

		</h3>

		<?php endif; ?>

	</div>
	<!-- Title Element - End -->

	<ul class="wu-list-none wu-m-0 wu-p-4 wu-grid wu-gap-2 wu-row-gap-0 lg:wu-grid-cols-<?php echo esc_attr((int) $columns); ?> <?php echo esc_attr(wu_env_picker('', 'wu-p-4')); ?>">

		<?php if ($post_type_limits->is_enabled()) : ?>

			<?php switch_to_blog($site->get_id()); ?>

			<?php $index = 0; foreach ($post_types as $post_type_slug => $post_type) : ?>

				<?php

				if (is_array($items_to_display) && ! in_array($post_type_slug, $items_to_display, true)) {
					continue;
				}

				if ($post_type_limits->{$post_type_slug}->enabled) :
					$post_count = $post_type_limits->get_post_count($post_type_slug);

					// Calculate width
					if (empty($post_type_limits->{$post_type_slug}->number)) { // unlimited posts.

						$width = 5;
					} else {
						$width = ($post_count / $post_type_limits->{$post_type_slug}->number * 100);
					}

					if ($width > 100) {
						$width = 100;
					}

					?>

				<li class="wu-py-2 wu-m-0">

				<span class="">

					<?php echo esc_html($post_type->label); ?>

				</span>

				<span class="wu-w-full wu-bg-gray-200 wu-rounded-full wu-h-1 wu-block wu-my-2">

					<span class="<?php echo esc_attr(wu_get_random_color($index)); ?> wu-rounded-full wu-h-1 wu-block wu-my-1" style="width: <?php echo esc_attr($width); ?>%;"></span>

				</span>

				<div class="wu-text-xs wu-text-gray-600 wu-align-middle">

					<?php echo esc_html($post_count); ?>
					/
					<?php echo empty($post_type_limits->{$post_type_slug}->number) ? esc_html__('Unlimited', 'multisite-ultimate') : esc_html($post_type_limits->{$post_type_slug}->number); ?>

				</div>

				</li>

	<?php endif; ?>

				<?php
				++$index;
endforeach;
			?>

			<?php restore_current_blog(); ?>

		<?php endif; ?>

		<?php if ($site->get_limitations()->visits->is_enabled()) : ?>

			<?php

			$visit_limitations = $site->get_limitations()->visits;

			/*
			* Get the visits count.
			*/
			$visits_count = (int) $site->get_visits_count();

			/*
			* Calculates the width of the bar
			*/
			$visits_width = empty($visit_limitations->get_limit()) ? 1 : $visits_count / $visit_limitations->get_limit() * 100;

			?>

		<li class="quota wu-py-2 wu-m-0">

			<div class="">

			<?php esc_html_e('Unique Visits', 'multisite-ultimate'); ?>
			<?php // translators: %s is the next reset date ?>
			<?php echo wu_tooltip(sprintf(esc_html__('Next Reset: %s', 'multisite-ultimate'), date_i18n(get_option('date_format', 'd/m/Y'), strtotime('last day of this month')))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			</div>

			<span class="wu-w-full wu-bg-gray-200 wu-rounded-full wu-h-1 wu-block wu-my-3">

			<span class="wu-bg-orange-500 wu-rounded-full wu-h-1 wu-block wu-my-1" style="width: <?php echo esc_attr($visits_width); ?>%;"></span>

			</span>

			<div class="wu-text-xs wu-text-gray-600 wu-align-middle">

			<?php echo number_format($visits_count); ?>
			/
			<?php echo $visit_limitations->get_limit() == 0 ? esc_html__('Unlimited', 'multisite-ultimate') : esc_html(number_format((int) $visit_limitations->get_limit())); ?>

			</div>

		</li>

		<?php endif; ?>

	</ul>

	</div>

</div>
