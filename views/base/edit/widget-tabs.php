<?php
/**
 * Tabs widget view.
 *
 * @since 2.0.0
 */
?>
<div
	class="wu-m-0"
	data-wu-app="<?php echo esc_attr($html_attr['data-wu-app']); ?>"
	data-state="<?php echo esc_attr(wp_json_encode(($html_attr['data-state']))); ?>"
	<?php echo wu_get_isset($html_attr, 'data-on-load') ? 'data-on-load="' . esc_attr($html_attr['data-on-load']) . '"' : ''; ?>
>

	<div
		class="wu-widget-inside md:wu-flex wu-flex-none md:wu--mx-3 md:wu--mb-3 wu--m-2"
		v-bind:class="display_all ? 'wu-display-all' : ''"
	>

		<div
			class="wu-block md:wu-px-3 wu-w-full md:wu-w-1/4 wu-bg-gray-100 md:wu-border-solid wu-border-gray-400 wu-border-t-0 wu-border-l-0 wu-border-b-0 wu-border-r"
		>

			<ul class="wu-text-sm">

				<ul>

					<!-- Menu Item -->
					<li v-show="display_all" v-cloak>

						<!-- Menu Link -->
						<a class="wu-cursor-pointer wu-block wu-py-2 wu-px-4 wu-no-underline wu-rounded wu-bg-gray-300 wu-text-gray-800">

							<span class="wu-text-base wu-w-4 wu-h-4 wu-pt-2px wu-mr-1 dashicons dashicons-wu-chevron-with-circle-down">&nbsp;</span>

							<?php esc_html_e('All Options', 'multisite-ultimate'); ?>

						</a>
						<!-- End Menu Link -->

					</li>
					<!-- End Menu Item -->

					<?php foreach ($sections as $section_id => $section) : ?>

						<!-- Menu Item -->
						<li v-show="!display_all && <?php echo esc_attr($section['v-show']); ?>">

							<!-- Menu Link -->
							<a
								class="wu-cursor-pointer wu-block md:wu-py-2 md:wu-px-4 wu-p-4 wu-no-underline wu-rounded wu-text-gray-600"
								v-bind:class="section == '<?php echo esc_attr($section_id); ?>' ? 'wu-bg-gray-300 wu-text-gray-800' : ''"
								v-on:click.prevent="section = '<?php echo esc_attr($section_id); ?>'"
							>

						<?php if ($section['icon']) : ?>

									<span class="wu-text-base wu-w-4 wu-h-4 wu-pt-2px wu-mr-1 dashicons <?php echo esc_attr($section['icon']); ?>">&nbsp;</span>

								<?php else : ?>

									<span class="wu-text-base wu-w-4 wu-h-4 wu-pt-2px wu-mr-1 dashicons dashicons-wu-sound-mix">&nbsp;</span>

								<?php endif; ?>

						<?php echo esc_html($section['title']); ?>

							</a>
							<!-- End Menu Link -->

						</li>
						<!-- End Menu Item -->

					<?php endforeach; ?>

				</ul>

				<a v-on:click="display_all = !display_all;" class="wu-cursor-pointer wu-block wu-py-2 wu-px-4 wu-pt-10 wu-no-underline wu-text-xs wu-rounded">

					<span v-show="!display_all">

						<?php esc_html_e('Display all fields', 'multisite-ultimate'); ?>

					</span>

					<span v-cloak v-show="display_all">

						<?php esc_html_e('Hide other fields', 'multisite-ultimate'); ?>

					</span>

				</a>

			</ul>

		</div>

		<div class="md:wu-w-3/4 wu-w-full">

			<div v-show="false" class="wu-text-center wu-rounded wu-flex wu-items-center wu-justify-center wu-uppercase wu-font-semibold wu-text-xs wu-h-full wu-text-gray-700">

				<span class="wu-blinking-animation">

					<?php esc_html_e('Loading...', 'multisite-ultimate'); ?>

				</span>

			</div>

			<?php foreach ($sections as $section_id => $section) : ?>

				<div
					class="wu-tab-content"
					v-cloak
					id="<?php echo esc_attr("wu_tab_$section_id"); ?>"
				>

				<?php

				/**
				 * Render Form
				 */
				$section['form']->render();

				?>

				</div>

			<?php endforeach; ?>

		</div>

	</div>

	<?php echo $after; // phpcs:ignore WordPress.Security.NonceVerification ?>

</div>
