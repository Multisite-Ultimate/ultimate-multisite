<?php
/**
 * Toggle field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<div class="wu-block">

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

	<?php if ($field->desc) : ?>

		<span class="wu-my-1 wu-inline-block wu-text-xs"><?php echo wp_kses($field->desc, wu_kses_allowed_html()); ?></span>

	<?php endif; ?>

	</div>

	<div class="wu-block wu-ml-2">

	<div class="wu-toggle">

		<input class="wu-tgl wu-tgl-ios" value="1" <?php checked('1' === (string) $field->value); ?>  id="wu-tg-<?php echo esc_attr($field->id); ?>" type="checkbox" name="<?php echo esc_attr($field_slug); ?>" <?php $field->print_html_attributes(); ?> />

		<label class="wu-tgl-btn wp-ui-highlight wu-bg-blue-500" for="wu-tg-<?php echo esc_attr($field->id); ?>"></label>

	</div>

	</div>

</li>
