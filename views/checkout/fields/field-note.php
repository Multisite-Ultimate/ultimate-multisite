<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>

<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<?php echo wp_kses($field->desc ?? '', wu_kses_allowed_html()); ?>

</div>
