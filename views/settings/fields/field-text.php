<?php
/**
 * Text field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo esc_html($field->title); ?>

		</label>

	</div>

	<div class="wu-w-2/3">

		<input <?php $field->print_html_attributes(); ?> <?php echo $field->disabled ? 'disabled="disabled"' : ''; ?> name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" id="<?php echo esc_attr($field->id); ?>" class="regular-text" value="<?php echo esc_attr(wu_get_setting($field->id)); ?>" placeholder="<?php echo esc_attr($field->placeholder ?: ''); ?>">

		<?php if (isset($field->append) && ! empty($field->append)) : ?>

			<?php echo wp_kses($field->append, wu_kses_allowed_html()); ?>

		<?php endif; ?>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo wp_kses($field->desc, wu_kses_allowed_html()); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

</div>
