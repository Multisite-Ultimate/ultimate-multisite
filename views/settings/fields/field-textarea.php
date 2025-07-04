<?php
/**
 * Textarea field view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo $field->title; ?>

		</label>

	</div>

	<div class="wu-w-2/3">

		<textarea cols="60" rows="7" name="<?php echo esc_attr($field->id); ?>" id="<?php echo esc_attr($field->id); ?>" class="regular-text" placeholder="<?php echo $field->placeholder ? esc_attr($field->placeholder) : ''; ?>"><?php echo esc_textarea(stripslashes(wu_get_setting($field_slug))); ?></textarea>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

	<?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>
