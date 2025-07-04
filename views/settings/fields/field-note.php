<?php
/**
 * Note field view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<?php if ($field->title) : ?>

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo $field->title; ?>

		</label>

	</div>

	<?php endif; ?>

	<div class="<?php echo esc_attr($field->title ? 'wu-w-2/3' : 'wu-w-full'); ?>">

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo $field->desc; ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

	<?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>
