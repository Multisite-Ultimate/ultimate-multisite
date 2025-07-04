<?php
/**
 * Text field view.
 *
 * @since 2.0.0
 */
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

	<?php if ('model' === $field->type) : ?>

		<div class="wu-flex">

		<div class="wu-w-full wu-my-1">
			<input class="form-control wu-w-full" name="<?php echo esc_attr($field->id); ?>" type="text" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		</div>

		<?php if (wu_get_isset($field->html_attr, 'data-base-link')) : ?>

			<div class="wu-ml-1 wu-my-1" v-cloak>
			<a
				v-bind:href="'<?php echo esc_js(wu_get_isset($field->html_attr, 'data-base-link')); ?>' + '=' + <?php echo esc_js(wu_get_isset($field->html_attr, 'v-model')); ?>"
				target="_blank"
				class="button"
				v-show='<?php echo esc_js(wu_get_isset($field->html_attr, 'v-model')); ?>'
				<?php echo wu_tooltip_text(__('View', 'multisite-ultimate')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			>
				<span class="dashicons-wu-popup wu-m-0 wu-p-0"></span>
			</a>
			</div>

		<?php endif; ?>

		</div>

	<?php elseif ($field->money) : ?>

		<money class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></money>

		<input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> v-if="false">

	<?php else : ?>

		<input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php endif; ?>

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
