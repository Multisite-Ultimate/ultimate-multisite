<?php
/**
 * Select field view.
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

		<select name="<?php echo esc_attr($field->id); ?>" id="<?php echo esc_attr($field->id); ?>" class="regular-text">

		<?php foreach ($field->options as $value => $option) : ?>

			<option <?php selected(wu_get_setting($field->id), $value); ?> value="<?php echo esc_attr($value); ?>">

			<?php echo $option; ?>

			</option>

		<?php endforeach; ?>

		</select>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

	<?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>
