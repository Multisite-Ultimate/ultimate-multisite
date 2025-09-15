<?php
/**
 * Group field view.
 *
 * @since 2.0.0
 */
defined( 'ABSPATH' ) || exit;

?>
<div class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>
	<span class="wu-block wu-w-full <?php echo esc_attr($field->classes); ?>">
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
		<?php
		/**
		 * Instantiate the form for the order details.
		 *
		 * @since 2.0.0
		 */
		$form = new \WP_Ultimo\UI\Form(
			$field->id,
			$field->fields,
			[
				'views'                 => 'checkout/fields',
				'classes'               => 'wu-flex wu-my-1',
				'field_wrapper_classes' => 'wu-bg-transparent',
				'wrap_tag'              => 'span',
				'step'                  => (object) [
					'classes' => '',
				],
			]
		);
		$form->render();
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
		<?php if ($field->desc) : ?>
			<span class="wu-mt-2 wu-block wu-bg-gray-100 wu-rounded wu-border-solid wu-border-gray-400 wu-border-t wu-border-l wu-border-b wu-border-r wu-text-2xs wu-py-2 wu-p-2">
				<?php echo esc_html($field->desc); ?>
			</span>
		<?php endif; ?>
	</span>
</div>
