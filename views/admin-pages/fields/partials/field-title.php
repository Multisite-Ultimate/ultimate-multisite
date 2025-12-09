<?php
/**
 * Title field partial view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>

<?php if ($field->title && is_string($field->title)) : ?>

	<span class="wu-my-1 wu-text-2xs wu-uppercase wu-font-bold wu-block">

	<?php echo wp_kses($field->title, wu_kses_allowed_html()); ?>

	<?php if ($field->tooltip) : ?>

		<?php wu_tooltip($field->tooltip); ?>

	<?php endif; ?>

	</span>

<?php endif; ?>
