<?php
/**
 * Summary view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling">

	<ul class="md:wu-flex wu-m-0">

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

		<div>

		<strong class="wu-text-gray-800 wu-text-base">
			<?php echo esc_html($signups); ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('Signups today', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative" <?php echo wu_tooltip_text(__('MRR stands for Monthly Recurring Revenue', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<div>

		<strong class="wu-text-gray-800 wu-text-base">
			<?php echo esc_html(wu_format_currency($mrr)); ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('MRR', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	<li class="wu-p-2 wu-w-full md:wu-w-4/12 wu-relative">

		<div>

		<strong class="wu-text-gray-800 wu-text-base">
			<?php echo esc_html(wu_format_currency($gross_revenue)); ?>
		</strong>

		</div>

		<div class="wu-text-md wu-text-gray-600">
		<span class="wu-block"><?php esc_html_e('Today\'s gross revenue', 'multisite-ultimate'); ?></span>
		</div>

	</li>

	</ul>

</div>
