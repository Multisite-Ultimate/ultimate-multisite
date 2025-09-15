<?php
/**
 * Hidden field view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

/** @var \WP_Ultimo\UI\Field $field */
?>
<li class="wu-hidden wu-m-0">

	<input class="<?php echo esc_attr(trim($field->classes)); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php $field->print_html_attributes(); ?>>

</li>
