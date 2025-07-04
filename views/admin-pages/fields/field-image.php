<?php
/**
 * Image field view.
 *
 * @since 2.0.0
 */

/**
 * Set the media query.
 *
 * When the stacked option is present
 * and set to true, ignore the flex arrangement
 * and make elements stacked.
 */
$mq = $field->stacked ? 'ignore-' : '';

$content_wrapper_classes = $field->content_wrapper_classes
	? esc_attr(trim($field->content_wrapper_classes))
	: "wu-ml-0 {$mq}md:wu-ml-4 {$mq}md:wu-w-4/12 wu-mt-4 {$mq}md:wu-mt-0 lg:wu-mt-2";

?>

<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php echo $field->get_wrapper_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<div class="<?php echo esc_attr($mq); ?>md:wu-flex wu-items-center wu-w-full">

		<div class="<?php echo esc_attr($mq); ?>md:wu-w-10/12">
	
		<?php

		/**
		 * Adds the partial title template.
		 *
		 * @since 2.0.0
		 */
		wu_get_template(
			'admin-pages/fields/partials/field-title',
			[
				'field' => $field,
			]
		);

		?>

		<div class="<?php echo esc_attr($mq); ?>md:wu-w-9/12">
	  
		<?php

		/**
		 * Adds the partial title template.
		 *
		 * @since 2.0.0
		 */
		wu_get_template(
			'admin-pages/fields/partials/field-description',
			[
				'field' => $field,
			]
		);

		?>

		</div>

	</div>

	<div class="<?php echo esc_attr($content_wrapper_classes); ?>">
	  
		<div class="wu-wrapper-image-field wu-w-full wu-overflow-hidden">

		<div class="wu-relative wu-w-full wu-overflow-hidden">

			<div class="wu-self-center wu-rounded wu-flex <?php echo esc_attr($mq); ?>md:wu-max-w-full wu-min-w-full <?php echo esc_attr($mq); ?>md:wu-max-h-20 wu-overflow-hidden">
			
			<img 
				class="<?php echo $field->img ? '' : 'wu-absolute'; ?> wu-self-center wu-rounded sm:wu-max-w-full wu-min-w-full" 
				src="<?php echo esc_url($field->img); ?>"
			>

			</div>

			<div class="wu-wrapper-image-field-upload-actions wu-absolute wu-top-4 wu-right-4 <?php echo esc_attr($mq); ?>md:wu-top-2 <?php echo esc_attr($mq); ?>md:wu-right-2 wu-scale-150 <?php echo esc_attr($mq); ?>md:wu-scale-100">

			<a title="<?php esc_attr_e('Preview Image', 'multisite-ultimate'); ?>" href="<?php echo esc_url($field->img); ?>" class="wubox wu-no-underline wu-text-center wu-inline-block wu-bg-black wu-opacity-60 wu-rounded-full wu-text-white wu-w-5 wu-h-5 wu-shadow-sm">

				<span class="dashicons-wu-eye1 wu-align-middle" style="top: -2px;"></span>

			</a>

			<a title="<?php esc_attr_e('Remove Image', 'multisite-ultimate'); ?>" href="#" class="wu-remove-image wu-no-underline wu-text-center wu-inline-block wu-bg-black wu-opacity-60 wu-rounded-full wu-text-white wu-w-5 wu-h-5 wu-shadow-sm">

				<span class="dashicons-wu-cross wu-align-middle"></span>

			</a>

			</div>

		</div>

		<input name="<?php echo esc_attr($field_slug); ?>" type="hidden" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />

		<div class="wu-add-image-wrapper <?php echo esc_attr($mq); ?>md:wu-mt-0 wu-w-full" style="display: none;">

			<a class="button wu-w-full wu-text-center wu-add-image">
  
			<span class="dashicons-wu-upload"></span> <?php esc_html_e('Upload Image', 'multisite-ultimate'); ?>
  
			</a>

		</div>

		</div>

	</div>

	</div>

</li>
