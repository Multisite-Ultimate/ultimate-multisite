<?php
/**
 * Submit field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes) . (! str_contains($field->wrapper_classes, '-bg-') ? ' wu-bg-gray-200' : '')); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<button id="<?php echo esc_attr($field->id); ?>" type="submit" name="submit_button" value="<?php echo esc_attr($field->id); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="<?php echo esc_attr(trim($field->classes)); ?>">

	<?php echo esc_html($field->title); ?>

	</button>

</li>
