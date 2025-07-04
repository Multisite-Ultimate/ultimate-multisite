<?php
/**
 * Field wp_editor view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo esc_html($field->title); ?>

		</label>

	</div>

	<div class="wu-w-2/3">

		<div style="max-width: 800px;">

		<?php wp_editor(wu_get_setting($field->id), $field->id, $field->args); ?>

		</div>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

	<?php // if (isset($field['tooltip'])) {echo WU_Util::tooltip($field['tooltip']);} ?>

</div>
