<?php
/**
 * Title field partial view.
 *
 * @since 2.0.0
 */
?>

<?php if ($field->desc) : ?>

	<?php echo wp_kses($field->desc, wu_kses_allowed_html()); ?>

	<?php
endif;
