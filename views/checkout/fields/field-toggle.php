<?php
/**
 * Checkbox field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<label class="wu-block wu-my-4" for="field-<?php echo esc_attr($field->id); ?>">

		<input id="field-<?php echo esc_attr($field->id); ?>" type="checkbox" name="<?php echo esc_attr($field->id); ?>" value="1" <?php $field->print_html_attributes(); ?> <?php checked($field->value); ?>>

		<?php echo wp_kses($field->title, wu_kses_allowed_html()); ?>

		<?php wu_tooltip($field->tooltip); ?>

		<?php echo wp_kses($field->desc, wu_kses_allowed_html()); ?>
	</label>

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
