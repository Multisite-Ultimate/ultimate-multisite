<?php
/**
 * Checkbox multi field view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

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

	<?php foreach ($field->options as $option_value => $option_name) : ?>
		<label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option_value); ?>">
			<input id="field-gateway-<?php echo esc_attr($option_value); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>[]" value="<?php echo esc_attr($option_value); ?>" <?php $field->print_html_attributes(); ?> <?php checked(in_array($option_value, (array) $field->value, true)); ?>>
			<?php echo esc_html($option_name); ?>
		</label>
	<?php endforeach; ?>

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
