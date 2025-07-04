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
		$slug    = 'signup_countries';
		$headers = [
			__('Country', 'multisite-ultimate'),
			__('Customer Count', 'multisite-ultimate'),
		];

		foreach ($countries as $country_code => $count) {
			$line = [
				wu_get_country_name($country_code),
				$count,
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

<?php if ( ! empty($countries)) : ?>

	<div class="wu-advanced-filters wu--mx-3 wu--mb-3 wu-mt-3">

		<table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

			<thead>
			<tr>
				<th><?php esc_html_e('Country', 'multisite-ultimate'); ?></th>
				<th class="wu-text-right"><?php esc_html_e('Customer Count', 'multisite-ultimate'); ?></th>
			</tr>
			</thead>

			<tbody>

			<?php foreach ($countries as $country_code => $count) : ?>

				<tr>
					<td>
						<?php

						printf(
							'<span class="wu-flag-icon wu-w-5 wu-mr-1" %s>%s</span>',
							wu_tooltip_text(esc_html(wu_get_country_name($country_code))), // phpcs:ignore WordPress.Security.EscapeOutput
							esc_html(wu_get_flag_emoji($country_code)),
						);

						?>
						<?php echo esc_html(wu_get_country_name($country_code)); ?>
					</td>
					<td class="wu-text-right"><?php echo esc_html($count); ?></td>
				</tr>

				<?php

				$state_list   = wu_get_states_of_customers($country_code);
				$_state_count = 0;

				?>

				<?php foreach ($state_list as $state => $state_count) : ?>
					<tr>
						<td class="wu-text-xs">|&longrightarrow; <?php echo esc_html($state); ?></td>
						<td class="wu-text-right"><?php echo esc_html($state_count); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if ($state_list && $count - $_state_count >= 0) : ?>
					<tr>
						<td class="wu-text-xs">|&longrightarrow; <?php esc_html_e('Other', 'multisite-ultimate'); ?></td>
						<td class="wu-text-right"><?php echo esc_html($count - $_state_count); ?></td>
					</tr>
				<?php endif; ?>

			<?php endforeach; ?>

			</tbody>

		</table>

	</div>

<?php else : ?>

	<div class="wu-bg-gray-100 wu-p-4 wu-rounded wu-mt-6">

		<?php esc_html_e('No countries registered yet.', 'multisite-ultimate'); ?>

	</div>

<?php endif; ?>
