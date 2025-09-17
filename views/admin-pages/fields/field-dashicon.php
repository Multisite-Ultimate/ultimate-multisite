<?php
/**
 * Select Dashicon field view.
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

	<select class="wu_select_icon" name="<?php echo esc_attr($field->id); ?>">

		<option value=""><?php echo esc_html__('No Icon', 'ultimate-multisite'); ?></option>

		<?php foreach (wu_get_icons_list() as $category_label => $category_array) : ?>

			<optgroup label="<?php echo esc_attr($category_label); ?>">

			<?php foreach ($category_array as $option_key => $option_value) : ?>

				<option
				value="<?php echo esc_attr($option_value); ?>"
				<?php selected($field->value, $option_value); ?>
				>
				<?php echo esc_html($option_value); ?>
				</option>

			<?php endforeach; ?>

			</optgroup>

		<?php endforeach; ?>

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
