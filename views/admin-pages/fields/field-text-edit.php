<?php
/**
 * Text edit field view.
 *
 * @since 2.0.0
 */
?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" data-wu-app="<?php echo esc_attr($field->id); ?>" data-state='{"edit":false}'>

	<div class="wu-block" v-show="!edit">

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

	<?php if ('date' === $field->type || true === $field->date) : ?>

		<?php

		if (wu_validate_date($field->value)) {
			if ( ! $field->display_value) {
				esc_html_e('No date', 'multisite-ultimate');
			} else {
				$date = $field->value;

				$time = strtotime(get_date_from_gmt($date));

				$formatted_value = date_i18n(get_option('date_format'), $time);

				// translators: %s a unit of time.
				$placeholder = wu_get_current_time('timestamp') > $time ? __('%s ago', 'multisite-ultimate') : __('In %s', 'multisite-ultimate');
				printf('<time datetime="%3$s">%1$s</time><br><small>%2$s</small>', esc_html($formatted_value), esc_html(sprintf($placeholder, human_time_diff($time, wu_get_current_time('timestamp')))), esc_html(get_date_from_gmt($date)));
			}
		} else {
			esc_html_e('None', 'multisite-ultimate');
		}

		?>

	<?php else : ?>

		<span class="wu-my-1 wu-inline-block">

		<?php echo $field->display_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		</span>

	<?php endif; ?>

	</div>

	<?php if ($field->edit) : ?>

	<div class="wu-block" v-show="!edit">
		<a href="#" class="wu-p-2 wu--m-2 wp-ui-text-highlight" v-on:click="open($event)" data-field="<?php echo esc_attr($field_slug); ?>">
		<?php echo wu_tooltip(__('Edit'), 'dashicons-edit'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>

	<div v-cloak class="wu-block wu-w-full" v-show="edit">

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

		<input class="form-control wu-w-full wu-my-1" name="<?php echo esc_attr($field->id); ?>" type="text" placeholder="<?php echo esc_attr($field->placeholder); ?>" value="<?php echo esc_attr($field->value); ?>" <?php echo $field->get_html_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

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

	<?php endif; ?>

	<?php if ($field->copy) : ?>

	<div class="wu-block" v-show="!edit">
		<a href="#" class="wu-p-2 wu--m-2" v-on:click="edit($event, '<?php echo esc_js($field_slug); ?>')" data-field="<?php echo esc_attr($field_slug); ?>">
		<?php echo wu_tooltip(__('Copy'), 'dashicons-admin-page'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>

	<?php endif; ?>

</li>
