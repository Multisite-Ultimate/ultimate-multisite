<?php
/**
 * Code editor field view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<div class="wu-block wu-w-full">

	<?php

	/**
	 * Adds the partial title template.
	 *
	 * @since 2.0.0
	 */
	wu_get_template(
		'admin-pages/fields/partials/field-title',
		[
			'field' => $field,
		]
	);

	?>

	<textarea id="field-<?php echo esc_attr($field->id); ?>" data-init="0"  data-code-editor="<?php echo esc_attr($field->lang); ?>" class="form-control wu-w-full wu-my-1 <?php echo esc_attr($field->classes); ?>" name="<?php echo esc_attr($field->id); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" <?php $field->print_html_attributes(); ?>><?php echo esc_attr($field->value); ?></textarea>

	<?php

	/**
	 * Adds the partial title template.
	 *
	 * @since 2.0.0
	 */
	wu_get_template(
		'admin-pages/fields/partials/field-description',
		[
			'field' => $field,
		]
	);

	?>

	</div>

</li>
