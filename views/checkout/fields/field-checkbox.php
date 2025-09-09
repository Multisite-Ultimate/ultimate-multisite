<?php
/**
 * Checkbox field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<label class="wu-block wu-my-4" for="field-<?php echo esc_attr($field->id); ?>">

		<?php echo $field->title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php echo wu_tooltip($field->tooltip); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php echo $field->desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
