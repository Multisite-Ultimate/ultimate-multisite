<?php
/**
 * Image field view.
 *
 * @since 2.0.0
 */
?>
<?php

// We need to get the media scripts
wp_enqueue_media();
wp_enqueue_script('media');

wp_enqueue_script('wu-field-button-upload', wu_get_asset('wu-field-image.js', 'js'), [], wu_get_version(), true);

?>

<tr>
	<th scope="row"><label for="<?php echo esc_attr($field_slug); ?>"><?php echo esc_html($field['title']); ?></label></th>
<td>

	<?php
	$image_url = WU_Settings::get_logo('full', wu_get_setting($field_slug));

	if ( ! $image_url && isset($field['default'])) {
		$image_url = $field['default'];
	}

	if ( $image_url ) {
		printf(
			'<img id="%s" src="%s" alt="%s" style="width:%s; height:auto">',
			esc_attr($field_slug . '-preview'),
			esc_attr($image_url),
			esc_attr(get_bloginfo('name')),
			esc_attr($field['width'] . 'px')
		);
	}
	?>

	<br>

	<a href="#" class="button wu-field-button-upload" data-target="<?php echo esc_attr($field_slug); ?>">
		<?php echo esc_html($field['button']); ?>
	</a>

	<a data-default="<?php echo esc_attr($field['default']); ?>" href="#" class="button wu-field-button-upload-remove" data-target="<?php echo esc_attr($field_slug); ?>">
		<?php esc_html_e('Remove Image', 'multisite-ultimate'); ?>
	</a>

	<?php if ( ! empty($field['desc'])) : ?>
	<p class="description" id="<?php echo esc_attr($field_slug); ?>-desc">
		<?php echo esc_html($field['desc']); ?>
	</p>

	<input type="hidden" name="<?php echo esc_attr($field_slug); ?>" id="<?php echo esc_attr($field_slug); ?>" value="<?php echo esc_attr(wu_get_setting($field_slug) ?: $field['default']); ?>">

	<?php endif; ?>

</td>
</tr>
