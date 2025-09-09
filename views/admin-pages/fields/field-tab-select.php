<?php
/**
 * Tab select field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?> wu-bg-gray-200" style="margin-bottom: -1px;" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="wu--m-4 wu-px-1">

	<?php foreach ($field->options as $option_value => $option_label) : ?>

		<label
		class="wu-mt-1 wu-inline-block wu-uppercase wu-text-xs wu-text-gray-500 wu-px-4 wu-py-3 wu-font-bold wu-border-solid wu-border wu-border-b-0 wu-border-transparent wu-rounded-tl wu-rounded-tr "
		v-bind:class="'<?php echo esc_attr($option_value); ?>' == <?php echo esc_attr($field->id); ?> ? 'wu-bg-white wu-text-gray-600 wu-border-gray-300' : ''"
		>

		<?php echo esc_html($option_label); ?>

		<input class="wu-w-0 wu-h-0 wu-overflow-hidden wu-hidden" type="radio" name="<?php echo esc_attr($field->id); ?>" value="<?php echo esc_attr($option_value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		</label>

	<?php endforeach; ?>

	</div>

</li>
