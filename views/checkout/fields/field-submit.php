<?php
/**
 * Submit field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<button id="<?php echo esc_attr($field->id); ?>-btn" type="submit" name="<?php echo esc_attr($field->id); ?>-btn" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="button <?php echo esc_attr(trim($field->classes)); ?>">
		<?php echo $field->title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</div>
