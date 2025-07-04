<?php
/**
 * Graph base view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-mt-6 wu-mb-0">

	<div v-show="false" class="wu-text-center wu-rounded wu-flex wu-items-center wu-justify-center wu-uppercase wu-font-semibold wu-text-xs wu-h-full wu-text-gray-700" style="height: 300px;">

	<span class="wu-blinking-animation">

		<?php esc_html_e('Loading...', 'multisite-ultimate'); ?>

	</span>

	</div>

	<div id="chart_mrr_growth">
	<apexchart
		v-cloak
		height="300"
		:type="chart_options.mrr_growth.chartOptions.chart.type"
		:options="chart_options.mrr_growth.chartOptions"
		:series="chart_options.mrr_growth.series"
	>
	</apexchart>
	</div>

</div>

