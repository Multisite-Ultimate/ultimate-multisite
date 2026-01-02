<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

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

	<div class="<?php echo esc_attr('wu-my-0 ' . $field->classes); ?>">
		<?php
		$desc = $field->desc;
		if ($desc) {
			echo wp_kses($desc, wu_kses_allowed_html());
		}
		?>
	</div>

</li>
