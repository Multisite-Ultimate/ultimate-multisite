<?php
/**
 * Text display field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
/** @var $field \WP_Ultimo\UI\Field */

?>
<li class="<?php echo esc_attr(trim($field->wrapper_classes)); ?>" <?php $field->print_wrapper_html_attributes(); ?>>

	<div class="wu-block">

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
			$date = $field->value;

			$time = strtotime(get_date_from_gmt($date));

			$formatted_value = date_i18n(get_option('date_format'), $time);

			// translators: %s is a relative past date.
			$placeholder = wu_get_current_time('timestamp') > $time ? esc_html__('%s ago', 'ultimate-multisite') : esc_html__('In %s', 'ultimate-multisite');

			printf('<time datetime="%3$s">%1$s</time><br><small>%2$s</small>', esc_html($formatted_value), esc_html(sprintf($placeholder, human_time_diff($time, wu_get_current_time('timestamp')))), esc_attr(get_date_from_gmt($date)));
		} else {
			esc_html_e('None', 'ultimate-multisite');
		}

		?>

	<?php else : ?>

		<span class="wu-my-1 wu-inline-block">

		<span id="<?php echo esc_attr($field->id); ?>_value"><?php echo wp_kses($field->display_value, wu_kses_allowed_html()); ?></span>

		<?php if ($field->copy) : ?>

			<a <?php wu_tooltip_text(esc_html__('Copy', 'ultimate-multisite')); ?> class="wu-no-underline wp-ui-text-highlight wu-copy"  data-clipboard-action="copy" data-clipboard-target="#<?php echo esc_attr($field->id); ?>_value">

			<span class="dashicons-wu-copy wu-align-middle"></span>

			</a>

		<?php endif; ?>

		</span>

	<?php endif; ?>

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

</li>
