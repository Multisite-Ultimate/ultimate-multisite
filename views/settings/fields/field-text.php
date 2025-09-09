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

		<input <?php echo $field->html_attr ? $field->get_html_attributes() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>  <?php echo $field->disabled ? 'disabled="disabled"' : ''; ?> name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" id="<?php echo esc_attr($field->id); ?>" class="regular-text" value="<?php echo esc_attr(wu_get_setting($field->id)); ?>" placeholder="<?php echo esc_attr($field->placeholder ?: ''); ?>">

		<?php if (isset($field->append) && ! empty($field->append)) : ?>

			<?php echo $field->append; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php endif; ?>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo $field->desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

</div>
