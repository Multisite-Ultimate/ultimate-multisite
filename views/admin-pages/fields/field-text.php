<?php
/**
 * Text field view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

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

	<?php if ('model' === $field->type) : ?>

		<div class="wu-flex">

		<div class="wu-w-full wu-my-1">
			<input class="form-control wu-w-full" name="<?php echo esc_attr($field->id); ?>" type="text" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php $field->print_html_attributes(); ?>>
		</div>

		<?php if (wu_get_isset($field->html_attr, 'data-base-link')) : ?>

			<div class="wu-ml-1 wu-my-1" v-cloak>
			<a
				v-bind:href="'<?php echo esc_js(wu_get_isset($field->html_attr, 'data-base-link')); ?>' + '=' + <?php echo esc_js(wu_get_isset($field->html_attr, 'v-model')); ?>"
				target="_blank"
				class="button"
				v-show='<?php echo esc_js(wu_get_isset($field->html_attr, 'v-model')); ?>'
				<?php wu_tooltip_text(__('View', 'multisite-ultimate')); ?>
			>
				<span class="dashicons-wu-popup wu-m-0 wu-p-0"></span>
			</a>
			</div>

		<?php endif; ?>

		</div>

	<?php elseif ($field->money) : ?>

		<money class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php $field->print_html_attributes(); ?>></money>

		<input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php $field->print_html_attributes(); ?> v-if="false">

	<?php else : ?>

		<input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php $field->print_html_attributes(); ?>>

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
