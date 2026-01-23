<?php
/**
 * Password field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

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

	<div class="wu-relative wu-password-field-container">
		<input class="form-control wu-w-full wu-my-1 wu-password-input <?php echo esc_attr(trim($field->classes)); ?>"
				id="field-<?php echo esc_attr($field->id); ?>"
				name="<?php echo esc_attr($field->id); ?>"
				type="<?php echo esc_attr($field->type); ?>"
				placeholder="<?php echo esc_attr($field->placeholder); ?>"
				value="<?php echo esc_attr($field->value); ?>"
				<?php $field->print_html_attributes(); ?>>
		<button type="button"
				class="wu-pwd-toggle hide-if-no-js wu-bg-transparent wu-border-0"
				data-toggle="0"
				aria-label="<?php esc_attr_e('Show password', 'ultimate-multisite'); ?>">
			<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
		</button>
	</div>

	<?php if ($field->meter) : ?>
		<span class="wu-block wu-password-strength-wrapper">
			<span id="pass-strength-result" class="wu-block wu-text-sm">
				<?php esc_html_e('Strength Meter', 'ultimate-multisite'); ?>
			</span>
		</span>
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
