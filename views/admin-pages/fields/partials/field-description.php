<?php
/**
 * Description field partial view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>

<?php if ($field->desc) : ?>

	<p class="description wu-text-2xs" id="<?php echo esc_attr($field->id); ?>-desc">

	<?php echo wp_kses($field->desc, wu_kses_allowed_html()); ?>

	</p>

<?php endif; ?>
