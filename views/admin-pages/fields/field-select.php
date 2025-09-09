<?php
/**
 * Select field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

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

	<select class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?><?php echo isset($field->html_attr['multiple']) && $field->html_attr['multiple'] ? '[]' : ''; ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> placeholder="<?php echo esc_attr($field->placeholder); ?>">

		<?php foreach ($field->options as $option_value => $option_label) : ?>

		<option <?php selected($field->value === $option_value || (is_array($field->value) && in_array($option_value, $field->value, true))); ?> value="<?php echo esc_attr($option_value); ?>">

			<?php echo esc_html($option_label); ?>

		</option>

		<?php endforeach; ?>

		<?php if ($field->options_template) : ?>

			<?php
			echo wp_kses(
				$field->options_template,
				array(
					'option' => array(
						'value'    => array(),
						'selected' => array(),
					),
				)
			);
			?>

		<?php endif; ?>

	</select>

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
