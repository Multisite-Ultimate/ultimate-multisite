<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

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

	<div class="<?php echo esc_attr('wu-my-0 ' . $field->classes); ?>">

	<?php echo $field->desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</div>

</li>
