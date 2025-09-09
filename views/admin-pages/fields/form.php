<?php
/**
 * Form view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<div class="wu-styling">

	<?php echo $form->before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<div class="wu-flex wu-flex-wrap">

	<?php if ($form->wrap_in_form_tag) : ?>

		<form 
		id="<?php echo esc_attr($form_slug); ?>" 
		action="<?php echo esc_attr($form->action); ?>"
		method="<?php echo esc_attr($form->method); ?>"
		<?php echo $form->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		>

	<?php endif; ?>

	<ul id="wp-ultimo-form-<?php echo esc_attr($form->id); ?>" class="wu-flex-grow <?php echo esc_attr(trim($form->classes)); ?>" <?php echo $form->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

		<?php echo $rendered_fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</ul>

	<?php if ($form->wrap_in_form_tag) : ?>

	</form>

	<?php endif; ?>

	<?php echo $form->after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	</div>

</div>
