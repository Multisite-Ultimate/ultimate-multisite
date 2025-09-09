<?php
/**
 * Select field view.
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

	<select
	class="form-control wu-w-full wu-my-1 <?php echo esc_attr(trim($field->classes)); ?>"
	id="field-<?php echo esc_attr($field->id); ?>"
	name="<?php echo esc_attr($field->id); ?>"
	value="<?php echo esc_attr($field->value); ?>"
	<?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	>

	<?php if ($field->placeholder) : ?>

	<option <?php checked(! $field->value); ?> class="wu-opacity-75"><?php echo esc_html($field->placeholder); ?></option>

	<?php endif; ?>

	<?php foreach ($field->options as $key => $label) : ?>

		<option
		value="<?php echo esc_attr($key); ?>"
		<?php checked($key, $field->value); ?>
		>
		<?php echo esc_html($label); ?>
	</option>

	<?php endforeach; ?>

	<?php if ($field->options_template) : ?>

		<?php echo $field->options_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php endif; ?>

	</select>

	<?php

	/**
	 * Adds the partial title template.
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
