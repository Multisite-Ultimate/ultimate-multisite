<?php
/**
 * Placeholders
 *
 * @since 2.0.0
 */
?>
<div id="wu_event_payload_placeholders" class="wu-styling">

	<div class="wu-widget-inside md:wu-flex wu-flex-none md:wu--mx-3 md:wu--mb-3 wu--m-2">

		<div v-show="!loading" v-cloak>

			<div class="wu-p-2 wu-border wu-border-solid wu-border-gray-400 wu-border-t-0 wu-border-l-0 wu-border-r-0 wu-bg-gray-100">

				<input class="wu-w-full wu-border-gray-400" type="text" placeholder="<?php esc_attr_e('Search Placeholders', 'multisite-ultimate'); ?>" v-model="search" />

			</div>

			<div style="max-height: 300px;" class="wu-overflow-auto">

				<table class="wp-list-table widefat fixed striped wu-border-t-0 wu-border-l-0 wu-border-r-0">

					<thead>

					<tr>

						<th style="width: 30%;"><?php echo esc_html__('Name', 'multisite-ultimate'); ?></th>

						<th style="width: 30%;"><?php echo esc_html__('Placeholder', 'multisite-ultimate'); ?></th>

					</tr>

					</thead>

					<tbody id="placeholders_table" class="wu-align-baseline">

					<tr v-for="placeholder in filtered_placeholders">

						<td class="wu-align-left wu-text-xs">

							<span :id="'payload_event_name_' + placeholder.placeholder" class="wu-rounded-sm wu-text-xs"><?php echo esc_html(str_replace(['Id', 'Url'], ['ID', 'URL'], $placeholder['name'])); ?></span>

						</td>

						<td class="wu-align-middle wu-text-xs">

									<a @click.prevent="" <?php echo wu_tooltip_text(__('Copy', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="wu-no-underline wp-ui-text-highlight wu-copy" href="#" data-clipboard-action="copy" :data-clipboard-target="'#payload_event_placeholder_' + placeholder.placeholder">

										<span class="dashicons-wu-copy wu-align-middle"></span>

							</a>

									<span v-html="'{{' + placeholder.placeholder + '}}'" :id="'payload_event_placeholder_' + placeholder.placeholder" class="wu-rounded-sm wu-text-xs wu-font-mono">

										&nbsp;

									</span>

						</td>

					</tr>

					</tbody>

				</table>

			</div>

		</div>

	</div>

	<div v-show="loading" class="wu-block wu-p-4 wu-py-8 wu-mb-0 wu-bg-white wu-text-center wu-my-4 wu-rounded">

		<span class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-mb-0 wu-text-2xs wu-uppercase wu-font-semibold" >

				<?php echo esc_html($loading_text); ?>

		</span>

	</div>

</div>
