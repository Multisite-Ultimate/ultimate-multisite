<?php
/**
 * Customizer editor view.
 *
 * @since 2.0.0
 */
?>
<div id="preview-stage">

	<div v-show="preview">

	<div class="wu-block wu-flex wu-justify-center wu-p-4 wu-py-8 wu-bg-white wu-text-center wu-border wu-border-solid wu-rounded wu-border-gray-400 wu-h-screen">

		<span class="wu-self-center wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">

		<?php esc_html_e('Loading Preview...', 'multisite-ultimate'); ?>

		</span>

	</div>

	</div>

	<div v-show="!preview" v-cloak>

	<div id="wp-ultimo-list-table-add-new-1" class="postbox wu-mb-0">

		<div class="wu-bg-white wu-px-4 wu-py-3 wu-flex wu-items-center">

		<div class="wu-w-1/2">

			<span class="wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">

			<?php echo esc_html__('Template Preview', 'multisite-ultimate'); ?>

			</span>

		</div>

		</div>

	</div>

	<div id="preview_content" class="wu-block wu-bg-gray wu-text-center wu-mb-5 wu-border wu-border-t-0 wu-border-solid wu-rounded wu-border-gray-400">

		<iframe id="preview-stage-iframe" class="preview-stage-iframe" width="100%" style="height: <?php echo esc_attr($preview_height); ?>;" frameborder="0" data-src="<?php echo esc_url($preview_iframe_url); ?>" src="<?php echo esc_url($preview_iframe_url); ?>"></iframe>

	</div>

	</div>

</div>
