<?php
/**
 * Group field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="wu-block wu-w-full <?php echo esc_attr($field->classes); ?>">

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

	<?php

	/**
	 * Instantiate the form for the order details.
	 *
	 * @since 2.0.0
	 */
	$form = new \WP_Ultimo\UI\Form(
		$field->id,
		$field->fields,
		[
			'views'                 => 'admin-pages/fields',
			'classes'               => trim('wu-flex ' . esc_attr($field->classes)),
			'field_wrapper_classes' => 'wu-bg-transparent',
		]
	);

	$form->render();

	?>

	<?php if ($field->desc) : ?>

		<div class="wu-mt-2 wu-block wu-bg-gray-100 wu-rounded wu-border-solid wu-border-gray-400 wu-border-t wu-border-l wu-border-b wu-border-r wu-text-2xs wu-py-2 wu-p-2">

		<?php echo $field->desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		</div>

		<?php endif; ?>

	</div>

</li>
