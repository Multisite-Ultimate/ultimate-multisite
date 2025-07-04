<?php
/**
 * Checkbox field view.
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

		<label for="<?php echo esc_attr($field->id); ?>">

		<input type='hidden' value='0' name="<?php echo esc_attr($field->id); ?>">

		<input <?php checked(wu_get_setting($field_slug)); ?> name="<?php echo esc_attr($field->id); ?>" type="<?php echo esc_attr($field->type); ?>" id="<?php echo esc_attr($field->id); ?>" value="1">

		<?php echo esc_html($field->title); ?>

		</label>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>
</div>
