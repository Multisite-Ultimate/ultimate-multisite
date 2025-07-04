<?php
/**
 * Graph countries view.
 *
 * @since 2.0.0
 */
?>

<div class="wu-styling">

<div class="wu-widget-inset">

<?php

$data    = [];
$slug    = 'most_visited_sites';
$headers = [
	__('Site', 'multisite-ultimate'),
	__('Visits', 'multisite-ultimate'),
];

foreach ($sites as $site_visits) {
	$site_line = $site_visits->site->get_title() . ' ' . get_admin_url($site_visits->site->get_id());

	$line = [
		$site_line,
		$site_visits->count,
	];

	$data[] = $line;
}

$page->render_csv_button(
	[
		'headers' => $headers,
		'data'    => $data,
		'slug'    => $slug,
	]
);

?>

</div>

</div>

<?php if ( ! empty($sites)) : ?>

	<div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu-mt-3">

	<table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

		<thead>
		<tr>
			<th class="wu-w-8/12"><?php esc_html_e('Site', 'multisite-ultimate'); ?></th>
			<th class="wu-text-right"><?php esc_html_e('Visits', 'multisite-ultimate'); ?></th>
		</tr>
		</thead>

		<tbody>

			<?php foreach ($sites as $site_visits) : ?>

			<tr>
			<td class="wu-align-middle">
				<span class="wu-uppercase wu-text-xs wu-text-gray-700 wu-font-bold">
					<?php echo esc_html($site_visits->site->get_title()); ?>
				</span>

				<div class="sm:wu-flex">          

				<a title="<?php esc_html_e('Homepage', 'multisite-ultimate'); ?>" href="<?php echo esc_attr(get_home_url($site_visits->site->get_id())); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight">

					<span class="dashicons-wu-link1 wu-align-middle wu-mr-1"></span>
					<?php esc_html_e('Homepage', 'multisite-ultimate'); ?>

				</a>

				<a title="<?php esc_html_e('Dashboard', 'multisite-ultimate'); ?>" href="<?php echo esc_attr(get_admin_url($site_visits->site->get_id())); ?>" class="wu-no-underline wu-flex wu-items-center wu-text-xs wp-ui-text-highlight sm:wu-mt-0 sm:wu-ml-6">

					<span class="dashicons-wu-browser wu-align-middle wu-mr-1"></span>
					<?php esc_html_e('Dashboard', 'multisite-ultimate'); ?>

				</a>

				</div>
			</td>
			<td class="wu-align-middle wu-text-right">
				<?php // translators: %s number of visitors. ?>
				<?php printf(esc_html(_n('%d visit', '%d visits', $site_visits->count, 'multisite-ultimate')), esc_html($site_visits->count)); ?>
			</td>
			</tr>

		<?php endforeach; ?>

		</tbody>

	</table>

	</div>

<?php else : ?>

	<div class="wu-bg-gray-100 wu-p-4 wu-rounded wu-mt-6">

	<?php esc_html_e('No visits registered in this period.', 'multisite-ultimate'); ?>

	</div>

<?php endif; ?>
