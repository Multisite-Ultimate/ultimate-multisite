<?php
/**
 * Requirements table view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-block">

	<div class="wu-block wu-text-gray-700 wu-font-bold wu-uppercase wu-text-xs wu-py-2">
	<?php esc_html_e('Multisite Ultimate Requires:', 'multisite-ultimate'); ?>
	</div>

	<div class="wu-advanced-filters">
	<table class="widefat fixed striped wu-border-b">
		<thead>
		<tr>
			<th><?php esc_html_e('Item', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Minimum Version', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Recommended', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Installed', 'multisite-ultimate'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($requirements as $req) : ?>
		<tr class="">
			<td><?php echo esc_html($req['name']); ?></td>
			<td><?php echo esc_html($req['required_version']); ?></td>
			<?php // translators: %s is the requirement version ?>
			<td><?php printf(esc_html__('%s or later', 'multisite-ultimate'), esc_html($req['recommended_version'])); ?></td>
			<td class="<?php echo $req['pass_requirements'] ? 'wu-text-green-600' : 'wu-text-red-600'; ?>">
				<?php echo esc_html($req['installed_version']); ?>
				<?php echo $req['pass_requirements'] ? '<span class="dashicons-wu-check"></span>' : '<span class="dashicons-wu-cross"></span>'; ?>

				<?php if ( ! $req['pass_requirements']) : ?>

					<a class="wu-no-underline wu-block" href="<?php echo esc_url($req['help']); ?>" title="<?php esc_attr_e('Help', 'multisite-ultimate'); ?>">
						<?php esc_html_e('Read More', 'multisite-ultimate'); ?>
						<span class="dashicons-wu-help-with-circle"></span>
					</a>

				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<br>
	</div>

	<div class="wu-block wu-text-gray-700 wu-font-bold wu-uppercase wu-text-xs wu-py-2">
		<?php echo esc_html__('And', 'multisite-ultimate'); ?>
	</div>

	<div class="wu-advanced-filters">
	<table class="widefat fixed striped wu-border-b">
		<thead>
		<tr>
			<th><?php esc_html_e('Item', 'multisite-ultimate'); ?></th>
			<th><?php esc_html_e('Condition', 'multisite-ultimate'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($plugin_requirements as $req) : ?>
		<tr class="">
			<td><?php echo esc_html($req['name']); ?></td>
			<td class="<?php echo $req['pass_requirements'] ? 'wu-text-green-600' : 'wu-text-red-600'; ?>">
				<?php echo esc_html($req['condition']); ?>
			<?php echo $req['pass_requirements'] ? '<span class="dashicons-wu-check"></span>' : '<span class="dashicons-wu-cross wu-align-middle"></span>'; ?>

			<?php if ( ! $req['pass_requirements']) : ?>

				<a target="_blank" class="wu-no-underline wu-ml-2" href="<?php echo esc_url($req['help']); ?>" title="<?php esc_attr_e('Help', 'multisite-ultimate'); ?>">
				<span class="dashicons-wu-help-with-circle wu-align-baseline"></span>
				<?php esc_html_e('Read More', 'multisite-ultimate'); ?>
				</a>

			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<br>
	</div>

	<?php if (\WP_Ultimo\Requirements::met() === false) : ?>

	<div class="wu-mt-4 wu-p-4 wu-bg-red-100 wu-border wu-border-solid wu-border-red-200 wu-rounded-sm wu-text-red-500">
		<?php esc_html_e('It looks like your hosting environment does not support the current version of Multisite Ultimate. Visit the <strong>Read More</strong> links on each item to see what steps you need to take to bring your environment up to the Multisite Ultimate current requirements.', 'multisite-ultimate'); ?>
	</div>

	<?php endif; ?>

</div>
