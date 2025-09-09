<?php
/**
 * Header field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="wu-bg-gray-100 wu-py-4 <?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="wu-block wu-w-full">

	<h3 class="wu-my-1 wu-text-base wu-text-gray-800">

		<?php echo $field->title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php if ($field->tooltip) : ?>

			<?php echo wu_tooltip($field->tooltip); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php endif; ?>

	</h3>

	<?php if ($field->desc) : ?>

	<p class="wu-mt-1 wu-mb-0 wu-text-gray-700">

		<?php echo $field->desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</p>

	<?php endif; ?>

	</div>

</li>
