<?php
/**
 * Site Actions
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

	<div class="<?php echo esc_attr(wu_env_picker('', 'wu-widget-inset')); ?>">

	<!-- Title Element -->
	<div class="wu-p-4 wu-flex wu-items-center <?php echo esc_attr(wu_env_picker('', 'wu-bg-gray-100')); ?>">

		<?php if (true) : ?>

		<h3 class="wu-m-0 <?php echo esc_attr(wu_env_picker('', 'wu-widget-title')); ?>">

			<?php esc_html_e('Actions', 'multisite-ultimate'); ?>

		</h3>

		<?php endif; ?>

	</div>
	<!-- Title Element - End -->

	<ul class="wu-list-none wu-m-0 wu-p-0">

		<?php foreach ($actions as $action) : ?>

		<li class="wu-border-0 wu-border-solid wu-border-t wu-border-gray-200 wu-m-0">

			<a
			title="<?php echo esc_attr($action['label']); ?>"
			href="<?php echo esc_attr($action['href']); ?>"
			class="
			<?php
			if (isset($action['classes']) && $action['classes']) {
				echo esc_attr($action['classes']); }
			?>
			wu-px-4 wu-py-3 wu-inline-block wu-no-underline"
			>

			<?php echo esc_html($action['label']); ?>

			</a>

		</li>

		<?php endforeach; ?>

	</ul>

	<?php if ( ! empty($danger_zone_actions)) : ?>

		<!-- Title Element -->
		<div class="wu-p-4 wu-flex wu-items-center <?php echo esc_attr(wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-t wu-border-gray-200')); ?>">

		<?php if (true) : ?>

			<h3 class="wu-m-0 <?php echo esc_attr(wu_env_picker('', 'wu-widget-title')); ?>">

			<?php esc_html_e('Danger Zone', 'multisite-ultimate'); ?>

			</h3>

		<?php endif; ?>

		</div>
		<!-- Title Element - End -->

		<ul class="wu-list-none wu-m-0 wu-p-0">

		<?php foreach ($danger_zone_actions as $action) : ?>

			<li class="wu-border-0 wu-border-solid wu-border-t wu-border-gray-200 wu-m-0">

			<a
				title="<?php echo esc_attr($action['label']); ?>"
				href="<?php echo esc_attr($action['href']); ?>"
				class="<?php echo esc_attr($action['classes']); ?> wu-px-4 wu-py-3 wu-inline-block wu-no-underline"
			>

				<?php echo esc_html($action['label']); ?>

			</a>

			</li>

		<?php endforeach; ?>

		</ul>

	<?php endif; ?>

	</div>

</div>
