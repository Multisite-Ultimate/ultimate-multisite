<?php
/**
 * Color field view.
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

		<input class="regular-text field-<?php echo esc_attr($field->id); ?>" name="<?php echo esc_attr($field->id); ?>" type="text" id="<?php echo esc_attr($field->id); ?>" value="<?php echo esc_attr(wu_get_setting($field->id)); ?>" placeholder="<?php echo esc_attr($field->placeholder ?: ''); ?>">

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

	<?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>

<?php
wp_enqueue_script('wu-color-field', wu_get_asset('color-field.js', 'js'), ['jquery', 'wp-color-picker'], wu_get_version(), true);
wp_add_inline_script('wu-color-field', 'var wu_color_field_ids = wu_color_field_ids || []; wu_color_field_ids.push("' . esc_js($field->id) . '");', 'before');
?>
