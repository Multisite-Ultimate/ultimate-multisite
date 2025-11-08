<?php
/**
 * HTML field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

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

	<div class="wu-block wu-w-full wu-mt-4 <?php echo esc_attr($field->classes); ?>">

		<?php echo wp_kses($field->content, wu_kses_allowed_html()); ?>

	</div>

	</div>


</li>
