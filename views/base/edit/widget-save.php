<?php
/**
 * Save widget view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;

?>
<?php if ( ! empty($labels['save_description'])) : ?>

	<p class="wu-mb-5">
	<?php echo wp_kses_post($labels['save_description']); ?>
	</p>

<?php endif; ?>

<div class="wu-bg-gray-200 wu-p-4 wu--m-3 wu--mt-2 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid">

	<button type="submit" name="action" value="save" class="button button-primary wu-w-full">
	<?php echo esc_html($labels['save_button_label']); ?>
	</button>

</div>
