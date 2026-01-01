<?php
/**
 * Password field view with optional strength meter.
 *
 * @since 2.3.0
 */
defined('ABSPATH') || exit;

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

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

	<div class="wu-relative wu-flex wu-items-center">
		<input class="form-control wu-w-full wu-my-1 wu-pr-10 <?php echo esc_attr(trim($field->classes)); ?>"
			id="field-<?php echo esc_attr($field->id); ?>"
			name="<?php echo esc_attr($field->id); ?>"
			type="password"
			placeholder="<?php echo esc_attr($field->placeholder); ?>"
			value="<?php echo esc_attr($field->value); ?>"
			<?php $field->print_html_attributes(); ?>>
		<button type="button"
				class="wu-pwd-toggle hide-if-no-js wu-absolute wu-right-2 wu-bg-transparent wu-border-0 wu-cursor-pointer wu-p-1 wu-text-gray-500 hover:wu-text-gray-700"
				data-toggle="0"
				aria-label="<?php esc_attr_e('Show password', 'ultimate-multisite'); ?>">
			<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
		</button>
	</div>

	<?php if (! empty($field->meter)) : ?>
		<span class="wu-block">
			<span id="pass-strength-result" class="wu-py-2 wu-px-4 wu-bg-gray-100 wu-block wu-text-sm wu-border-solid wu-border wu-border-gray-200">
				<?php esc_html_e('Strength indicator', 'ultimate-multisite'); ?>
			</span>
		</span>
	<?php endif; ?>

	<?php

	/**
	 * Adds the partial description template.
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
