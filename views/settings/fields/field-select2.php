<?php
/**
 * Select2 field view.
 *
 * @since 2.0.0
 */
?>
<?php

$setting = wu_get_setting($field_slug);

$setting = is_array($setting) ? $setting : [];

$placeholder = $field['placeholder'] ?? '';

?>

<tr>
	<th scope="row"><label for="<?php echo esc_attr($field_slug); ?>"><?php echo esc_html($field['title']); ?></label> <?php echo wu_tooltip($field['tooltip']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </th>
	<td>

	<select data-width="350px" multiple="multiple" placeholder="<?php echo esc_attr($placeholder); ?>"  class="wu-select" name="<?php echo esc_attr($field_slug); ?>[]" id="<?php echo esc_attr($field_slug); ?>">

		<?php foreach ($field['options'] as $value => $option) : ?>
		<option <?php selected(in_array($value, $setting)); ?> value="<?php echo esc_attr($value); ?>"><?php echo esc_html($option); ?></option>
		<?php endforeach; ?>

	</select>

	<?php if ( ! empty($field['desc'])) : ?>
	<p class="description" id="<?php echo esc_attr($field_slug); ?>-desc">
		<?php echo esc_html($field['desc']); ?>
	</p>
	<?php endif; ?>

	</td>
</tr>
