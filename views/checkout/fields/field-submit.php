<?php
/**
 * Submit field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>
	<button id="<?php echo esc_attr($field->id); ?>-btn" type="submit" name="<?php echo esc_attr($field->id); ?>-btn" <?php $field->print_html_attributes(); ?> class="button <?php echo esc_attr(trim($field->classes)); ?>">
		<?php echo wp_kses($field->title, wu_kses_allowed_html()); ?>
	</button>
</div>
