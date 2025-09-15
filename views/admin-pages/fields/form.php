<?php
/**
 * Form view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $form \WP_Ultimo\UI\Form */

?>
<div class="wu-styling">

	<?php echo wp_kses($form->before ?? '', wu_kses_allowed_html()); ?>

	<div class="wu-flex wu-flex-wrap">

	<?php if ($form->wrap_in_form_tag) : ?>

		<form 
		id="<?php echo esc_attr($form_slug); ?>" 
		action="<?php echo esc_attr($form->action); ?>"
		method="<?php echo esc_attr($form->method); ?>"
		<?php $form->print_html_attributes(); ?>
		>

	<?php endif; ?>

	<ul id="wp-ultimo-form-<?php echo esc_attr($form->id); ?>" class="wu-flex-grow <?php echo esc_attr(trim($form->classes)); ?>" <?php $form->print_html_attributes(); ?>>

		<?php echo wp_kses($rendered_fields, wu_kses_allowed_html()); ?>

	</ul>

	<?php if ($form->wrap_in_form_tag) : ?>

	</form>

	<?php endif; ?>

	<?php echo wp_kses($form->after ?? '', wu_kses_allowed_html()); ?>

	</div>

</div>
