<?php
/**
 * HTML field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>

<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<div class="wu-block wu-w-full">

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

		/**
		 * Adds the partial description template.
		 *
		 * @since 2.0.0
		 */
		wu_get_template(
			'checkout/fields/partials/field-description',
			[
				'field' => $field,
			]
		);
		?>

		<div class="wu-block wu-w-full wu-mt-4">
			<?php echo $field->content ? wp_kses($field->content, wu_kses_allowed_html()) : ''; ?>
		</div>

		<?php
		/**
		 * Adds the partial error's template.
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

</div>
