<?php
/**
 * System info view.
 *
 * @since 2.0.0
 */
?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>

<div id="wp-ultimo-wrap" class="<?php wu_wrap_use_container(); ?> wrap">

	<h1 class="wp-heading-inline"><?php esc_html_e('System Info', 'multisite-ultimate'); ?></h1>

	<textarea cols="100" rows="40" aria-hidden="true" class="screen-reader-text" id="hidden_textarea">

	<?php foreach ($data as $name_type => $type) : ?>
		<?php echo "\n" . esc_html($name_type) . "\n"; ?>
		<?php foreach ($type as $key => $value) : ?>
			<?php echo esc_html($value['title'] . ': ' . $value['value']) . "\n"; ?>
		<?php endforeach; ?>

	<?php endforeach; ?>
	</textarea>

	<button data-clipboard-action="copy" data-clipboard-target="#hidden_textarea" class="btn page-title-action">

	<span class="dashicons dashicons-admin-page wu-text-sm wu-align-middle wu-h-4 wu-w-4">&nbsp;</span>

	<?php esc_html_e('Copy Data to Clipboard', 'multisite-ultimate'); ?>

	</button>

	<a href="<?php echo esc_attr(admin_url('admin-ajax.php?action=wu_generate_text_file_system_info')); ?>" class="page-title-action">

	<span class="dashicons dashicons-download wu-text-sm wu-align-middle wu-h-4 wu-w-4">&nbsp;</span>

	<?php esc_html_e('Download File', 'multisite-ultimate'); ?>

	</a>

	<div id="poststuff">
	<div id="post-body" class="">
		<div id="post-body-content">

		<?php do_meta_boxes($screen->id, 'normal', ''); ?>

		</div>
	</div>
	</div>

</div>

<!-- Instantiate clipboard by passing a string selector -->
<script>

	var clipboard = new ClipboardJS('.btn');

</script>

<style>
	.meta-box-sortables .inside {
	margin:0 !important;
	padding:0;
	}
	.meta-box-sortables h2 {
	margin:0;
	padding:0;
	}
</style>
