<?php
/**
 * Text field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php

	/**
	 * Adds the partial title template.
	 *
	 * @since 2.0.0
	 */
	wu_get_template(
		'checkout/fields/partials/field-title',
		[
			'field' => $field,
		]
	);

	?>

	<?php if ($field->prefix) : ?>

	<div class="sm:wu-flex wu-items-stretch wu-content-center">

		<div <?php echo wu_array_to_html_attrs($field->prefix_html_attr ?? []); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo $field->prefix; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php endif; ?>

		<input class="form-control wu-w-full wu-my-1 <?php echo esc_attr(trim($field->classes)); ?>" id="field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<?php if ($field->suffix) : ?>

			<div <?php echo wu_array_to_html_attrs($field->suffix_html_attr ?? []); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo $field->suffix; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

		<?php endif; ?>

		<?php if ($field->prefix || $field->suffix) : ?>

	</div>

<?php endif; ?>

	<?php

	/**
	 * Adds the partial error template.
	 *
	 * @since 2.0.0
	 */
	wu_get_template(
		'checkout/fields/partials/field-errors',
		[
			'field' => $field,
		]
	);

	?>

</div>
