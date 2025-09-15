<?php
/**
 * Products field view.
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

	<?php foreach (wu_get_plans() as $option) : ?>
		<label class="wu-block" for="field-<?php echo esc_attr($field->id); ?>-<?php echo esc_attr($option->get_id()); ?>">
			<input id="field-products-<?php echo esc_attr($option->get_id()); ?>" type="checkbox" name="products[]" value="<?php echo esc_attr($option->get_id()); ?>" <?php checked($option->get_id() == $field->value); ?> v-model="products">
			<?php echo esc_html($option->get_name()); ?>
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
