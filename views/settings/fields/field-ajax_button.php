<?php
/**
 * Ajax button field view.
 *
 * @since 2.0.0
 */
defined('ABSPATH') || exit;
?>
<div class="wu-my-6">

	<div class="wu-flex">

	<div class="wu-w-1/3">

		<label for="<?php echo esc_attr($field->id); ?>">

		<?php echo esc_html($field->title); ?>

		</label>

	</div>

	<div class="wu-w-2/3">

		<label for="<?php echo esc_attr($field->id); ?>">
		<button class="button wu-ajax-button" name="<?php echo esc_attr($field->id); ?>" id="<?php echo esc_attr($field->id); ?>" value="<?php echo esc_attr(wp_create_nonce($field->action)); ?>" data-action-url="<?php echo esc_attr(admin_url('admin-ajax.php?action=') . $field->action); ?>">
			<?php echo esc_html($field->title); ?>
		</button>
		</label>

		<?php if ($field->desc) : ?>

		<p class="description" id="<?php echo esc_attr($field->id); ?>-desc">

			<?php echo esc_html($field->desc); ?>

		</p>

		<?php endif; ?>

	</div>

	</div>

</div>

